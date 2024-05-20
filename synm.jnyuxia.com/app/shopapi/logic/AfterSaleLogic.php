<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------

namespace app\shopapi\logic;

use app\adminapi\logic\settings\order\TransactionSettingsLogic;
use app\common\enum\AccountLogEnum;
use app\common\enum\AfterSaleEnum;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\AfterSaleGoods;
use app\common\model\AfterSaleLog;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\RegionService;
use think\facade\Db;

/**
 * 售后逻辑层
 * Class AfterSaleLogic
 * @package app\shopapi\logic
 */
class AfterSaleLogic extends BaseLogic
{
    /**
     * @notes 获取子订单商品信息
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/7/31 18:21
     */
    public static function orderGoodsInfo($params)
    {
        $field = 'og.id as order_goods_id, og.goods_num, og.total_pay_price,og.goods_snap,og.express_price,og.order_id';
        $field .= ',g.name as goods_name, g.image as goods_image';
        $field .= ',gi.image as item_image, gi.spec_value_str';
        $orderGoods = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('goods g', 'g.id = og.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = og.item_id')
            ->field($field)
            ->findOrEmpty($params['order_goods_id']);
        if($orderGoods->isEmpty()) {
            return [];
        }
        $orderGoods = $orderGoods->toArray();
        $orderGoods['image'] = $orderGoods['item_image'] ?: $orderGoods['goods_image'];
        $orderGoods['image'] = FileService::getFileUrl($orderGoods['image']);
        if(isset($params['refund_method'])) {
            $orderGoods['reason'] = AfterSaleEnum::getReason($params['refund_method']);
        }

        //非待发货时扣除运费
        $order = Order::where('id',$orderGoods['order_id'])->findOrEmpty()->toArray();
        if ($order['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY) {
            $orderGoods['total_pay_price'] = $orderGoods['total_pay_price'] - $orderGoods['express_price'];
        }

        return $orderGoods;
    }

    /**
     * @notes 申请商品售后
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/2 11:54
     */
    public static function apply($params)
    {
        DB::startTrans();
        try {
            // 校验是否允许售后申请
            self::checkCondition($params);

            // 生成售后记录
            $data = self::createGoodsAfterSale($params);

            // 新的售后申请成功，删除该子订单以前售后失败的记录
            $ids = AfterSale::where([
                'order_goods_id' => $params['order_goods_id'],
                'status' => AfterSaleEnum::STATUS_FAIL
            ])->column('id');
            if (!empty($ids)) {
                AfterSale::duokaiDestroy($ids);
                AfterSaleGoods::where('after_sale_id', 'in', $ids)->useSoftDelete('delete_time', time())->delete();
            }

            // 消息通知 - 通知卖家
            $mobile = ConfigService::get('shop', 'return_contact_mobile');
            event('Notice', [
                'scene_id' =>  NoticeEnum::SELLER_REFUND_APPLY_NOTICE,
                'params' => [
                    'mobile' => $mobile,
                    'after_sale_sn' => $data['after_sale']->sn
                ]
            ]);

            Db::commit();
            return [
                'after_sale_id' => $data['after_sale']->id,
                'after_sale_goods_id' => $data['after_sale_goods']->id,
            ];
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 校验是否允许售后申请
     * @param $params
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/2 11:54
     */
    public static function checkCondition($params)
    {
        $config = TransactionSettingsLogic::getConfig();
        if(!$config['after_sales']) {
            throw new \think\Exception('系统已关闭售后维权');
        }

        $orderGoods = OrderGoods::findOrEmpty($params['order_goods_id']);
        if($orderGoods->isEmpty()) {
            throw new \think\Exception('子订单不存在,无法发起商品售后');
        }
        $orderGoods = $orderGoods->toArray();
        $order = Order::findOrEmpty($orderGoods['order_id']);
        if ($order['user_id'] != $params['user_id']) {
            throw new \think\Exception('您没有权限发起该订单的售后');
        }
        if($order['pay_status'] != YesNoEnum::YES) {
            throw new \think\Exception('主订单未付款,不允许发起商品售后');
        }
        if ($order['order_status'] == OrderEnum::STATUS_FINISH) {
            $after_sales = ConfigService::get('transaction', 'after_sales');
            if(!$after_sales) {
                throw new \think\Exception('系统已关闭售后维权');
            }

            if(!is_null($order['after_sale_deadline']) && ($order['after_sale_deadline'] < time())) {
                throw new \think\Exception('订单已过售后时间，无法发起商品售后');
            }
        }
        //批量下单后，过了取消订单的时间，可对其中一件商品进行退款操作
        if (in_array($order['order_status'],[OrderEnum::STATUS_WAIT_DELIVERY,OrderEnum::STATUS_WAIT_RECEIVE])) {
            $ableCancelOrder = ConfigService::get('transaction', 'cancel_unshipped_orders');
            if ($ableCancelOrder == YesNoEnum::YES) {
                $configTime = ConfigService::get('transaction', 'cancel_unshipped_orders_times');
                $ableCancelTime = strtotime($order['pay_time']) + ($configTime * 60);
                if (time() < $ableCancelTime) {
                    throw new \think\Exception('买家取消待发货订单未过，无法发起商品售后');
                }
            }
        }

        $aferSale = AfterSale::where([
            ['order_goods_id', '=', $orderGoods['id']],
            ['status', '=', AfterSaleEnum::STATUS_SUCCESS]
        ])->select()->toArray();
        if($aferSale) {
            throw new \think\Exception('该子订单已售后成功, 不能重复发起售后');
        }

        $aferSale = AfterSale::where([
            ['order_goods_id', '=', $orderGoods['id']],
            ['status', '=', AfterSaleEnum::STATUS_ING]
        ])->select()->toArray();
        if($aferSale) {
            throw new \think\Exception('该子订单已在售后中，请耐心等待');
        }

        $aferSale = AfterSale::where([
            ['order_id', '=', $order['id']],
            ['refund_type', '=', AfterSaleEnum::REFUND_TYPE_ORDER],
            ['status', '=', AfterSaleEnum::STATUS_SUCCESS]
        ])->select()->toArray();
        if($aferSale) {
            throw new \think\Exception('主订单已售后成功, 不能重复发起售后');
        }

        $aferSale = AfterSale::where([
            ['order_id', '=', $order['id']],
            ['refund_type', '=', AfterSaleEnum::REFUND_TYPE_ORDER],
            ['status', '=', AfterSaleEnum::STATUS_ING]
        ])->select()->toArray();
        if($aferSale) {
            throw new \think\Exception('主订单已在售后中，请耐心等待');
        }
    }

    /**
     * @notes 生成售后记录
     * @param $params
     * @author Tab
     * @date 2021/8/2 11:54
     */
    public static function createGoodsAfterSale($params)
    {
        $orderGoods = OrderGoods::findOrEmpty($params['order_goods_id'])->toArray();
        $order = Order::where('id',$orderGoods['order_id'])->findOrEmpty()->toArray();
        $refund_total_amount = $orderGoods['total_pay_price'];
        if ($order['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY) {
            $refund_total_amount = $orderGoods['total_pay_price'] - $orderGoods['express_price'];
        }

        $params['refund_image'] = isset($params['refund_image']) ? FileService::setFileUrl($params['refund_image']) : '';
        // 生成售后主表记录
        $data = [
            'sn' => generate_sn((new AfterSale()), 'sn'),
            'user_id' => $params['user_id'],
            'order_id' => $orderGoods['order_id'],
            'order_goods_id' => $orderGoods['id'],
            'refund_reason' => $params['refund_reason'],
            'refund_remark' => $params['refund_remark'] ?? '',
            'refund_image' => $params['refund_image'],
            'refund_type' => AfterSaleEnum::REFUND_TYPE_GOODS,
            'refund_method' => $params['refund_method'],
            'refund_total_amount' => $refund_total_amount,
            'status' => AfterSaleEnum::STATUS_ING,
            'sub_status' => AfterSaleEnum::SUB_STATUS_WAIT_SELLER_AGREE,
            'refund_status' => AfterSaleEnum::NO_REFUND,
            'voucher' => $params['voucher'] ?? []
        ];

        $afterSale = AfterSale::duokaiCreate($data);

        // 生成售后商品记录
        $data = [
            'after_sale_id' => $afterSale->id,
            'order_goods_id' => $orderGoods['id'],
            'goods_id' => $orderGoods['goods_id'],
            'item_id' => $orderGoods['item_id'],
            'goods_price' => $orderGoods['goods_price'],
            'goods_num' => $orderGoods['goods_num'],
            'refund_amount' => $refund_total_amount
        ];

        $afterSaleGoods = AfterSaleGoods::duokaiCreate($data);

        // 生成售后日志
        self::createAfterLog($afterSale->id, '买家发起商品售后,等待卖家同意', $params['user_id'], AfterSaleLogEnum::ROLE_BUYER);

        return [
            'after_sale' => $afterSale,
            'after_sale_goods' => $afterSaleGoods,
        ];
    }

    /**
     * @notes 生成售后日志
     * @param $afterSaleId
     * @param $content
     * @param null $operatorId
     * @param null $operatorRole
     * @author Tab
     * @date 2021/8/9 20:10
     */
    public static function createAfterLog($afterSaleId, $content, $operatorId = null, $operatorRole = null)
    {
        $data = [
            'after_sale_id' => $afterSaleId,
            'content' => $content,
            'operator_id' => $operatorId,
            'operator_role' => $operatorRole,
        ];

        AfterSaleLog::duokaiCreate($data);
    }

    /**
     * @notes 买家取消售后
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 10:26
     */
    public static function cancel($params)
    {
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后订单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不是售后中状态，不允许撤销申请');
            }
            if(!in_array($afterSale->sub_status,AfterSaleEnum::ALLOW_CANCEL)) {
                throw new \think\Exception('售后处理中，不允许撤销申请');
            }
            $afterSale->status = AfterSaleEnum::STATUS_FAIL;
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_BUYER_CANCEL_AFTER_SALE;
            $afterSale->duokaiSave();
            // 售后日志
            AfterSaleService::createAfterLog($afterSale->id, '买家取消售后', $params['user_id'], AfterSaleLogEnum::ROLE_BUYER);
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 买家确认退货
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 11:45
     */
    public static function returnGoods($params)
    {
        Db::startTrans();
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后订单不存在');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_BUYER_RETURN) {
                throw new \think\Exception('非等待买家退货状态,不允许进行确认退货操作');
            }
            if(isset($params['express_image']) && !empty($params['express_image'])) {
                $params['express_image'] = FileService::setFileUrl($params['express_image']);
            }
            $afterSale->express_name = $params['express_name'];
            $afterSale->invoice_no = $params['invoice_no'];
            $afterSale->express_remark = $params['express_remark'] ?? '';
            $afterSale->express_image = $params['express_image'] ?? '';
            $afterSale->express_time = time();
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_WAIT_SELLER_RECEIPT;
            $afterSale->duokaiSave();

            // 记录日志
            AfterSaleService::createAfterLog($afterSale->id, '买家已退货,等待卖家确认收货', $params['user_id'], AfterSaleLogEnum::ROLE_BUYER);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 查看售后列表
     * @param $params
     * @return array|mixed
     * @author Tab
     * @date 2021/8/10 14:20
     */
    public static function lists($params)
    {
        $lists = [];
        switch($params['type']) {
            // 未发起过售后的子订单
            case 'apply':
                $lists = self::applyList($params);
                break;
            // 售后中、售后成功、售后结束
            case 'status_ing';
            case 'status_success';
            case 'status_fail';
            case 'status_success_fail';
                $lists = self::statusLists($params);
                break;
        }
        // 统计数据
        $lists['extend'] = self::statistics($params);
        return $lists;
    }

    /**
     * @notes 统计数据
     * @author Tab
     * @date 2021/12/7 15:26
     */
    public static function statistics($params)
    {
        // 未发起过售后的子订单
        $afterSaleList = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->where('as.user_id',$params['user_id'])
            ->column('asg.order_goods_id');

        $orderGoodsWhere = [
            ['o.user_id', '=', $params['user_id']],
            ['o.pay_status', '=', YesNoEnum::YES],
            ['og.id', 'not in', $afterSaleList],
        ];
        $field = 'og.id,og.goods_num,og.goods_price,og.goods_snap';
        $apply = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->field($field)
            ->where($orderGoodsWhere)
            ->count();

        // 处理中的售后
        $field = 'asg.id as sub_id,asg.create_time,as.id as master_id,as.refund_method,as.sub_status,asg.order_goods_id as goods_snap,og.goods_num,og.goods_price,og.id as order_goods_id';
        $ing = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->leftJoin('order_goods og', 'og.id = asg.order_goods_id')
            ->where([
                ['user_id', '=', $params['user_id']],
                ['status', 'in', [AfterSaleEnum::STATUS_ING]],
            ])
            ->field($field)
            ->count();

        // 处理完成的售后
        $finish = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->leftJoin('order_goods og', 'og.id = asg.order_goods_id')
            ->where([
                ['user_id', '=', $params['user_id']],
                ['status', 'in', [AfterSaleEnum::STATUS_SUCCESS, AfterSaleEnum::STATUS_FAIL]],
            ])
            ->field($field)
            ->count();

        return [
            'apply' => $apply,
            'ing' => $ing,
            'finish' => $finish,
        ];
    }

    /**
     * @notes 未发起过售后的子订单列表
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/8/10 11:29
     */
    public static function applyList($params)
    {
        // 未发起过售后的子订单
        $afterSaleList = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->where('as.user_id',$params['user_id'])
            ->column('asg.order_goods_id');

        $orderGoodsWhere = [
            ['o.user_id', '=', $params['user_id']],
            ['o.pay_status', '=', YesNoEnum::YES],
            ['og.id', 'not in', $afterSaleList],
        ];

        $field = 'og.id,og.goods_num,og.goods_price,og.goods_snap';
        $orderGoodsLists = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->field($field)
            ->where($orderGoodsWhere)
            ->order('og.id', 'desc')
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->field($field)
            ->where($orderGoodsWhere)
            ->count();

        $data = [
            'lists' => $orderGoodsLists,
            'page' => $params['page_no'],
            'size' => $params['page_size'],
            'count' => $count,
            'more' => is_more($count, $params['page_no'], $params['page_size'])
        ];
        return $data;
    }

    /**
     * @notes 售后列表
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/8/10 14:09
     */
    public static function statusLists($params)
    {
        switch($params['type']) {
            case 'status_ing':
                $status = [AfterSaleEnum::STATUS_ING];
                break;
            case 'status_success':
                $status = [AfterSaleEnum::STATUS_SUCCESS];
                break;
            case 'status_fail':
                $status = [AfterSaleEnum::STATUS_FAIL];
                break;
            case 'status_success_fail':
                $status = [AfterSaleEnum::STATUS_SUCCESS, AfterSaleEnum::STATUS_FAIL];
                break;
        }
        $field = 'asg.id as sub_id,asg.create_time,as.id as master_id,as.refund_method,as.sub_status,asg.order_goods_id as goods_snap,og.goods_num,og.goods_price,og.id as order_goods_id';
        $lists = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->leftJoin('order_goods og', 'og.id = asg.order_goods_id')
            ->where([
                ['user_id', '=', $params['user_id']],
                ['status', 'in', $status],
            ])
            ->field($field)
            ->page($params['page_no'], $params['page_size'])
            ->order('asg.id', 'desc')
            ->select()
            ->toArray();

        $count = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->leftJoin('order_goods og', 'og.id = asg.order_goods_id')
            ->where([
                ['user_id', '=', $params['user_id']],
                ['status', 'in', $status],
            ])
            ->field($field)
            ->count();

        foreach($lists as &$item) {
            $item['refund_method_desc'] = AfterSaleEnum::getMethodDesc($item['refund_method']);
            $item['sub_status_desc'] = AfterSaleEnum::getSubStatusDesc($item['sub_status']);
            $item['btns'] = AfterSaleEnum::getBtns2($item['sub_status']);
        }

        $data = [
            'lists' => $lists,
            'page' => $params['page_no'],
            'size' => $params['page_size'],
            'count' => $count,
            'more' => is_more($count, $params['page_no'], $params['page_size'])
        ];

        return $data;
    }

    /**
     * @notes 查看售后详情
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/8/10 15:21
     */
    public static function detail($params)
    {
        $field = 'asg.id as sub_id,as.sub_status,asg.order_goods_id,asg.order_goods_id as goods_snap,asg.goods_num,asg.goods_price,as.refund_method,as.refund_reason,asg.refund_amount,as.refund_remark,as.id as master_id,as.sn,as.create_time,as.voucher,as.express_name,as.invoice_no,as.express_time';
        $detail = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('after_sale as', 'as.id = asg.after_sale_id')
            ->leftJoin('order_goods og', 'og.id = asg.order_goods_id')
            ->field($field)
            ->findOrEmpty($params['id'])
            ->toArray();
        if(empty($detail)) {
            return [];
        }
        $detail['refund_method_desc'] = AfterSaleEnum::getMethodDesc($detail['refund_method']);
        $detail['sub_status_desc'] = AfterSaleEnum::getSubStatusDesc($detail['sub_status']);
        $detail['btns'] = AfterSaleEnum::getBtns2($detail['sub_status']);
        $detail['voucher'] = empty($detail['voucher']) ? [] : json_decode($detail['voucher'], true);
        $detail['express_time'] = empty($detail['express_time']) ? '' : date('Y-m-d H:i:s', $detail['express_time']);
        foreach ($detail['voucher'] as &$item) {
            $item = FileService::getFileUrl($item);
        }
        // 退货地址
        $detail['return_contact'] = ConfigService::get('shop', 'return_contact', '');
        $detail['return_contact_mobile'] = ConfigService::get('shop', 'return_contact_mobile', '');
        $detail['return_province'] = ConfigService::get('shop', 'return_province', '');
        $detail['return_city'] = ConfigService::get('shop', 'return_city', '');
        $detail['return_district'] = ConfigService::get('shop', 'return_district', '');
        $detail['return_address'] = ConfigService::get('shop', 'return_address', '');
        $detail['address'] = RegionService::getAddress([$detail['return_province'], $detail['return_city'], $detail['return_district']], $detail['return_address']);

        return $detail;
    }
}