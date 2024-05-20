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

namespace app\common\service\after_sale;

use app\adminapi\logic\marketing\CouponLogic;
use app\common\enum\AfterSaleEnum;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\RefundLogic;
use app\common\model\AfterSale;
use app\common\model\AfterSaleLog;
use app\common\model\AfterSaleGoods;
use app\common\model\Coupon;
use app\common\model\CouponList;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\service\ConfigService;

/**
 * 售后服务类
 * Class AfterSaleService
 * @package app\common\service
 */
class AfterSaleService
{
    /**
     * @notes  整单退款
     * 调用者需开启事务以保证数据写入的一致性
     * @param $params array order_id 订单号 、 scene 场景 1-买家取消订单 2-卖家取消订单 3-支付加调时订单已取消
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/2 10:36
     */
    public static function orderRefund($params)
    {
        // 校验是否允许发起整单退款
        self::checkCondition($params);

        // 生成售后记录
        self::createAfterSale($params);
    }

    /**
     * @notes 校验是否允许发起整单退款
     * @param $orderId 订单号
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/2 9:26
     */
    public static function checkCondition($params)
    {
        $order = Order::withoutGlobalScope()->findOrEmpty($params['order_id']);

        if($order->isEmpty()) {
            throw new \think\Exception('订单不存在,无法发起整单退款');
        }
        $order = $order->toArray();
        if($order['pay_status'] != YesNoEnum::YES) {
            throw new \think\Exception('订单未付款,不允许发起整单退款');
        }

        if($order['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY && $order['order_status'] != OrderEnum::STATUS_CLOSE) {
            // 订单已关闭的情况之一：用户完成支付完后但第三方未及时调用我方支付回调，有可能此时用户手动或后台手动取消订单了订单
            throw new \think\Exception('订单已发货,不允许发起整单退款');
        }

        $aferSale = AfterSale::withoutGlobalScope()
            ->where([
            ['order_id', '=', $order['id']],
            ['status', '=', AfterSaleEnum::STATUS_SUCCESS]
        ])->select()->toArray();
        if($aferSale) {
            throw new \think\Exception('该订单已售后成功, 不能重复发起售后');
        }

        $aferSale = AfterSale::withoutGlobalScope()
            ->where([
            ['order_id', '=', $order['id']],
            ['status', '=', AfterSaleEnum::STATUS_ING]
        ])->select()->toArray();
        if($aferSale) {
            throw new \think\Exception('该订单已在售后中，请耐心等待');
        }
    }

    /**
     * @notes 生成售后记录
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/2 10:38
     */
    public static function createAfterSale($params)
    {
        $order = Order::withoutGlobalScope()->findOrEmpty($params['order_id'])->toArray();
        $sn = generate_sn(((new AfterSale())->duokaiWithoutGlobalScope()->where('sid', $order['sid'])), 'sn');
        $sid  = $order['sid'];
        // 生成售后主表记录
        $data = [
            'sn' => $sn,
            'user_id' => $order['user_id'],
            'order_id' => $order['id'],
            'refund_reason' => AfterSaleLogEnum::getSenceDesc($params['scene']),
            'refund_remark' => '系统发起整单退款',
            'refund_type' => AfterSaleEnum::REFUND_TYPE_ORDER,
            'refund_method' => AfterSaleEnum::METHOD_ONLY_REFUND,
            'refund_way' => AfterSaleEnum::REFUND_WAYS_ORIGINAL,
            'refund_total_amount' => $order['order_amount'],
            'status' => AfterSaleEnum::STATUS_ING,
            'sub_status' => AfterSaleEnum::SUB_STATUS_SELLER_REFUND_ING,
            'refund_status' => AfterSaleEnum::NO_REFUND,
            'sid' => $sid
        ];

        $afterSale = AfterSale::duokaiCreate($data, [], false, '', false);

        // 生成售后商品记录
        $orderGoods = OrderGoods::withoutGlobalScope()->where('order_id', $order['id'])->select()->toArray();
        $data = [];
        foreach($orderGoods as $item) {
            $data[] = [
                'after_sale_id' => $afterSale->id,
                'order_goods_id' => $item['id'],
                'goods_id' => $item['goods_id'],
                'item_id' => $item['item_id'],
                'goods_price' => $item['goods_price'],
                'goods_num' => $item['goods_num'],
                'refund_amount' => $item['total_pay_price'],
                'sid' => $sid,
            ];
        }
        (new AfterSaleGoods())->duokaiSaveAll($data, true, false, false);

        // 生成售后日志
        $msg = ',系统发起整单退款';
        switch($params['scene']) {
            case AfterSaleLogEnum::BUYER_CANCEL_ORDER:
                $content = AfterSaleLogEnum::getSenceDesc(AfterSaleLogEnum::BUYER_CANCEL_ORDER) . $msg;
                self::createAfterLog($afterSale->id, $content, null, AfterSaleLogEnum::ROLE_SYS);
                break;
            case AfterSaleLogEnum::SELLER_CANCEL_ORDER:
                $content = AfterSaleLogEnum::getSenceDesc(AfterSaleLogEnum::SELLER_CANCEL_ORDER) . $msg;
                self::createAfterLog($afterSale->id, $content, null, AfterSaleLogEnum::ROLE_SYS);
                break;
            case AfterSaleLogEnum::ORDER_CLOSE:
                $content = AfterSaleLogEnum::getSenceDesc(AfterSaleLogEnum::ORDER_CLOSE) . $msg;
                self::createAfterLog($afterSale->id, $content, null, AfterSaleLogEnum::ROLE_SYS);
                break;
        }
        // 区分是否来自定时任务
        $command = isset($params['command']) ? 1 : 0;
        // 整单退款
        RefundLogic::refund(AfterSaleEnum::REFUND_WAYS_ORIGINAL, $order, $afterSale->id, $order['order_amount'], $command);
    }

    /**
     * @notes 生成售后日志
     * @param $type
     * @param $afterSaleId
     * @param $content
     * @param null $operatorId
     * @author Tab
     * @date 2021/8/2 9:57
     */
    public static function createAfterLog($afterSaleId, $content, $operatorId = null,$operatorRole = null)
    {
        $afterSale = AfterSale::withoutGlobalScope()->findOrEmpty($afterSaleId);
        $data = [
            'after_sale_id' => $afterSaleId,
            'content' => $content,
            'operator_id' => $operatorId,
            'operator_role' => $operatorRole,
            'sid' => $afterSale->sid,
        ];

        AfterSaleLog::duokaiCreate($data, [], false, '', false);
    }

    /**
     * @notes 退还库存
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/2 10:34
     */
    public static function returnInventory($params)
    {
        $orderGoods = OrderGoods::where(['order_id' => $params['order_id']])->select()->toArray();

        foreach($orderGoods as $item) {
            $goods = Goods::findOrEmpty($item['goods_id']);
            if($goods->isEmpty()){
                continue;
            }
            $goods->total_stock = $goods->total_stock + $item['goods_num'];
            $goods->duokaiSave();
            
            $goodsItem = GoodsItem::findOrEmpty($item['item_id']);
            $goodsItem->stock = $goodsItem->stock + $item['goods_num'];
            $goodsItem->duokaiSave();
        }
    }

    /**
     * @notes 退还优惠券
     * @param $order
     * @author Tab
     * @date 2021/8/18 15:36
     */
    public static function returnCoupon($order)
    {
        if(empty($order['coupon_list_id'])) {
           return false;
        }
        $couponList = CouponList::findOrEmpty($order['coupon_list_id'])->toArray();
        // 重新发放一张新优惠券
        CouponLogic::send([
            'id' => $couponList['coupon_id'],
            'send_user_num' => 1,
            'send_user' => [$order['user_id']],
        ]);
    }
}