<?php

namespace app\common\service\pay;

use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\PayNotifyLogic;
use app\common\model\Config;
use app\common\model\Order;
use app\common\model\RechargeOrder;
use app\common\model\Refund;
use app\common\service\ConfigService;
use think\Exception;

class ToutiaoPayService
{
    private $config;

    public function __construct($sid = 0)
    {
        $this->config = $this->getConfig($sid);

        if (empty($this->config['appid']) || empty($this->config['secret'])) {
            throw new \Exception("请先在后台配置appid和secret");
        }
    }

    /**
     * @notes 获取配置
     * @return array
     * @author Tab
     * @date 2021/11/12 9:21
     */
    private function getConfig($sid = 0)
    {
        if ($sid === 0) {
            return [
                'appid' => ConfigService::get("toutiao", "appid", ''),
                'secret' => ConfigService::get("toutiao", "secret", ''),
                'access_token' => ConfigService::get("toutiao", "access_token", ''),
                'expires_in' => ConfigService::get("toutiao", "expires_in", ''),
                'expires_in_time' => ConfigService::get("toutiao", "expires_in_time", ''),
                'pay_salt' => ConfigService::get("toutiao", "pay_salt", ''),
            ];
        }

        // 主要用于定时任务
        return [
            'appid' => Config::withoutGlobalScope()->where(['type' => 'toutiao', 'name' => 'appid', 'sid' => $sid])->value('value'),
            'secret' => Config::withoutGlobalScope()->where(['type' => 'toutiao', 'name' => 'secret', 'sid' => $sid])->value('value'),
            'access_token' => Config::withoutGlobalScope()->where(['type' => 'toutiao', 'name' => 'access_token', 'sid' => $sid])->value('value'),
            'expires_in' => Config::withoutGlobalScope()->where(['type' => 'toutiao', 'name' => 'expires_in', 'sid' => $sid])->value('value'),
            'expires_in_time' => Config::withoutGlobalScope()->where(['type' => 'toutiao', 'name' => 'expires_in_time', 'sid' => $sid])->value('value'),
            'pay_salt' => Config::withoutGlobalScope()->where(['type' => 'toutiao', 'name' => 'pay_salt', 'sid' => $sid])->value('value'),
        ];

    }

    /**
     * @notes 获取access_token
     * 官方：access_token 最多2小时即过期，重复获取 access_token 会导致上次 access_token失效
     * 官方：为了平滑过渡，新老 access_token 在 5 分钟内都可使用
     * @return mixed
     * @throws \Exception
     * @author Tab
     * @date 2021/11/12 9:25
     */
    public function getAccessToken()
    {
        // 已获取过access_token并且仍在有效期
        if (!empty($this->config['access_token']) && $this->config['expires_in_time'] > time()) {
            return $this->config['access_token'];
        }
        // 重新获取access_token
        $url = 'https://developer.toutiao.com/api/apps/v2/token';
        $data = [
            "appid" =>   $this->config['appid'],
            "secret" =>   $this->config['secret'],
            "grant_type" =>   'client_credential' // 固定值
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        // 获取成功
        if ($result['err_no'] == 0) {
            // 获取成功存入数据库
            ConfigService::set('toutiao', 'access_token', $result['data']['access_token']);
            // expires_in : access_token 有效时间，单位：秒
            ConfigService::set('toutiao', 'expires_in', $result['data']['expires_in']);
            // 有效期比官方缩短10分钟，提前去重新获取access_token
            $expires_in_time = time() + $result['data']['expires_in'] - 600;
            ConfigService::set('toutiao', 'expires_in_time', $expires_in_time);
            return $result['data']['access_token'];
        }

        // 获取失败
        throw new \Exception("access_token获取失败：" . $result['err_tips']);
    }

    /**
     * @notes post请求
     * 官方：参数应以 JSON 字符串形式写入， 需要设置请求头 "content-type": "application/json"
     * @param $url
     * @param $data
     * @return bool|string
     * @throws \Exception
     * @author Tab
     * @date 2021/11/12 9:25
     */
    public function http_post($url,$data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length:' . strlen($data)));
        $result = curl_exec($curl);
        if(curl_errno($curl)) {
            throw new \Exception('Errno'.curl_errno($curl));
        }
        curl_close($curl);
        return $result;
    }

    /**
     * @notes 预下单
     * $params 需包含3个元素: order_id 订单id  from 来源类型(order/recharge) pay_way 支付方式
     * @author Tab
     * @date 2021/11/12 17:19
     */
    public function createOrder($params)
    {
        $order = $this->getOrderInfo($params);

        // 核心字段
        $map = [
            // 添加from前缀，避免订单表与充值表单号冲突
            "out_order_no" => strtolower($params['from']).$order['sn'],
            "total_amount" => $order['order_amount'] * 100,
            "subject" =>   $order['sn']."预下单",
            "body" =>   $order['sn']."预下单",
            "valid_time" => 24 * 3600,
            "cp_extra" => strtolower($params['from']),
        ];

        $url = 'https://developer.toutiao.com/api/apps/ecpay/v1/create_order';
        $data = [
            "app_id" =>   $this->config['appid'],
            // 订单号
            "out_order_no" =>  $map['out_order_no'],
            // 支付金额,单位为：分
            "total_amount" =>   $map['total_amount'],
            // 商品描述; 长度限制 128 字节，不超过 42 个汉字
            "subject" =>   $map['subject'],
            // 商品详情
            "body" =>  $map['body'],
            //  订单过期时间(秒); 最小 15 分钟，最大两天
            "valid_time" => $map['valid_time'],
            // 开发者自定义字段，回调原样回传
            "cp_extra" => $map['cp_extra'],
            // 核心字段签名
            "sign" => $this->sign($map)
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        if ($result['err_no'] == 0) {
            // 返回给前端的orderInfo
            return [
                "config" => $result["data"],
                "pay_way" => $params['pay_way']
            ];
        }
        throw new \Exception("错误码:".$result['err_no'].";出错原因：".$result['err_tips']);
    }

    /**
     * @notes 统一回调接口(支付、退款、分账)
     * @author Tab
     * @date 2021/11/17 11:41
     */
    public function notify($params)
    {
        switch ($params['type']) {
            // 支付回调
            case "payment":
                return $this->paymentNotify($params);
            // 退款回调
            case "refund":
                return $this->refundNotify($params);
            // 分账回调
            case "settle":
                return $this->settleNotify($params);
        }
    }

    /**
     * @notes 支付回调
     * @author Tab
     * @date 2021/11/18 9:40
     */
    public function paymentNotify($params)
    {
        // 验证请求是否来自字节小程序平台服务端
        $msg = json_decode($params['msg'], true);
        if ($msg['cp_extra'] != 'order' && $msg['cp_extra'] != 'recharge') {
            // 与预下单时自定义的字段不同，不是预想的回调请求
            return false;
        }
        // 支付成功
        if ($msg['status'] == 'SUCCESS') {
            // 提取订单号
            $orderSn = $this->getOrderSn($msg);
            $result = PayNotifyLogic::handle($msg['cp_extra'], $orderSn, ['transaction_id' => $msg['payment_order_no']]);
            return $result === true ? $this->processSuccess() : false;
        }
        return false;
    }

    /**
     * @notes 退款回调
     * @author Tab
     * @date 2021/11/18 9:41
     */
    public function refundNotify($params)
    {
        // 验证请求是否来自字节小程序平台服务端
        $msg = json_decode($params['msg'], true);
        if ($msg['cp_extra'] != 'tk') {
            // 与预下单时自定义的字段不同，不是预想的回调请求
            return false;
        }
        unset($msg['appid']);
        $offset = strlen($msg['cp_extra']);
        $sn = substr($msg['cp_refundno'], $offset);
        Refund::duokaiUpdate(['refund_msg' => json_encode($msg)], ['sn' => $sn]);
        return $this->processSuccess();
    }

    /**
     * @notes 分账回调
     * @author Tab
     * @date 2021/11/18 9:43
     */
    public function settleNotify()
    {

    }

    /**
     * @notes 提取订单号
     * @param $msg
     * @author Tab
     * @date 2021/11/18 11:06
     */
    public function getOrderSn($msg)
    {
        $offset = strlen($msg['cp_extra']);
        return substr($msg['cp_orderno'], $offset);
    }

    /**
     * @notes 获取订单信息
     * @param $params
     * @author Tab
     * @date 2021/11/18 9:58
     */
    public function getOrderInfo($params)
    {
        switch ($params['from']) {
            case "order":
                $order = Order::findOrEmpty($params['order_id']);
                break;
            case "recharge":
                $order = RechargeOrder::findOrEmpty($params['order_id']);
                break;
        }
        if ($order->isEmpty()) {
            throw new \Exception("订单不存在");
        }
        if ($order->pay_status == PayEnum::ISPAID) {
            throw new \Exception("订单已支付，请勿重复预下单");
        }
        return $order->toArray();
    }

    /**
     * @notes 退款
     * @author Tab
     * @date 2021/11/18 14:10
     */
    public function refund($order, $refundAmount, $refundOrder)
    {
        // 核心字段
        $map = [
            // 商户分配订单号，标识进行退款的订单
            'out_order_no' => 'order' . $order['sn'],
            // 商户分配退款号
            'out_refund_no' => 'tk' . $refundOrder['sn'],
            // 退款原因
            'reason' => '订单退款',
            // 退款金额，单位[分]
            'refund_amount' => $refundAmount * 100,
            // 开发者自定义字段，回调原样回传
            'cp_extra' => 'tk',
            // 是否为分账后退款，1-分账后退款；0-分账前退款。分账后退款会扣减可提现金额，请保证余额充足
            'all_settle' => 0,
        ];

        $url = 'https://developer.toutiao.com/api/apps/ecpay/v1/create_refund';
        $data = [
            "app_id" =>   $this->config['appid'],
            'out_order_no' => $map['out_order_no'],
            'out_refund_no' => $map['out_refund_no'],
            'reason' => $map['reason'],
            'refund_amount' => $map['refund_amount'],
            'cp_extra' => $map['cp_extra'],
            'all_settle' => $map['all_settle'],
            "sign" => $this->sign($map)
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        if ($result['err_no'] == 0) {
            Refund::duokaiUpdate([
                'id' => $refundOrder['id'],
                'transaction_id' => $refundOrder['refund_no'],
                'refund_msg' => json_encode($result),
            ], [], [], '', false);
            return true;
        }
        throw new \Exception("错误码:".$result['err_no'].";出错原因：".$result['err_tips']);
    }

    /**
     * @notes 请求加签
     * @author Tab
     * @date 2021/11/17 11:45
     */
    public function sign($map)
    {
        if (empty($this->config['pay_salt'])) {
            throw new \Exception("后台请配置支付SALT");
        }
        $rList = array();
        foreach($map as $k =>$v) {
            if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0,1)=="\"" && substr($value,$len, $len-1)=="\"")
                $value = substr($value,1, $len-1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        array_push($rList, $this->config['pay_salt']);
        sort($rList, 2);
        return md5(implode('&', $rList));
    }

    /**
     * @notes 查询订单
     * @return mixed
     * @throws \Exception
     * @author Tab
     * @date 2021/11/17 19:08
     */
    public function queryOrder($orderId, $from)
    {
        if ($from == 'order') {
            $order = Order::findOrEmpty($orderId);
        }
        if ($order->isEmpty()) {
            throw new \Exception("订单不存在");
        }
        if ($order->pay_status == PayEnum::ISPAID) {
            throw new \Exception("订单已支付，无需查询");
        }
        $order = $order->toArray();
        $url = 'https://developer.toutiao.com/api/apps/ecpay/v1/query_order';
        $map = [
            'out_order_no' => $order['sn']
        ];
        $data = [
            "app_id" =>   $this->config['appid'],
            "out_order_no" =>   $map['out_order_no'],
            "sign" =>   $this->sign($map),
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        // 查询成功
        if ($result['err_no'] == 0) {
            // 返回整个查询结果
            return $result;
        }

        // 查询失败
        throw new \Exception("查询失败：" . $result['err_tips']);
    }

    /**
     * @notes 查询退款
     * @author Tab
     * @date 2021/11/18 14:43
     */
    public function queryRefund($refundSn)
    {
        // 核心字段
        $map = [
            'out_refund_no' => 'tk'.$refundSn
        ];

        $url = 'https://developer.toutiao.com/api/apps/ecpay/v1/query_refund';
        $data = [
            "app_id" =>   $this->config['appid'],
            'out_refund_no' => $map['out_refund_no'],
            "sign" => $this->sign($map)
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        if ($result['err_no'] == 0) {
            if ($result['refundInfo']['refund_status'] == 'SUCCESS') {
                return true;
            }
            if ($result['refundInfo']['refund_status'] == 'FAIL') {
                return false;
            }
        }
        return null;
    }

    /**
     * @notes 回调处理成功信息
     * @return array
     * @author Tab
     * @date 2021/11/18 9:36
     */
    public function processSuccess()
    {
        $data = [
            "err_no" => 0,
            "err_tips" => "success",
        ];
        return json_encode($data);
    }



    public function getQrcode(array $param){

        //获取access_token
        $accessToken = $this->getAccessToken();

        $url = 'https://developer.toutiao.com/api/apps/qrcode';
        $data = [
            "access_token"  => $accessToken,
            "path"          => $param['page'],
            "appname"       => $param['appname']
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = $this->http_post($url,$data);
        $error = json_decode($result,true);
        if($error){
            throw new Exception($error['errmsg']);
        };
        $mpBase64 = chunk_split(base64_encode($result));
        $contents = 'data:image/png;base64,' . $mpBase64;
        return $contents;
    }
}