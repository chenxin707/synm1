<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likeshop_gitee/likeshop
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\common\logic;

use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\model\IntegralOrder;
use app\common\model\Order;
use app\common\model\PayConfig;
use app\common\model\PayWay;
use app\common\model\RechargeOrder;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\pay\AliPayService;
use app\common\service\pay\BalancePayService;
use app\common\service\pay\ToutiaoPayService;
use app\common\service\pay\WeChatPayService;
use app\common\service\RegionService;
use think\Exception;


/**
 * 支付逻辑
 * Class PaymentLogic
 * @package app\common\logic
 */
class PaymentLogic extends BaseLogic
{
    /**
     * @notes 支付
     * @param $payWay // 支付方式
     * @param $from //订单来源(商品订单?充值订单?其他订单?)
     * @param $order //订单信息
     * @param $terminal //终端
     * @return array|bool|string|void
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/29 14:49
     */
    public static function pay($payWay, $from, $order, $terminal)
    {
        //更新支付方式
        switch ($from) {
            case 'order':
                Order::duokaiUpdate(['pay_way' => $payWay], ['id' => $order['id']]);
                break;
            case 'recharge':
                RechargeOrder::duokaiUpdate(['pay_way' => $payWay], ['id' => $order['id']]);
                break;
            case 'integral':
                IntegralOrder::update(['pay_way' => $payWay], ['id' => $order['id']]);
                break;
        }

        if($order['order_amount'] == 0) {
            PayNotifyLogic::handle($from, $order['sn']);
            return ['pay_way'=>PayEnum::BALANCE_PAY];
        }

        switch ($payWay) {
            case PayEnum::BALANCE_PAY:
                //余额支付
                $payService = (new BalancePayService());
                $result = $payService->pay($from, $order);
                if (false !== $result) {
                    PayNotifyLogic::handle($from, $order['sn']);
                }
                break;
            case PayEnum::WECHAT_PAY:
                $payService = (new WeChatPayService($terminal, $order['user_id'] ?? null));
                $result = $payService->pay($from, $order);
                break;
            case PayEnum::ALI_PAY:
                $payService = (new AliPayService($terminal));
                $result = $payService->pay($from, $order);
                break;
            case PayEnum::BYTE_PAY:
                try {
                    $params = [
                        'order_id' => $order['id'],
                        'from' => $from,
                        'pay_way' => $payWay
                    ];
                    $result =  (new ToutiaoPayService())->createOrder($params);
                } catch (\Exception $e) {
                    self::$error = $e->getMessage();
                    $result = false;
                }
                break;
            default:
                self::$error = '订单异常';
                $result = false;
        }

        //支付成功, 执行支付回调
        if (false === $result && !self::hasError()) {
            self::setError($payService->getError());
        }
        return $result;
    }


    /**
     * @notes 获取准备预支付订单信息
     * @param $params
     * @return Order|array|false|\think\Model
     * @author 段誉
     * @date 2021/8/3 11:57
     */
    public static function getPayOrderInfo($params)
    {
        try {
            switch ($params['from']) {
                case 'order':
                    $order = Order::findOrEmpty($params['order_id']);
                    if ($order->isEmpty()
                        || $order['order_status'] == OrderEnum::STATUS_CLOSE
                        || $order['delete_time'] > 0)
                    {
                        throw new \Exception('订单已关闭');
                    }
                    break;
                case 'recharge':
                    $order = RechargeOrder::findOrEmpty($params['order_id']);
                    if($order->isEmpty()) {
                        throw new \Exception('充值订单不存在');
                    }
                    break;
                case 'integral':
                    $order = IntegralOrder::findOrEmpty($params['order_id']);
                    if($order->isEmpty()) {
                        throw new \Exception('积分订单不存在');
                    }
                    break;
            }

            if ($order['pay_status'] == PayEnum::ISPAID) {
                throw new \Exception('订单已支付');
            }
            return $order;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }




    //获取支付方式列表
    public static function getPayWay($userId, $terminal, $params)
    {
        try {
            // 获取待支付金额
            if ($params['from'] == 'order') {
                // 订单
                $order = Order::findOrEmpty($params['order_id'])->toArray();
            }
            if ($params['from'] == 'recharge') {
                // 充值
                $order = RechargeOrder::findOrEmpty($params['order_id'])->toArray();
            }
            if ($params['from'] == 'integral') {
                // 积分订单
                $order = IntegralOrder::findOrEmpty($params['order_id'])->toArray();
            }
            if (empty($order) || !isset($order)) {
                throw new \Exception('待支付订单不存在');
            }

            // 获取订单剩余支付时间
            $cancelUnpaidOrders = ConfigService::get('transaction', 'cancel_unpaid_orders');
            $cancelUnpaidOrdersTimes = ConfigService::get('transaction', 'cancel_unpaid_orders_times');

            if (empty($cancelUnpaidOrders)) {
                // 不自动取消待支付订单
                $cancelTime = 0;
            } else {
                // 指定时间内取消待支付订单
                $cancelTime = strtotime($order['create_time']) + intval($cancelUnpaidOrdersTimes) * 60;
            }
            //获取支付场景
            $scene = UserTerminalEnum::trueerminalEnumByScene($params['scene']);
            $pay_way = PayWay::withoutGlobalScope()
                ->aliasSid('pw')
                ->alias('pw')
                ->join('dev_pay dp', 'pw.dev_pay_id = dp.id')
                ->where(['pw.scene'=>$scene,'pw.status'=>YesNoEnum::YES])
                ->field('dp.id,dp.name,dp.pay_way,dp.icon,dp.sort,dp.remark')
                ->order('pw.is_default desc,dp.sort desc,id asc')
                ->select()
                ->toArray();
            foreach ($pay_way as $k=>&$item) {

                if ($item['pay_way'] == PayEnum::WECHAT_PAY) {
                    $item['extra'] = '微信快捷支付';
                }

                if ($item['pay_way'] == PayEnum::ALI_PAY) {
                    $item['extra'] = '支付宝快捷支付';
                }

                if ($item['pay_way'] == PayEnum::BALANCE_PAY) {
                    $user_money = User::where(['id' => $userId])->value('user_money');
                    $item['extra'] = '可用余额:'.$user_money;
                }
                // 充值时去除余额支付
                if ($params['from'] == 'recharge' && $item['pay_way'] == PayEnum::BALANCE_PAY) {
                    unset($pay_way[$k]);
                }
                $item['icon'] = empty($item['icon']) ? '' : FileService::getFileUrl($item['icon']);
            }

            return [
                'lists' => array_values($pay_way),
                'order_amount' => $order['order_amount'],
                'cancel_time' => $cancelTime,
            ];
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 查看订单支付状态
     * @param $params
     * @return array|false
     * @author Tab
     * @date 2021/11/30 15:54
     */
    public static function getPayStatus($params)
    {
        try {
            if ($params['from'] == 'order') {
                $order = Order::with('orderGoods')->findOrEmpty($params['order_id']);
                $payStatus = $order->pay_status;
                $orderInfo = '';
                if ($order->order_terminal == UserTerminalEnum::PC) {
                    $orderInfo = self::formatOrder($order); // PC商城
                }
            } else if ($params['from'] == 'recharge') {
                $order = RechargeOrder::findOrEmpty($params['order_id']);
                $orderInfo = ''; // 充值无需返回订单详情
                $payStatus =  $order->getData('pay_status');
            }else if ($params['from'] == 'integral') {
                $order = IntegralOrder::findOrEmpty($params['order_id']);
                $orderInfo = '';
                $payStatus =  $order->getData('pay_status');
            }
            if ($order->isEmpty()) {
                throw new \Exception('订单不存在');
            }
            return [
                'pay_status' => $payStatus,
                'pay_way' => $order->pay_way,
                'order' => $orderInfo
            ];
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 获取PC商城支付信息页订单信息
     * @author Tab
     * @date 2021/11/30 15:41
     */
    public static function formatOrder($order)
    {
        $data['sn'] = $order->sn;
        $data['order_amount'] = $order->order_amount;
        $data['address']['contact'] = $order->address->contact;
        $data['address']['mobile'] = $order->address->mobile;
        $data['address']['address'] = RegionService::getAddress([
            $order->address->province,
            $order->address->city,
            $order->address->district,
        ], $order->address->address);
        $data['order_goods'] = [];
        foreach ($order->orderGoods as $key => $item) {
            $data['order_goods'][] = '[商品' . ($key + 1) . ']-'. $item->goods_snap->goods_name . ' 规格:' . $item->goods_snap->spec_value_str;
        }
        return $data;
    }

    /**
     * @notes 获取支付结果
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/4/6 15:23
     */
    public static function getPayResult($params)
    {
        switch ($params['from']) {
            case 'order' :
                $result = Order::where(['id' => $params['order_id']])
                    ->field(['id', 'sn', 'pay_time', 'pay_way', 'order_amount', 'pay_status'])
                    ->findOrEmpty()
                    ->toArray();
                $result['total_amount'] = '￥' . $result['order_amount'];
                break;
            case 'integral':
                $result = IntegralOrder::where(['id' => $params['order_id']])
                    ->field(['id', 'sn', 'pay_time', 'pay_way', 'pay_status','order_amount', 'order_integral', 'exchange_way'])
                    ->findOrEmpty()
                    ->toArray();
                if (!empty($result)) {
                    $order_integral = $result['order_integral'] > 0 ? $result['order_integral'] . '积分' : '';
                    $order_amount = $result['order_amount'] > 0 ? '+ ￥' . $result['order_amount'] . '元' : '';
                    $result['total_amount'] = $order_integral . $order_amount;
                }
                break;
            default :
                $result = [];
        }

        if (empty($result)) {
            self::$error = '订单信息不存在';
        }

        $result['pay_way'] = PayEnum::getPayDesc($result['pay_way']);
        return $result;
    }
}