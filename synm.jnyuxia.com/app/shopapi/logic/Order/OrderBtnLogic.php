<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\shopapi\logic\Order;


use app\common\enum\AfterSaleEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\Order;
use app\common\service\ConfigService;

/**
 * 订单按钮
 * Class OrderBtnLogic
 * @package app\shopapi\logic\Order
 */
class OrderBtnLogic extends BaseLogic
{

    /**
     * @notes 订单按钮状态
     * @param Order $order
     * @return array
     * @author 段誉
     * @date 2021/8/2 20:07
     */
    public static function getOrderBtn(Order $order)
    {
        return [
            'pay_btn'       => self::getPayBtn($order),
            'cancel_btn'    => self::getCancelBtn($order),
            'delivery_btn'  => self::getDeliveryBtn($order),
            'confirm_btn'   => self::getConfirmBtn($order),
            'finish_btn'    => self::getFinishBtn($order),
            'comment_btn'   => self::getCommentBtn($order),
            'refund_btn'    => self::getRefundBtn($order),
            'delete_btn'    => self::getDeletedBtn($order),
            'content_btn'   => self::getContentBtn($order),
        ];
    }


    /**
     * @notes 支付按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:25
     */
    public static function getPayBtn($order)
    {
        if ($order['order_status'] == OrderEnum::STATUS_WAIT_PAY && $order['pay_status'] == PayEnum::UNPAID) {
            return OrderEnum::BTN_SHOW;
        }
        return OrderEnum::BTN_HIDE;
    }


    /**
     * @notes 取消按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:25
     */
    public static function getCancelBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        //未支付的允许取消,订单已支付时，在允许取消的时间内并且订单未发货，允许取消
        if ($order['order_status'] == OrderEnum::STATUS_WAIT_PAY
            || $order['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY
        ) {
            $btn = OrderEnum::BTN_SHOW;
        }

        if ($order['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY) {
            $ableCancelOrder = ConfigService::get('transaction', 'cancel_unshipped_orders');
            if ($ableCancelOrder == YesNoEnum::NO) {
                $btn = OrderEnum::BTN_HIDE;
            }
            $configTime = ConfigService::get('transaction', 'cancel_unshipped_orders_times');
            $ableCancelTime = strtotime($order['create_time']) + ($configTime * 60);
            if (time() > $ableCancelTime) {
                $btn = OrderEnum::BTN_HIDE;
            }
        }
        return $btn;
    }


    /**
     * @notes 物流按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:25
     */
    public static function getDeliveryBtn($order)
    {
//        $btn = OrderEnum::BTN_HIDE;
//        if ($order['order_status'] >= OrderEnum::STATUS_WAIT_RECEIVE
//            && $order['pay_status'] == PayEnum::ISPAID
//            && $order['express_status'] == YesNoEnum::YES
//        ) {
//            $btn = OrderEnum::BTN_SHOW;
//        }
        $btn = OrderEnum::BTN_HIDE;
        if ($order['order_status'] >= OrderEnum::STATUS_WAIT_RECEIVE && $order['pay_status'] == PayEnum::ISPAID && $order['express_status'] == YesNoEnum::YES) {
            $btn = OrderEnum::BTN_SHOW;
            //如果是拼团订单，未成团的情况，不显示核销按钮
            if(OrderEnum::TEAM_ORDER == $order['order_type'] &&  1 != $order['is_team_success']){
                $btn = OrderEnum::BTN_HIDE;
            }
        }
        return $btn;
    }


    /**
     * @notes 确认收货按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:24
     */
    public static function getConfirmBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        if ($order['order_status'] == OrderEnum::STATUS_WAIT_RECEIVE && $order['express_status'] == YesNoEnum::YES) {
            $btn = OrderEnum::BTN_SHOW;
        }
        //虚拟订单确认收货
        if(OrderEnum::VIRTUAL_ORDER == $order['order_type'] && $order['order_status'] == OrderEnum::STATUS_WAIT_RECEIVE ){
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }

    /**
     * @notes 完成按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:24
     */
    public static function getFinishBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        if ($order['order_status'] == OrderEnum::STATUS_FINISH) {
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }

    /**
     * @notes 评论按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:24
     */
    public static function getCommentBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        $commentCount = 0;
        if ($order['pay_status'] == PayEnum::ISPAID && $order['order_status'] == OrderEnum::STATUS_FINISH) {
            $btn = OrderEnum::BTN_SHOW;
            foreach ($order->order_goods as $item) {
                if ($item['is_comment'] == 1) {
                    $commentCount += 1;
                }
            }
            if (count($order->order_goods) == $commentCount) {
                $btn = OrderEnum::BTN_HIDE;
            }
        }
        return $btn;
    }


    /**
     * @notes 申请退款按钮
     * @param $order
     * @return int
     * @author 段誉
     * @date 2021/8/2 20:24
     */
    public static function getRefundBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        //订单已完成、在售后期内。未申请退款、
        if ($order['order_status'] == OrderEnum::STATUS_FINISH && $order['after_sale_deadline'] > time()) {
            $checkRefund = AfterSale::where([
                ['order_id', '=', $order['id']],
                ['status', 'in', [AfterSaleEnum::STATUS_ING, AfterSaleEnum::STATUS_SUCCESS]]
            ])->order(['id' => 'desc'])->findOrEmpty();

            if ($checkRefund->isEmpty()) {
                $btn = OrderEnum::BTN_SHOW;
            }
        }
        return $btn;
    }


    /**
     * @notes 删除订单按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2021/10/13 7:06 下午
     */
    public static function getDeletedBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        //订单已关闭
        if ($order['order_status'] == OrderEnum::STATUS_CLOSE) {
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }

    /**
     * @notes 查看内容按钮
     * @param $order
     * @return int
     * @author cjhao
     * @date 2022/4/20 17:23
     */
    public static function getContentBtn($order){

        $btn = OrderEnum::BTN_HIDE;
        //虚拟订单，有发货内容
        if((OrderEnum::VIRTUAL_ORDER == $order['order_type']  && $order['delivery_content'] && in_array($order['order_status'],[OrderEnum::STATUS_WAIT_RECEIVE,OrderEnum::STATUS_FINISH]))){
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }

    /**
     * @notes 修改地址按钮
     * @param $order
     * @return int
     * @author cjhao
     * @date 2023/2/17 15:36
     */
    public static function getEitaddressBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        if(OrderEnum::STATUS_WAIT_PAY == $order['order_status'] || OrderEnum::STATUS_WAIT_DELIVERY == $order['order_status']) {
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }

    /**
     * @notes 去发货按钮
     * @param $order
     * @return int
     * @author cjhao
     * @date 2023/2/17 15:38
     */
    public static function getDeliverBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        if(OrderEnum::STATUS_WAIT_DELIVERY == $order['order_status']) {
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }


    /**
     * @notes 核销按钮
     * @param $order
     * @return int
     * @author cjhao
     * @date 2023/2/17 17:44
     */
    public static function getVerificationBtn($order)
    {
        $btn = OrderEnum::BTN_HIDE;
        if(PayEnum::ISPAID == $order['pay_status'] && OrderEnum::NOT_WRITTEN_OFF == $order['verification_status']) {
            $btn = OrderEnum::BTN_SHOW;
        }
        return $btn;
    }



}