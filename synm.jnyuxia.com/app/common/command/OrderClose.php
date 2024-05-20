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

namespace app\common\command;


use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\model\Config;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderLog;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\ConfigService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

/**
 * 关闭超时待付款订单
 * Class OrderClose
 * @package app\common\command
 */
class OrderClose extends Command
{

    protected function configure()
    {
        $this->setName('order_close')
            ->setDescription('系统关闭超时未付款订单');
    }

    protected function execute(Input $input, Output $output)
    {
        $orders = Order::withoutGlobalScope()
            ->where([
                'order_status' => OrderEnum::STATUS_WAIT_PAY,
                'pay_status' => PayEnum::UNPAID,
            ])->select();


        if (empty($orders)) {
            return true;
        }

        try{

            foreach ($orders as $order) {
                // 自动取消订单设置
                $ableClose = Config::withoutGlobalScope()
                    ->where([
                    'type' => 'transaction',
                    'name' => 'cancel_unpaid_orders',
                    'sid' => $order['sid'],
                ])->value('value');
                if (empty($ableClose)) {
                    // 未开启自动取消订单
                    continue;
                }
                $cancelTime = Config::withoutGlobalScope()
                    ->where([
                        'type' => 'transaction',
                        'name' => 'cancel_unpaid_orders_times',
                        'sid' => $order['sid'],
                    ])->value('value');
                $orderCreateTime = strtotime($order['create_time']);
                if ($orderCreateTime + (int)$cancelTime * 60 > time()) {
                    // 未到自动取消时间
                    continue;
                }
                //回退订单商品库存
                $this->rollbackGoods($order);

                //更新订单状态
                $this->updateOrderStatus($order);
            }

        } catch(\Exception $e) {
            Log::write('订单自动关闭失败,失败原因:' . $e->getMessage());
        }
    }



    /**
     * @notes 回退库存
     * @param $order
     * @author 段誉
     * @date 2021/9/15 14:32
     */
    protected function rollbackGoods($order)
    {
        $orderGoods = OrderGoods::withoutGlobalScope()
            ->field('goods_id, item_id, goods_num')
            ->where('order_id', $order->id)
            ->select()
            ->toArray();

        foreach ($orderGoods as $good) {
            $currentGoods = Goods::withoutGlobalScope()
                ->findOrEmpty($good['goods_id']);
            if (!$currentGoods->isEmpty()) {
                $currentGoods->total_stock = $currentGoods->total_stock + $good['goods_num'];
                $currentGoods->duokaiSave([], null, false, false);
            }

            $currentItem = GoodsItem::withoutGlobalScope()
                ->findOrEmpty($good['item_id']);
            if (!$currentItem->isEmpty()) {
                $currentItem->stock = $currentItem->stock + $good['goods_num'];
                $currentItem->duokaiSave([], null, false, false);
            }
        }
    }



    /**
     * @notes 更新订单状态
     * @param $order
     * @author 段誉
     * @date 2021/9/15 14:32
     */
    protected function updateOrderStatus($order)
    {
        //更新订单状态
        Order::duokaiUpdate([
            'id' => $order['id'],
            'order_status' => OrderEnum::STATUS_CLOSE
        ], [], [], '', false);

        // 订单日志
        OrderLog::duokaiCreate([
            'type' => OrderLogEnum::TYPE_SYSTEM,
            'channel' => OrderLogEnum::SYSTEM_CANCEL_ORDER,
            'order_id' => $order['id'],
            'operator_id' => 0,
            'content'       => OrderLogEnum::getRecordDesc(OrderLogEnum::SYSTEM_CANCEL_ORDER),
            'sid' => $order['sid']
        ], [], false, '', false);
    }
}