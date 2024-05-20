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

namespace app\common\logic;

use app\common\service\WeChatConfigService;
use app\common\service\pay\{AliPayService, BalancePayService, WeChatPayService};
use app\common\model\{IntegralGoods, IntegralOrder, IntegralOrderRefund, User};
use app\common\enum\{AccountLogEnum, IntegralGoodsEnum, IntegralOrderEnum, IntegralOrderRefundEnum, PayEnum};
use think\Exception;


/**
 * 积分订单退款逻辑
 * Class OrderRefundLogic
 * @package app\common\logic
 */
class IntegralOrderRefundLogic extends BaseLogic
{

    /**
     * @notes 取消订单(标记订单状态,退回库存,扣减销量)
     * @param int $orderId
     * @author 段誉
     * @date 2022/4/1 10:09
     */
    public static function cancelOrder(int $orderId)
    {
        // 订单信息
        $order = IntegralOrder::findOrEmpty($orderId);
        $order->cancel_time = time();
        $order->order_status = IntegralOrderEnum::ORDER_STATUS_DOWN;
        $order->save();

        // 订单商品信息
        $goodsSnap = $order['goods_snap'];
        // 退回库存, 扣减销量
        IntegralGoods::where([['id', '=', $goodsSnap['id']], ['sales', '>=', $order['total_num']]])
            ->inc('stock', $order['total_num'])
            ->dec('sales', $order['total_num'])
            ->update();
    }


    /**
     * @notes 退回已支付积分
     * @param int $id
     * @return bool
     * @author 段誉
     * @date 2022/4/1 10:09
     */
    public static function refundOrderIntegral(int $id)
    {
        $order = IntegralOrder::findOrEmpty($id);
        if ($order['order_integral'] > 0) {
            // 退回积分
            User::where(['id' => $order['user_id']])
                ->inc('user_integral', $order['order_integral'])
                ->update();

            // 流水日志
            AccountLogLogic::add(
                $order['user_id'],
                AccountLogEnum::INTEGRAL_INC_CANCEL_INTEGRAL,
                AccountLogEnum::INC,
                $order['order_integral'],
                $order['sn']
            );

            // 更新积分订单表 已退积分
            IntegralOrder::where(['id' => $id])->update([
                'refund_integral' => $order['order_integral']
            ]);
        }
        return true;
    }


    /**
     * @notes 退回已支付金额
     * @param int $orderId
     * @return bool
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 16:00
     */
    public static function refundOrderAmount(int $orderId)
    {
        // 订单信息
        $order = IntegralOrder::findOrEmpty($orderId);
        // 订单商品信息
        $goodsSnap = $order['goods_snap'];

        //已支付的商品订单,取消,退款
        if ($goodsSnap['type'] != IntegralGoodsEnum::TYPE_GOODS
            || $order['refund_status'] != IntegralOrderEnum::NO_REFUND
            || $order['order_amount'] <= 0
        ) {
            return true;
        }
        // 退款记录
        $refund = self::addRefundLog($order, $order['order_amount'], IntegralOrderRefundEnum::STATUS_ING);
        switch ($order['pay_way']) {
            //余额退款
            case PayEnum::BALANCE_PAY:
                self::balancePayRefund($order, $refund);
                break;
            //微信退款
            case PayEnum::WECHAT_PAY:
                self::wechatPayRefund($order, $refund);
                break;
            //支付宝退款
            case PayEnum::ALI_PAY:
                self::aliPayRefund($order, $refund);
                break;
            default:
                throw new \Exception('支付方式异常');
        }
        // 更新订单退款状态为已退款
        IntegralOrder::where(['id' => $order['id']])->update([
            'refund_status' => IntegralOrderEnum::IS_REFUND,//订单退款状态; 0-未退款；1-已退款
            'refund_amount' => $order['order_amount'],
        ]);

        // 更新退款记录状态为退款成功
        IntegralOrderRefund::where(['id' => $order['id']])->update([
            'refund_status' => IntegralOrderRefundEnum::STATUS_SUCCESS
        ]);

        return true;
    }


    /**
     * @notes 增加退款记录
     * @param $order
     * @param $refundAmount
     * @param $status // 退款状态，0退款中，1完成退款，2退款失败
     * @param string $msg
     * @return IntegralOrderRefund|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 14:51
     */
    public static function addRefundLog($order, $refundAmount, $status, $msg = '')
    {
        return IntegralOrderRefund::duokaiCreate([
            'sn' => generate_sn(new IntegralOrderRefund(), 'sn'),
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'order_amount' => $order['order_amount'],
            'refund_amount' => $refundAmount,
            'transaction_id' => $order['transaction_id'],
            'create_time' => time(),
            'refund_status' => $status,
            'refund_time' => time(),
            'refund_msg' => json_encode($msg, JSON_UNESCAPED_UNICODE),
        ]);
    }


    /**
     * @notes 微信支付退款
     * @param $order
     * @param $refund_id
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @author 段誉
     * @date 2022/3/3 14:52
     */
    public static function wechatPayRefund($order, $refund)
    {
        $config = WeChatConfigService::getWechatConfigByTerminal($order['order_source']);

        if (!isset($config['cert_path']) || !isset($config['key_path'])) {
            throw new \Exception('请联系管理员设置微信证书!');
        }

        if (!file_exists($config['cert_path']) || !file_exists($config['key_path'])) {
            throw new \Exception('微信证书不存在,请联系管理员!');
        }

        $result = (new WeChatPayService($order['order_source']))->refund([
            'transaction_id' => $order['transaction_id'],
            'refund_sn' => $refund['sn'],
            'total_fee' => $refund['order_amount'] * 100,//订单金额,单位为分
            'refund_fee' => intval($refund['refund_amount'] * 100),//退款金额
        ]);

        if (isset($result['return_code']) && $result['return_code'] == 'FAIL') {
            throw new Exception($result['return_msg']);
        }

        if (isset($result['err_code_des'])) {
            throw new Exception($result['err_code_des']);
        }

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            //更新退款日志记录
            IntegralOrderRefund::where(['id' => $refund['id']])->update([
                'wechat_refund_id' => $result['refund_id'] ?? 0,
                'refund_msg' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ]);
        } else {
            throw new Exception('微信支付退款失败');
        }
    }


    /**
     * @notes 支付宝退款
     * @param $order
     * @param $refund
     * @throws Exception
     * @author 段誉
     * @date 2022/4/1 11:00
     */
    public static function aliPayRefund($order, $refund)
    {
        $result = (new AliPayService())->refund($order['sn'], $refund['refund_amount'], $refund['sn']);
        $result = (array)$result;
        if ($result['code'] == '10000' && $result['msg'] == 'Success' && $result['fundChange'] == 'Y') {
            //更新退款日志记录
            IntegralOrderRefund::where(['id' => $refund])->update([
                'refund_msg' => json_encode($result['httpBody'], JSON_UNESCAPED_UNICODE),
            ]);
        } else {
            throw new Exception('支付宝退款失败');
        }
    }


    /**
     * @notes 余额退款
     * @param $order
     * @author 段誉
     * @date 2022/4/1 11:01
     */
    public static function balancePayRefund($order, $refund)
    {
        BalancePayService::integralOrderRefund($order, $refund['refund_amount']);
    }

}