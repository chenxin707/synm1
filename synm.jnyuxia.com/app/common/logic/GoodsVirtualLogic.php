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
namespace app\common\logic;
use app\common\enum\DefaultEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\GoodsEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\model\Delivery;
use app\common\model\Order;
use app\common\model\OrderLog;
use app\shopapi\logic\Order\OrderLogic;

class GoodsVirtualLogic
{
    /**
     * @notes 虚拟商品下单后更新订单状态
     * @param $orderIds
     * @author cjhao
     * @date 2022/4/19 17:34
     */
    public static function afterPayVirtualDelivery($orderIds){
        $orderList = Order::where(['id'=>$orderIds])->with(['order_goods'])->select()->toArray();
        foreach ($orderList as $order){
            $updateData = [];
            if(OrderEnum::VIRTUAL_ORDER != $order['order_type']){
                continue;
            }
            $orderGoods = array_shift($order['order_goods']);
            if(empty($orderGoods)){
                continue;
            }
            $goodsSnap = $orderGoods['goods_snap'];
            //实物商品，不处理
            if(GoodsEnum::GOODS_REALITY == $goodsSnap->type){
                continue;
            }

            //自动发货
            if(GoodsEnum::AFTER_PAY_AUTO == $goodsSnap->after_pay){
                $updateData['delivery_content'] = $goodsSnap->delivery_content ?? '';
                //更新数据
                $updateData = array_merge($updateData,[
                    'order_status'      => OrderEnum::STATUS_WAIT_RECEIVE,
                    'express_status'    => DeliveryEnum::NOT_SHIPPED,
                    'express_time'      => time(),
                    'delivery_id'       => 0,
                ]);
                //自动完成订单
                if(GoodsEnum::AFTER_DELIVERY_AUTO == $goodsSnap->after_delivery){
                    $updateData['order_status'] = OrderEnum::STATUS_FINISH;
                    $updateData['confirm_take_time'] = time();
                    $updateData['after_sale_deadline'] = OrderLogic::getAfterSaleDeadline();

                    //记录日志
                    (new OrderLog())->record([
                        'type' => OrderLogEnum::TYPE_SYSTEM,
                        'channel' => OrderLogEnum::USER_CONFIRM_ORDER,
                        'order_id' => $order['id']
                    ]);
                }
            }

            //更新订单状态
            $updateData && Order::where(['id'=>$order['id']])->update($updateData);

        }
    }

}