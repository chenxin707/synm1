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


namespace app\common\service\pay;


use app\common\enum\AfterSaleLogEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\UserTerminalEnum;
use app\common\logic\PayNotifyLogic;
use app\common\logic\RefundLogic;
use app\common\model\IntegralOrder;
use app\common\model\Order;
use app\common\model\RechargeOrder;
use app\common\model\UserAuth;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use EasyWeChat\Payment\Application;


/**
 * 微信支付
 * Class WeChatPayService
 * @package app\common\server
 */
class WeChatPayService extends BasePayService
{
    /**
     * 授权信息
     * @var UserAuth|array|\think\Model
     */
    protected $auth;


    /**
     * 微信配置
     * @var
     */
    protected $config;


    /**
     * easyWeChat实例
     * @var
     */
    protected $pay;


    /**
     * 当前使用客户端
     * @var
     */
    protected $terminal;


    /**
     * 初始化微信配置
     * @param $terminal //用户终端
     * @param null $userId //用户id(获取授权openid)
     */
    public function __construct($terminal, $userId = null, $command = 0, $sid = 0)
    {
        $this->terminal = $terminal;
        if ($command === 0) {
            $this->config = WeChatConfigService::getWechatConfigByTerminal($terminal);
        } else {
            // 定时任务
            $this->config = RefundLogic::getWechatConfigByTerminalCommand($terminal, $sid);
        }

        $this->pay = Factory::payment($this->config);
        if ($userId !== null) {
            $this->auth = UserAuth::withoutGlobalScope()->where(['user_id' => $userId, 'terminal' => $terminal])->findOrEmpty();
        }
    }


    /**
     * @notes 发起微信支付统一下单
     * @param $from
     * @param $order
     * @return array|false|string
     * @author 段誉
     * @date 2021/8/4 15:05
     */
    public function pay($from, $order)
    {
        try {
            switch ($this->terminal) {
                case UserTerminalEnum::WECHAT_MMP:
                case UserTerminalEnum::WECHAT_OA:
                    $result = $this->jsapiPay($from, $order);
                    break;
                case UserTerminalEnum::IOS:
                case UserTerminalEnum::ANDROID:
                    $result = $this->appPay($from, $order);
                    break;
                case UserTerminalEnum::H5:
                    $result = $this->mwebPay($from, $order);
                    break;
                case UserTerminalEnum::PC:
                    $result = $this->pcPay($from, $order);
                    break;
                default:
                    throw new \Exception('支付方式错误');
            }
            return [
                'config' => $result,
                'pay_way' => PayEnum::WECHAT_PAY
            ];
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes jsapi支付(小程序,公众号)
     * @param $from
     * @param $order
     * @return array|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2021/8/4 15:05
     */
    public function jsapiPay($from, $order)
    {
        $check_source = [UserTerminalEnum::WECHAT_MMP, UserTerminalEnum::WECHAT_OA];
        if ($this->auth->isEmpty() && in_array($this->terminal, $check_source)) {
            throw new \Exception('获取授权信息失败');
        }
        $result = $this->pay->order->unify($this->getAttributes($from, $order));
        $this->checkResultFail($result);
        return $this->pay->jssdk->bridgeConfig($result['prepay_id'], false);
    }


    /**
     * @notes app支付(android,ios)
     * @param $from
     * @param $order
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2021/8/4 15:06
     */
    public function appPay($from, $order)
    {
        $result = $this->pay->order->unify($this->getAttributes($from, $order));
        $this->checkResultFail($result);
        return $this->pay->jssdk->appConfig($result['prepay_id'], false);
    }


    /**
     * @notes h5支付 (非微信环境下h5)
     * @param $from
     * @param $order
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2021/8/4 15:07
     */
    public function mwebPay($from, $order)
    {
        $result = $this->pay->order->unify($this->getAttributes($from, $order));
        $this->checkResultFail($result);
        $redirect_url = request()->domain() . '/mobile/bundle/pages/h5_pay_query/h5_pay_query?pay_way='.PayEnum::WECHAT_PAY;
        $redirect_url = urlencode($redirect_url);
        return $result['mweb_url'] . '&redirect_url=' . $redirect_url;
    }

    /** PC支付
     * @notes
     * @param $from
     * @param $order
     * @author Tab
     * @date 2021/11/30 16:24
     */
    public function pcPay($from, $order)
    {
        $result = $this->pay->order->unify($this->getAttributes($from, $order));
        $this->checkResultFail($result);
        // 返回信息由前端生成支付二维码
        return [
            'code_url' => $result['code_url'],
            'order_amount' => $order['order_amount']
        ];
    }


    /**
     * @notes 验证微信返回数据
     * @param $result
     * @throws \Exception
     * @author 段誉
     * @date 2021/8/4 14:56
     */
    public function checkResultFail($result)
    {
        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            if (isset($result['return_code']) && $result['return_code'] == 'FAIL') {
                throw new \Exception($result['return_msg']);
            }
            if (isset($result['err_code_des'])) {
                throw new \Exception($result['err_code_des']);
            }
            throw new \Exception('未知原因');
        }
    }


    /**
     * @notes 支付请求参数
     * @param $from
     * @param $order
     * @return array
     * @author 段誉
     * @date 2021/8/4 15:07
     */
    public function getAttributes($from, $order)
    {
        switch ($from) {
            case 'order':
                $attributes = [
                    'trade_type' => 'JSAPI',
                    'body' => '商品',
                    'total_fee' => $order['order_amount'] * 100, // 单位：分
                    'openid' => $this->auth['openid'],
                    'attach' => 'order',
                ];
                break;
            case 'recharge':
                $attributes = [
                    'trade_type' => 'JSAPI',
                    'body' => '充值',
                    'total_fee' => $order['order_amount'] * 100, // 单位：分
                    'openid' => $this->auth['openid'],
                    'attach' => 'recharge',
                ];
                break;
            case 'integral':
                $attributes = [
                    'trade_type' => 'JSAPI',
                    'body' => '积分商城',
                    'total_fee' => $order['order_amount'] * 100, // 单位：分
                    'openid' => $this->auth['openid'],
                    'attach' => 'integral',
                ];
                break;
        }

        //app支付类型
        if ($this->terminal == UserTerminalEnum::ANDROID || $this->terminal == UserTerminalEnum::IOS) {
            $attributes['trade_type'] = 'APP';
        }

        //h5支付类型
        if ($this->terminal == UserTerminalEnum::H5) {
            $attributes['trade_type'] = 'MWEB';
        }

        //NATIVE模式设置
        if ($this->terminal == UserTerminalEnum::PC) {
            $attributes['trade_type'] = 'NATIVE';
            $attributes['product_id'] = $order['sn'];
        }

        //修改微信统一下单,订单编号 -> 支付回调时截取前面的单号 18个
        //修改原因:回调时使用了不同的回调地址,导致跨客户端支付时(例如小程序,公众号)可能出现201,商户订单号重复错误
        $suffix = mb_substr(time(), -4);
        $attributes['out_trade_no'] = $order['sn'] . $attributes['trade_type'] . $this->terminal . $suffix;

        return $attributes;
    }


    /**
     * @notes 支付回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     * @author 段誉
     * @date 2021/8/13 14:19
     */
    public function notify()
    {
        $app = new Application($this->config);
        $response = $app->handlePaidNotify(function ($message, $fail) {

            if ($message['return_code'] !== 'SUCCESS') {
                return $fail('通信失败');
            }

            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                $extra['transaction_id'] = $message['transaction_id'];
                $attach = $message['attach'];
                $message['out_trade_no'] = mb_substr($message['out_trade_no'], 0, 18);
                switch ($attach) {
                    case 'order':
                        $order = Order::where(['sn' => $message['out_trade_no']])->findOrEmpty();
                        if ($order->isEmpty() || $order['pay_status'] >= PayEnum::ISPAID) {
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

                        PayNotifyLogic::handle('order', $message['out_trade_no'], $extra);
                        break;
                    case 'recharge':
                        $order = RechargeOrder::where(['sn' => $message['out_trade_no']])->findOrEmpty();
                        if($order->isEmpty() || $order->pay_status == PayEnum::ISPAID) {
                            return true;
                        }
                        PayNotifyLogic::handle('recharge', $message['out_trade_no'], $extra);
                        break;
                    case 'integral':
                        $order = IntegralOrder::where(['sn' => $message['out_trade_no']])->findOrEmpty();
                        if($order->isEmpty() || $order['pay_status'] != PayEnum::UNPAID) {
                            return true;
                        }
                        PayNotifyLogic::handle('integral', $message['out_trade_no'], $extra);
                        break;
                }
            } elseif ($message['result_code'] === 'FAIL') {
                // 用户支付失败

            }
            return true; // 返回处理完成

        });
        return $response->send();
    }


    /**
     * @notes 退款
     * @param $data //微信订单号、商户退款单号、订单金额、退款金额、其他参数
     * @return array|\EasyWeChat\Kernel\Support\Collection|false|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @author 段誉
     * @date 2021/8/4 14:57
     */
    public function refund($data)
    {
        if (!empty($data["transaction_id"])) {
            return $this->pay->refund->byTransactionId(
                $data['transaction_id'],
                $data['refund_sn'],
                $data['total_fee'],
                $data['refund_fee']
            );
        } else {
            return false;
        }
    }

}