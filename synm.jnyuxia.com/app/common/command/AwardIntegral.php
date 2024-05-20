<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\command;


use app\common\enum\AccountLogEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\Order;
use app\common\model\User;
use app\common\service\ConfigService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

class AwardIntegral extends Command
{
    protected function configure()
    {
        $this->setName('award_integral')
            ->setDescription('结算消费赠送积分');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $time = time();

            $ableAuto = ConfigService::get('transaction', 'automatically_confirm_receipt');
            $confirmTime = ConfigService::get('transaction', 'automatically_confirm_receipt_days') * 24 * 60 * 60;

            $orders = Order::withoutGlobalScope()->where([
                ['award_integral_event', '>', 0],
                ['award_integral', '>', 0],
                ['is_award_integral', '=', 0],
                ['pay_status', '=', 1],
                ['order_status', 'in', [1,2,3]]
            ])->select()->toArray();

            foreach ($orders as $order) {
                if ($order['award_integral_event'] == OrderEnum::INTEGRAL_ORDER_DELIVERY && $order['order_status'] < OrderEnum::STATUS_WAIT_RECEIVE) {
                    continue;
                }
                if ($order['award_integral_event'] == OrderEnum::INTEGRAL_ORDER_FINISH && $order['order_status'] != OrderEnum::STATUS_FINISH) {
                    continue;
                }
                if ($order['award_integral_event'] == OrderEnum::INTEGRAL_ORDER_AFTER_SALE_OVER) {
                    if ($ableAuto == YesNoEnum::YES && ($order['order_status'] != OrderEnum::STATUS_FINISH || ($confirmTime + $order['confirm_take_time']) > $time)) {
                        continue;
                    }
                    if ($ableAuto == YesNoEnum::NO && $order['order_status'] != OrderEnum::STATUS_FINISH) {
                        continue;
                    }
                }

                //增加用户积分
                $user = User::withoutGlobalScope()->findOrEmpty($order['user_id']);
                $user->user_integral = $user->user_integral + $order['award_integral'];
                $user->duokaiSave([], null, false, false);
                //修改订单信息
                $order = Order::withoutGlobalScope()->findOrEmpty($order['id']);
                $order->is_award_integral = 1;
                $order->duokaiSave([], null, false, false);
                //账户变动记录
                AccountLogLogic::add(
                    $order['user_id'],
                    AccountLogEnum::INTEGRAL_INC_AWARD,
                    AccountLogEnum::INC,
                    $order['award_integral'],
                    $order['sn'],
                    '',
                    [],
                    $order['sid']
                );
            }

        } catch (\Exception $e) {
            Log::write('结算消费赠送积分异常:'.$e->getMessage());
        }
    }
}