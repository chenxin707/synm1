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

// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\common\service\pay;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Config;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\UserTerminalEnum;
use app\common\logic\PayNotifyLogic;
use app\common\model\IntegralOrder;
use app\common\model\Order;
use app\common\model\PayConfig;
use app\common\model\RechargeOrder;
use app\common\service\after_sale\AfterSaleService;
use think\facade\Log;

/**
 * 支付宝支付
 * Class AliPayService
 * @package app\common\server
 */
class AliPayService extends BasePayService
{

    /**
     * 用户客户端
     * @var
     */
    protected $terminal;

    /**
     * 支付实例
     * @var
     */
    protected $pay;

    /**
     * 初始化设置
     * AliPayService constructor.
     * @throws \Exception
     */
    public function __construct($terminal = null, $sid = 0)
    {
        //设置用户终端
        $this->terminal = $terminal;
        //初始化支付配置
        Factory::setOptions($this->getOptions($sid));
        $this->pay = Factory::payment();
    }


    /**
     * @notes 支付设置
     * @return Config
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/7/28 17:43
     */
    public function getOptions($sid = 0)
    {
        if ($sid === 0) {
            $config = (new PayConfig())->where(['pay_way' => PayEnum::ALI_PAY])->find();
        } else {
            // 此处主要定时任务用到
            $config = (new PayConfig())->duokaiWithoutGlobalScope()->where([
                'pay_way' => PayEnum::ALI_PAY,
                'sid' => $sid,
            ])->find();
        }

        if (empty($config)) {
            throw new \Exception('请配置好支付设置');
        }
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
//        $options->gatewayHost = 'openapi.alipaydev.com'; //测试沙箱地址
        $options->signType = 'RSA2';
        $options->appId = $config['config']['app_id'] ?? '';
        // 应用私钥
        $options->merchantPrivateKey = $config['config']['private_key'] ?? '';
        //支付宝公钥
        $options->alipayPublicKey = $config['config']['ali_public_key'] ?? '';
        //回调地址
        $options->notifyUrl = (string)url('pay/aliNotify', [], false, true);
        return $options;
    }


    /**
     * @notes 支付
     * @param $from //订单来源;order-商品订单;recharge-充值订单
     * @param $order //订单信息
     * @return false|string[]
     * @author 段誉
     * @date 2021/8/13 17:08
     */
    public function pay($from, $order)
    {
        try {
            switch ($this->terminal) {
                case UserTerminalEnum::PC:
                    $result = $this->pagePay($from, $order);
                    break;
                case UserTerminalEnum::IOS:
                case UserTerminalEnum::ANDROID:
                    $result = $this->appPay($from, $order);
                    break;
                case UserTerminalEnum::H5:
                    $result = $this->wapPay($from, $order);
                    break;
                default:
                    throw new \Exception('支付方式错误');
            }
            return [
                'config' => $result,
                'pay_way' => PayEnum::ALI_PAY
            ];
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * Notes: 支付回调
     * @param $data
     * @return bool
     * @author 段誉(2021/3/22 17:22)
     */
    public function notify($data)
    {
        try {
            $verify = $this->pay->common()->verifyNotify($data);
            if (false === $verify) {
                throw new \Exception('异步通知验签失败');
            }
            if (!in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                return true;
            }
            $extra['transaction_id'] = $data['trade_no'];
            //验证订单是否已支付
            switch ($data['passback_params']) {
                case 'order':
                    $order = Order::where(['sn' => $data['out_trade_no']])->findOrEmpty();
                    if (!$order || $order['pay_status'] >= PayEnum::ISPAID) {
                        return true;
                    }

                    //特殊情况：用户在前端支付成功的情况下，调用回调接口之前，订单被关闭
                    if ($order['order_status'] == OrderEnum::STATUS_CLOSE) {
                        //更新订单支付状态为已支付
                        Order::duokaiUpdate(['pay_status' => PayEnum::ISPAID],['id'=>$order['id']]);

                        //发起售后
                        AfterSaleService::orderRefund([
                            'order_id' => $order['id'],
                            'scene' =>  AfterSaleLogEnum::ORDER_CLOSE
                        ]);

                        return true;
                    }

                    PayNotifyLogic::handle('order', $data['out_trade_no'], $extra);
                    break;
                case 'recharge':
                    $order = RechargeOrder::where(['sn' => $data['out_trade_no']])->findOrEmpty();
                    if($order->isEmpty() || $order->pay_status == PayEnum::ISPAID) {
                        return true;
                    }
                    PayNotifyLogic::handle('recharge', $data['out_trade_no'], $extra);
                    break;
                case 'integral':
                    $order = IntegralOrder::where(['sn' => $data['out_trade_no']])->findOrEmpty();
                    if($order->isEmpty() || $order['pay_status'] != PayEnum::UNPAID) {
                        return true;
                    }
                    PayNotifyLogic::handle('integral', $data['out_trade_no'], $extra);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            $record = [
                __CLASS__,
                __FUNCTION__,
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ];
            Log::write(implode('-', $record));
            $this->setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes PC支付
     * @param $attach //附加参数(在回调时会返回)
     * @param $order //订单信息
     * @return string
     * @author 段誉
     * @date 2021/7/28 17:34
     */
    public function pagePay($attach, $order)
    {
        $domain = request()->domain();
        $result = $this->pay->page()->optional('passback_params', $attach)->pay(
            '订单:' . $order['sn'],
            $order['sn'],
            $order['order_amount'],
            $domain . '/pc/user/order'
        );
        return $result->body;
    }


    /**
     * @notes APP支付
     * @param $attach //附加参数(在回调时会返回)
     * @param $order //订单信息
     * @return string
     * @author 段誉
     * @date 2021/7/28 17:34
     */
    public function appPay($attach, $order)
    {
        $result = $this->pay->app()->optional('passback_params', $attach)->pay(
            $order['sn'],
            $order['sn'],
            $order['order_amount']
        );
        return $result->body;
    }


    /**
     * @notes 手机网页支付
     * @param $attach //附加参数(在回调时会返回)
     * @param $order //订单信息
     * @return string
     * @author 段誉
     * @date 2021/7/28 17:34
     */
    public function wapPay($attach, $order)
    {
        $domain = request()->domain();
        $url = $domain . '/mobile/pages/index/index';
        if ($attach == 'order') {
            $url = $domain . '/mobile/pages/order_list/order_list';
        } else if ($attach == 'recharge') {
            $url = $domain . '/mobile/bundle/pages/user_recharge/user_recharge';
        }
        $result = $this->pay->wap()->optional('passback_params', $attach)->pay(
            '订单:' . $order['sn'],
            $order['sn'],
            $order['order_amount'],
            $url,
            $url
        );
        return $result->body;
    }


    /**
     * @notes 查询订单
     * @param $orderSn
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeQueryResponse
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/28 17:36
     */
    public function checkPay($orderSn)
    {
        return $this->pay->common()->query($orderSn);
    }


    /**
     * @notes 退款
     * @param $orderSn
     * @param $order_amount
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeRefundResponse
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/28 17:37
     */
    public function refund($orderSn, $orderAmount, $outRequestNo)
    {
        return $this->pay->common()->optional('out_request_no', $outRequestNo)->refund($orderSn, $orderAmount);
    }

    /**
     * @notes 查询退款
     * @author Tab
     * @date 2021/9/13 11:38
     */
    public function queryRefund($orderSn, $refundSn)
    {
        return $this->pay->common()->queryRefund($orderSn, $refundSn);
    }
}

