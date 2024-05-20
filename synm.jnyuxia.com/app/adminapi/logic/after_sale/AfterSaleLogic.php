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

namespace app\adminapi\logic\after_sale;

use app\common\enum\AfterSaleEnum;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\logic\RefundLogic;
use app\common\model\AfterSale;
use app\common\model\AfterSaleGoods;
use app\common\model\AfterSaleLog;
use app\common\model\GoodsSupplier;
use app\common\model\Order;
use app\common\model\User;
use app\common\service\after_sale\AfterSaleService;
use think\facade\Db;

/**
 * 售后逻辑层
 * Class AfterSaleLogic
 * @package app\adminapi\logic\after_sale
 */
class AfterSaleLogic extends BaseLogic
{
    /**
     * @notes 卖家同意售后
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/2 19:51
     */
    public static function agree($params)
    {
        Db::startTrans();
        try{
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后订单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不在售后中状态,不能进行同意售后操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_AGREE) {
                throw new \think\Exception('不是等待卖家同意状态,不能进行同意售后操作');
            }
            switch($afterSale->refund_method) {
                // 仅退款
                case AfterSaleEnum::METHOD_ONLY_REFUND:
                    $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_WAIT_SELLER_REFUND;
                    AfterSaleService::createAfterLog($afterSale->id, '卖家已同意,等待退款', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);
                    break;
                // 退货退款
                case AfterSaleEnum::METHOD_REFUND_GOODS:
                    $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_WAIT_BUYER_RETURN;
                    AfterSaleService::createAfterLog($afterSale->id, '卖家已同意售后,等待买家退货', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);
                    break;
            }
            $afterSale->admin_id = $params['admin_id'];
            $afterSale->admin_remark = $params['admin_remark'] ?? '';
            $afterSale->duokaiSave();

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 卖家拒绝售后
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/2 20:01
     */
    public static function refuse($params)
    {
        Db::startTrans();
        try{
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后订单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不在售后中状态,不能进行拒绝售后操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_AGREE) {
                throw new \think\Exception('不是等待卖家同意状态,不能进行拒绝售后操作');
            }
            $afterSale->status = AfterSaleEnum::STATUS_FAIL;
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_SELLER_REFUSE_AFTER_SALE;
            $afterSale->admin_id = $params['admin_id'];
            $afterSale->admin_remark = $params['admin_remark'] ?? '';
            $afterSale->duokaiSave();

            AfterSaleService::createAfterLog($afterSale->id, '卖家拒绝售后', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);

            // 消息通知
            event('Notice', [
                'scene_id' => NoticeEnum::REFUND_REFUSE_NOTICE,
                'params' => [
                    'user_id' => $afterSale->user_id,
                    'after_sale_sn' => $afterSale->sn
                ]
            ]);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 卖家拒绝收货
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 12:02
     */
    public static function refuseGoods($params)
    {
        Db::startTrans();
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不是售后中状态，不能进行拒绝收货操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_RECEIPT) {
                throw new \think\Exception('不是等待卖家收货状态，不允许进行拒绝收货操作');
            }
            $afterSale->status = AfterSaleEnum::STATUS_FAIL;
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_SELLER_REFUSE_RECEIPT;
            $afterSale->duokaiSave();

            // 记录日志
            AfterSaleService::createAfterLog($afterSale->id, '卖家拒绝收货，售后失败', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 卖家确认收货
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 14:04
     */
    public static function confirmGoods($params)
    {
        Db::startTrans();
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不是售后中状态，不能进行确认收货操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_RECEIPT) {
                throw new \think\Exception('不是等待卖家收货状态，不允许进行确认收货操作');
            }
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_WAIT_SELLER_HANDLE;
            $afterSale->duokaiSave();

            // 记录日志
            AfterSaleService::createAfterLog($afterSale->id, '卖家确认收货，等待卖家处理', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 卖家同意退款
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 14:24
     */
    public static function agreeRefund($params)
    {
        Db::startTrans();
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不是售后中状态，不能进行同意退款操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_HANDLE) {
                throw new \think\Exception('不是等待卖家处理状态，不允许进行同意退款操作');
            }
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_WAIT_SELLER_REFUND;
            $afterSale->duokaiSave();

            // 记录日志
            AfterSaleService::createAfterLog($afterSale->id, '卖家已同意，等待退款', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /** 卖家拒绝退款
     * @notes
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 14:33
     */
    public static function refuseRefund($params)
    {
        Db::startTrans();
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不是售后中状态，不能进行拒绝退款操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_HANDLE) {
                throw new \think\Exception('不是等待卖家处理状态，不允许进行拒绝退款操作');
            }
            $afterSale->status = AfterSaleEnum::STATUS_FAIL;
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_SELLER_REFUSE_REFUND;
            $afterSale->duokaiSave();

            // 记录日志
            AfterSaleService::createAfterLog($afterSale->id, '卖家拒绝退款,售后失败', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 卖家确认退款
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/3 10:59
     */
    public static function confirmRefund($params)
    {
        Db::startTrans();
        try {
            $afterSale = AfterSale::findOrEmpty($params['id']);
            if($afterSale->isEmpty()) {
                throw new \think\Exception('售后单不存在');
            }
            if($afterSale->status != AfterSaleEnum::STATUS_ING) {
                throw new \think\Exception('不是售后中状态，不能进行确认退款操作');
            }
            if($afterSale->sub_status != AfterSaleEnum::SUB_STATUS_WAIT_SELLER_REFUND) {
                throw new \think\Exception('不是等待卖家退款状态，不允许进行确认退款操作');
            }
            if($afterSale->refund_total_amount < $params['refund_total_amount']) {
                throw new \think\Exception('退款金额不能大于订单实付金额');
            }

            $afterSale->refund_total_amount = $params['refund_total_amount'];
            $afterSale->refund_way = $params['refund_way'];
            $afterSale->sub_status = AfterSaleEnum::SUB_STATUS_SELLER_REFUND_ING;
            $afterSale->duokaiSave();

            // 更新售后商品记录中的退款金额
            AfterSaleGoods::where('after_sale_id', $afterSale->id)->update(['refund_amount' => $params['refund_total_amount']]);

            // 记录日志
            AfterSaleService::createAfterLog($afterSale->id, '卖家已确认退款，售后退款中', $params['admin_id'], AfterSaleLogEnum::ROLE_SELLER);

            // 退款
            $order = Order::findOrEmpty($afterSale->order_id)->toArray();
            RefundLogic::refund($afterSale->refund_way, $order, $afterSale->id, $afterSale->refund_total_amount);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 查看售后详情
     * @param $params
     * @return string[]
     * @author Tab
     * @date 2021/8/9 18:55
     */
    public static function detail($params)
    {
        // 售后信息
        $afterSale = self::afterSaleInfo($params);

        // 退货信息
        $returnGoodsInfo = self::returnGoodsInfo($afterSale);

        // 订单信息
        $orderInfo = self::orderInfo($afterSale);

        // 商品信息
        $goodsInfo = self::goodsInfo($afterSale);

        // 售后日志
        $afterSaleLog= self::afterSaleLog($afterSale);

        // 退款按钮
        $btns = self::btns($afterSale);

        return [
            'after_sale' => $afterSale,
            'return_goods_info' => $returnGoodsInfo,
            'order_info' => $orderInfo,
            'goods_info' => $goodsInfo,
            'after_sale_log' => $afterSaleLog,
            'btns' => $btns
        ];
    }

    /**
     * @notes 售后信息
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/8/9 20:36
     */
    public static function afterSaleInfo($params)
    {
        $field = 'id,order_id,user_id,sn,refund_type,refund_type as refund_type_desc,refund_method,refund_method  as refund_method_desc,status,status as status_desc,sub_status,refund_reason,refund_remark,refund_image,create_time,express_name,invoice_no,express_remark,express_image,voucher,express_time';
        $afterSale = AfterSale::field($field)->findOrEmpty($params['id'])->toArray();
        return $afterSale;
    }

    /**
     * @notes 退货信息
     * @param $afterSale
     * @return array
     * @author Tab
     * @date 2021/8/9 20:36
     */
    public static function returnGoodsInfo($afterSale)
    {
        $user = User::field('sn,nickname,mobile')->findOrEmpty($afterSale['user_id'])->toArray();
        return [
            'user_sn' => $user['sn'],
            'user_nickname' => $user['nickname'],
            'user_mobile' => $user['mobile']
        ];
    }

    /**
     * @notes 订单信息
     * @param $afterSale
     * @return array
     * @author Tab
     * @date 2021/8/9 20:36
     */
    public static function orderInfo($afterSale)
    {
        $field = 'order_status,order_status as order_status_desc,sn,order_type,order_type as order_type_desc,order_terminal,order_terminal as order_terminal_desc,create_time,pay_status,pay_status as pay_status_desc,pay_way,pay_way as pay_way_desc,pay_time,confirm_take_time,express_status';
        return Order::field($field)->append(['express_status_desc'])->findOrEmpty($afterSale['order_id'])->toArray();
    }

    /**
     * @notes 商品信息
     * @param $afterSale
     * @return array
     * @author Tab
     * @date 2021/8/9 20:39
     */
    public static function goodsInfo($afterSale)
    {
        // 商品信息
        $field = 'gi.goods_id,gi.image as item_image,gi.spec_value_str';
        $field .= ',g.name as goods_name,g.image as goods_image';
        $field .= ',og.goods_price,og.goods_num,og.original_price,og.total_price,og.change_price,og.discount_price,og.member_price,og.integral_price,og.total_pay_price';
        $field .= ',asg.refund_amount';
        $orderGoods = AfterSaleGoods::withoutGlobalScope()
            ->aliasSid('asg')
            ->alias('asg')
            ->leftJoin('goods g', 'g.id = asg.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = asg.item_id')
            ->leftJoin('order_goods og', 'og.id = asg.order_goods_id')
            ->field($field)
            ->where('asg.after_sale_id', $afterSale['id'])
            ->select()
            ->toArray();

        $goodsIds = array_column($orderGoods,'goods_id');
        $goodsSupplier = GoodsSupplier::withoutGlobalScope()->aliasSid('GS')->alias('GS')
            ->join('goods G','G.supplier_id = GS.id')
            ->where(['G.id'=>$goodsIds])
            ->column('GS.name','G.id');

        // 商品合计信息
        $orderGoodsSum['sum_goods_num'] = $orderGoodsSum['sum_total_price'] = $orderGoodsSum['sum_discount_price'] = $orderGoodsSum['sum_total_pay_price'] = $orderGoodsSum['sum_refund_amount'] = 0;
        foreach($orderGoods as &$item) {
            $orderGoodsSum['sum_goods_num'] += $item['goods_num'];
            $orderGoodsSum['sum_total_price'] += $item['total_price'];
            $orderGoodsSum['sum_discount_price'] += $item['discount_price'];
            $orderGoodsSum['sum_total_pay_price'] += $item['total_pay_price'];
            $orderGoodsSum['sum_refund_amount'] += $item['refund_amount'];
            $item['goods_image'] = get_image([$item['item_image'], $item['goods_image']]);
            $item['supplier_name'] = $goodsSupplier[$item['goods_id']] ?? '';
            $item['member_discount'] = 0;
            if($item['member_price'] > 0){
                $item['member_discount'] = round(($item['original_price'] - $item['member_price']) * $item['goods_num'],2);
            }
            $item['coupon_discount'] = $item['discount_price'];
            $item['integral_discount'] = $item['integral_price'];
            $item['total_discount'] = round( $item['member_discount']+$item['coupon_discount']+$item['integral_discount'],2);
            $item['total_amount'] = round($item['original_price'] * $item['goods_num'],2);
        }

        return [
            'order_goods' => $orderGoods,
            'order_goods_sum' => $orderGoodsSum,
        ];
    }

    /**
     * @notes 售后日志
     * @param $afterSale
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/9 20:42
     */
    public static function afterSaleLog($afterSale)
    {
        $field = 'operator_role,operator_id,content,create_time';
        $afterSaleLog = AfterSaleLog::field($field)
            ->where('after_sale_id', $afterSale['id'])
            ->order('id', 'desc')
            ->select()
            ->toArray();
        foreach($afterSaleLog as &$item) {
            $item['operator_name'] = AfterSaleLogEnum::getOpertorName($item['operator_id'], $item['operator_role']);
        }

        return $afterSaleLog;
    }

    /**
     * @notes 退款按钮
     * @param $afterSale
     * @return mixed
     * @author Tab
     * @date 2021/8/10 9:40
     */
    public static function btns($afterSale)
    {
        return AfterSaleEnum::getBtns($afterSale);
    }
}