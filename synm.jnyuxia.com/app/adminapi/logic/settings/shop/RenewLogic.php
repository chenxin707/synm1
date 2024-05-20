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

namespace app\adminapi\logic\settings\shop;

use app\common\enum\PayEnum;
use app\common\enum\SetMealLogEnum;
use app\common\logic\BaseLogic;
use app\common\model\Config;
use app\common\model\PayConfig;
use app\common\model\PlatformShop;
use app\common\model\SetMeal;
use app\common\model\SetMealLog;
use app\common\model\SetMealOrder;
use app\common\model\SetMealPrice;
use EasyWeChat\Factory;
use EasyWeChat\Payment\Application;
use think\Exception;
use think\facade\Db;
use think\facade\Log;
use Alipay\EasySDK\Kernel\Factory as AliFactory;
use Alipay\EasySDK\Kernel\Config as AliConfig;


/**
 * 店铺续费
 */
class RenewLogic extends BaseLogic
{
    /**
     * @notes 续费页面
     */
    public static function index() {
        $mealId = PlatformShop::where('id', request()->sid)->value('set_meal_id');
        $meal = SetMeal::findOrEmpty($mealId)->toArray();
        $mealPrice = SetMealPrice::where('set_meal_id', $mealId)->select()->toArray();
        foreach($mealPrice as &$item) {
            switch($item['time_type']) {
                case SetMealLogEnum::MONTH:
                    $item['time_desc'] = $item['time'] . '个月';
                    break;
                case SetMealLogEnum::YEAR:
                    $item['time_desc'] = $item['time'] . '年';
                    break;
                case SetMealLogEnum::FOREVER:
                    $item['time_desc'] = '永久';
                    break;
            }
        }
        $payWays = self::payWays();
        return [
            'meal_name' => $meal['name'],
            'meal_prices' => $mealPrice,
            'pay_ways' => $payWays
        ];
    }

    /**
     * @notes 支付方式
     */
    public static function payWays() {
        $payConfig = PayConfig::withoutGlobalScope()->where('sid', 0)->select();
        $tmp = [];
        foreach($payConfig as $config) {
            if (in_array($config['pay_way'], [PayEnum::WECHAT_PAY, PayEnum::ALI_PAY])) {
                unset($config['config']);
                $tmp['online'][] = $config;
            }
            if (in_array($config['pay_way'], [PayEnum::TRANSFER_PAY])) {
                unset($config['config']);
                $tmp['offline'][] = $config;
            }
        }
        return $tmp;
    }

    /**
     * @notes 获取商户当前套餐
     */
    public static function setMeal() {
        $shop = PlatformShop::findOrEmpty(request()->sid)->toArray();
        $setMealName = SetMeal::where('id', $shop['set_meal_id'])->value('name');
        if (strtotime($shop['expires_time']) == 4102415999) {  // 永久
            $shop['expires_time'] = '永久(' .$shop['expires_time'] . ')';
        }
        return [
            'set_meal_name' => $setMealName,
            'expires_time' => $shop['expires_time']
        ];
    }

    /**
     * @notes 提交续费订单
     */
    public static function placeOrder($params, $adminId) {
        Db::startTrans();
        try {
            $setMealPrice = SetMealPrice::findOrEmpty($params['set_meal_price_id'])->toArray();
            $order = SetMealOrder::duokaiCreate([
                'sn' => self::sn(),
                'set_meal_id' => $setMealPrice['set_meal_id'],
                'set_meal_price_snapshot' => json_encode($setMealPrice),
                'pay_way' => $params['pay_way'],
                'operator_id' => $adminId,
            ]);
            if ($params['pay_way'] == PayEnum::TRANSFER_PAY) {
                Db::commit();
                return [
                    'order_id' => $order->id,
                    'pay_way' => $params['pay_way'],
                ];
            } else {
                // 非线下付款方式
                $result =  self::prepay([
                    'order_id' => $order->id,
                    'pay_way' => $order->pay_way,
                    'redirect_url' => $params['redirect_url']??'',
                ]);
                $result['order_id'] = $order->id;
                $result['pay_way'] = $params['pay_way'];
                Db::commit();
                return $result;
            }
        } catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 订单详情
     */
    public static function detail($params) {
        $data  = SetMealOrder::withoutGlobalScope()
            ->alias('smo')
            ->leftJoin('platform_shop ps', 'smo.sid = ps.id')
            ->field('smo.sid,smo.id,smo.pay_status,smo.order_status,smo.sn,smo.pay_way,smo.create_time,smo.pay_time,smo.remark,smo.set_meal_price_snapshot,ps.name as shop_name, ps.expires_time')
            ->append(['pay_status_desc', 'order_status_desc', 'pay_way_desc', 'pay_time_desc', 'set_meal_name', 'time_desc', 'price', 'shop_logo', 'expires_time_desc'])
            ->where('smo.id', $params['order_id'])
            ->findOrEmpty()
            ->toArray();
        return $data;
    }

    /**
     * @notes 取消支付
     */
    public static function cancel($params) {
        try {
            $setMealOrder = SetMealOrder::findOrEmpty($params['order_id']);
            if ($setMealOrder->order_status != 0) {  // 判断是否为未支付
                throw new Exception('未付款的订单才能取消支付');
            }
            $setMealOrder->order_status = 2; // 已关闭
            $setMealOrder->duokaiSave();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 获取续费订单支付状态
     */
    public static function payStatus($params) {
        $setMealOrder = SetMealOrder::where('id', $params['order_id'])->findOrEmpty();
        if ($setMealOrder->isEmpty()) {
            return ['pay_status' => 0];
        }
        return ['pay_status' => $setMealOrder->pay_status];
    }



    /**
     * @notes 生成订单编号
     */
    public static function sn() {
        $sn =  date('YmdHis') . substr(time(), 6);
        $order = SetMealOrder::withoutGlobalScope()->where('sn', $sn)->findOrEmpty();
        if ($order->isEmpty()) {
            return $sn;
        }
        self::sn();
    }

    /**
     * @notes 预支付
     */
    public static function prepay($params) {
        $order = self::getOrder($params['order_id']);
        if ($order->isEmpty()) {
            throw new Exception('查询不到订单信息');
        }
        self::updatePayWay($order, $params['pay_way']);
        if ($params['pay_way'] == PayEnum::TRANSFER_PAY) {
            throw new Exception('对公线下转账请联系客服');
        }
        $order = $order->toArray();
        $order['set_meal_price_snapshot'] = json_decode($order['set_meal_price_snapshot']);
        switch ($params['pay_way']) {
            case PayEnum::WECHAT_PAY:
                return self::wechatPay($order);
            case PayEnum::ALI_PAY:
                return [
                    'order_amount' =>  $order['set_meal_price_snapshot']->price,
                    'code_url' => self::aliPay($order, $params['redirect_url'])
                ];
        }

    }

    /**
     * @notes 获取续费订单
     */
    public static function getOrder($orderId) {
        return SetMealOrder::findOrEmpty($orderId);
    }

    /**
     * @notes 更新支付方式
     */
    public static function updatePayWay($order, $payWay) {
        $order->pay_way = $payWay;
        $order->duokaiSave();
    }

    /**
     * @notes 微信扫码支付
     */
    public static function wechatPay($order) {
        $config = self::getPlatformWechatConfig();
        $easyWechatPay = Factory::payment($config);
        $result = $easyWechatPay->order->unify([
            'body' => '套餐续费',
            'total_fee' => $order['set_meal_price_snapshot']->price * 100, // 单位：分
            'openid' => '',
            'attach' => 'SetMealOrder',
            'trade_type' => 'NATIVE',
            'product_id' => $order['id'],
            'out_trade_no' => $order['id']
        ]);
        SetMealOrder::duokaiUpdate([
            'id' => $order['id'],
            'prepay_result' => json_encode($result)
        ], [], [], '', false);
        self::checkResultFail($result);
        // 返回信息由前端生成支付二维码
        return [
            'code_url' => $result['code_url'],
            'order_amount' => $order['set_meal_price_snapshot']->price
        ];
    }

    /**
     * @notes 支付宝扫码支付
     */
    public static function aliPay($order, $redirectUrl) {
        AliFactory::setOptions(self::getOptions());
        $pay = AliFactory::payment();
        $domain = request()->domain();
        $result = $pay->page()->optional('passback_params', 'SetMealOrder')->pay(
            '套餐续费:' . $order['id'],
            $order['id'],
            $order['set_meal_price_snapshot']->price,
            $domain . $redirectUrl
        );
        return $result->body;
    }

    /**
     * @notes 获取平台微信支付配置
     */
    public static function getPlatformWechatConfig() {
        $appid = Config::withoutGlobalScope()->where(['type' => 'official_account', 'name' => 'app_id', 'sid' => 0])->value('value');
        $secret = Config::withoutGlobalScope()->where(['type' => 'official_account', 'name' => 'app_secret', 'sid' => 0])->value('value');
        $notify_url = (string)url('settings.shop.renew/notifyOa', [], false, true);

        $pay = PayConfig::withoutGlobalScope()->where(['pay_way' => PayEnum::WECHAT_PAY, 'sid' => 0])->findOrEmpty()->toArray();
        if (empty($pay) || empty($appid) || empty($secret)) {
            throw new Exception('暂时无法支付，请联系客服');
        }
        //判断是否已经存在证书文件夹，不存在则新建
        if (!file_exists(app()->getRootPath().'runtime/certificate')) {
            mkdir(app()->getRootPath().'runtime/certificate', 0775, true);
        }
        //写入文件
        $apiclient_cert = $pay['config']['apiclient_cert'] ?? '';
        $apiclient_key = $pay['config']['apiclient_key'] ?? '';
        $cert_path = app()->getRootPath().'runtime/certificate/'.md5('0' . $apiclient_cert).'.pem';
        $key_path = app()->getRootPath().'runtime/certificate/'.md5('0' .$apiclient_key).'.pem';
        if (!file_exists($cert_path)) {
            $fopen_cert_path = fopen($cert_path, 'w');
            fwrite($fopen_cert_path, $apiclient_cert);
            fclose($fopen_cert_path);
        }
        if (!file_exists($key_path)) {
            $fopen_key_path = fopen($key_path, 'w');
            fwrite($fopen_key_path, $apiclient_key);
            fclose($fopen_key_path);
        }

        $config = [
            'app_id' => $appid,
            'secret' => $secret,
            'mch_id' => $pay['config']['mch_id'] ?? '',
            'key' => $pay['config']['pay_sign_key'] ?? '',
            'cert_path' => $cert_path,
            'key_path' => $key_path,
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => '../runtime/log/wechat.log'
            ],
            'notify_url' => $notify_url
        ];

        return $config;
    }

    /**
     * @notes 校验预支付结果
     */
    public static function checkResultFail($result) {
        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            if (isset($result['return_code']) && $result['return_code'] == 'FAIL') {
                throw new Exception($result['return_msg']);
            }
            if (isset($result['err_code_des'])) {
                throw new Exception($result['err_code_des']);
            }
            throw new Exception('未知原因');
        }
    }

    /**
     * @notes 微信扫码支付回调
     */
    public static function notifyOa() {
        $config = self::getPlatformWechatConfig();
        $app = new Application($config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            Log::write("微信扫码支付回调" . json_encode($message));
            if ($message['return_code'] !== 'SUCCESS') {
                return $fail('通信失败');
            }
            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                self::payHandle($message);
            } elseif ($message['result_code'] === 'FAIL') {
                // 用户支付失败
            }
            return true; // 返回处理完成

        });
        return $response->send();
    }

    /**
     * @notes 支付成功后的操作
     */
    public static function payHandle($params) {
        Db::startTrans();
        try {
            // 更新续费订单
            $setMealOrder = SetMealOrder::findOrEmpty($params['out_trade_no']);
            if ($setMealOrder->isEmpty() || $setMealOrder->pay_status == PayEnum::ISPAID) {
                Db::commit();
                return true;
            }
            $setMealOrder->pay_time = time();
            $setMealOrder->pay_status = PayEnum::ISPAID;
            $setMealOrder->order_status = 1; // 已完成
            $setMealOrder->pay_postback_result = json_encode($params);
            $setMealOrder->transaction_id = $params['transaction_id'];
            $setMealOrder->duokaiSave();

            $setMealOrder = $setMealOrder->toArray();
            $setMealOrder['set_meal_price_snapshot'] = json_decode($setMealOrder['set_meal_price_snapshot'], true);
            $newSetMeal = SetMeal::findOrEmpty($setMealOrder['set_meal_price_snapshot']['set_meal_id'])->toArray();
            $shop = PlatformShop::findOrEmpty(request()->sid);
            $originSetMeal = SetMeal::findOrEmpty($shop['set_meal_id'])->toArray();
            $originExpiresTime = $shop->getData('expires_time');
            // 计算到期时间
            switch($setMealOrder['set_meal_price_snapshot']['time_type']) {
                case 1: // 月
                    if ($shop->set_meal_id == $newSetMeal['id']) { // 相同套餐，到期时间+续费时间
                        $expires_time = $originExpiresTime + (strtotime('+'. (int)$setMealOrder['set_meal_price_snapshot']['time'] . 'months') - time());
                    } else {  // 不同套餐，当前时间+续费时间
                        $expires_time = strtotime('+'. (int)$setMealOrder['set_meal_price_snapshot']['time'] . 'months');
                    }
                    break;
                case 2: // 年
                    if ($shop->set_meal_id == $newSetMeal['id']) { // 相同套餐，到期时间+续费时间
                        $expires_time = $originExpiresTime + (strtotime('+'. (12 * (int)$setMealOrder['set_meal_price_snapshot']['time']) . 'months') - time());
                    } else {  // 不同套餐，当前时间+续费时间
                        $expires_time = strtotime('+'. (12 * (int)$setMealOrder['set_meal_price_snapshot']['time']) . 'months');
                    }
                    break;
                case 3: // 永久
                    $expires_time = 4102415999; // 2099-12-31 23:59:59
                    break;
            }
            // 更新店铺套餐信息
            $shop->set_meal_id = $newSetMeal['id'];
            $shop->expires_time = $expires_time;
            $shop->duokaiSave();

            // 添加续费记录
            SetMealLog::duokaiCreate([
                'type' => SetMealLogEnum::TYPE_SHOP,
                'operator_id' => $setMealOrder['operator_id'],
                'origin_set_meal_id' => $originSetMeal['id'],
                'set_meal_id' => $newSetMeal['id'],
                'set_meal_order_id' => $setMealOrder['id'],
                'origin_set_meal_name' => $originSetMeal['name'],
                'set_meal_name' => $newSetMeal['name'],
                'origin_expires_time' => $originExpiresTime,
                'expires_time' => $expires_time,
                'content' => '商户续费',
                'create_time' => time(),
                'channel' => 801,
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::write("支付后操作错误" . $e->getMessage());
        }
    }

    /**
     * @notes 获取支付宝配置
     */
    public static function getOptions() {
        $config = (new PayConfig())->duokaiWithoutGlobalScope()->where([
            'pay_way' => PayEnum::ALI_PAY,
            'sid' => 0, // 平台
        ])->findOrEmpty();
        if (empty($config)) {
            throw new \Exception('请配置好支付设置');
        }
        $options = new AliConfig();
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
        $options->notifyUrl = (string)url('settings.shop.renew/notifyAli', [], false, true);
        return $options;
    }


    /**
     * @notes 支付宝回调
     */
    public static function notifyAli($data) {
        Log::write('支付宝回调结果:' . json_encode($data));
        try {
//            AliFactory::setOptions(self::getOptions());
//            $pay = AliFactory::payment();
//            $verify = $pay->common()->verifyNotify($data);
//            if (false === $verify) {
//                throw new Exception('异步通知验签失败');
//            }
            if (!in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                return true;
            }

            self::payHandle([
                'out_trade_no' => $data['out_trade_no'],
                'transaction_id' => $data['trade_no'],
                'data' => $data
            ]);
            return true;
        } catch (\Exception $e) {
            Log::write('支付宝回调错误:' . $e->getMessage());
            return false;
        }
    }

    /**
     * @notes 确认支付
     */
    public static function confirmPay($params) {
        Db::startTrans();
        try {
            if ($params['pay_way'] == PayEnum::TRANSFER_PAY) {
                SetMealOrder::duokaiUpdate([
                    'id' => $params['order_id'],
                    'pay_way' => $params['pay_way']
                ], [], [], '', false);
                Db::commit();
                return [];
            } else {
                // 非线下付款方式
                $result =  self::prepay([
                    'order_id' => $params['order_id'],
                    'pay_way' => $params['pay_way'],
                    'redirect_url' => $params['redirect_url']??'',
                ]);
                Db::commit();
                return $result;
            }
        } catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

}
