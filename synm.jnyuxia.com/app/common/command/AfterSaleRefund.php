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

use app\common\enum\AccountLogEnum;
use app\common\enum\AfterSaleEnum;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\BargainEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\UserTerminalEnum;
use app\common\model\AccountLog;
use app\common\model\AfterSale;
use app\common\model\AfterSaleLog;
use app\common\model\BargainInitiate;
use app\common\model\Config;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\PayConfig;
use app\common\model\Refund;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\ConfigService;
use app\common\service\pay\AliPayService;
use app\common\service\pay\ToutiaoPayService;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

/**
 * 售后退款查询
 * 只查询微信退款、支付宝退款两种退款方式，退回余额方式是马上到账的得到售后结果的，无需查询
 * Class DistributionSettlement
 * @package app\common\command
 */
class AfterSaleRefund extends Command
{
    protected function configure()
    {
        $this->setName('after_sale_refund')
            ->setDescription('售后退款查询');
    }

    protected function execute(Input $input, Output $output)
    {
        // 查找售后中：售后退款中记录
        $afterSaleList = AfterSale::withoutGlobalScope()->where('sub_status', AfterSaleEnum::SUB_STATUS_SELLER_REFUND_ING)->select()->toArray();

        if(empty($afterSaleList)) {
            return false;
        }
        foreach($afterSaleList as $item) {
            switch ($item['refund_way']) {
                // 原路退回
                case AfterSaleEnum::REFUND_WAYS_ORIGINAL:
                    $result = self::originalRefund($item);
                    break;
                default:
                    $result = null;
            }

            // 退款成功
            if($result === true) {
                self::afterSuccess($item);
                continue;
            }
            // 退款失败
            if($result === false) {
                self::afterFail($item);
                continue;
            }
        }

    }


    /**
     * @notes 查询微信退款是否成功
     * @param $item
     * @author Tab
     * @date 2021/9/11 11:33
     */
    public static function checkWechatRefund($item)
    {
        $order = Order::withoutGlobalScope()->findOrEmpty($item['order_id'])->toArray();
        $wechatConfig = self::getWechatConfigByTerminal($order['order_terminal'], $item['sid']);
        if (!isset($wechatConfig['cert_path']) || !isset($wechatConfig['key_path'])) {
            Log::write('定时任务微信退款查询失败：请联系管理员设置微信证书');
            return null;
        }

        if (!file_exists($wechatConfig['cert_path']) || !file_exists($wechatConfig['key_path'])) {
            Log::write('定时任务微信退款查询失败：微信证书不存在,请联系管理员');
            return null;
        }
        $app = Factory::payment($wechatConfig);
        // 获取售后单对应的退款记录
        $refund = Refund::withoutGlobalScope()->where([
            ['after_sale_id', '=', $item['id']],
            ['sid', '=', $item['sid']],
        ])->findOrEmpty();
        // 根据商户退款单号查询退款
        $result = $app->refund->queryByOutRefundNumber($refund->sn);
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['refund_status_0'] == 'SUCCESS') {
            // 退款成功
            return true;
        }
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['refund_status_0'] == 'REFUNDCLOSE') {
            // 退款失败
            return false;
        }

        // 其他情况,将查询结果写入到退款记录中
        $refund->refund_msg = json_encode($result, JSON_UNESCAPED_UNICODE);
        $refund->duokaiSave([], null, false, false);

        return null;
    }

    /**
     * @notes 查询支付宝退款是否成功
     * @param $item
     * @author Tab
     * @date 2021/9/13 10:34
     */
    public static function checkAliRefund($item)
    {
        $order = Order::withoutGlobalScope()->findOrEmpty($item['order_id'])->toArray();
        // 获取售后单对应的退款记录
        $refund = Refund::withoutGlobalScope()->where([
            ['after_sale_id', '=' ,$item['id']],
            ['sid', '=' ,$item['sid']],
        ])->findOrEmpty()->toArray();
        $result = (new AliPayService(null, $item['sid']))->queryRefund($order['sn'], $refund['sn']);
        $result = $result->toMap();
        if ($result['code'] == '10000' && $result['msg'] == 'Success' && $result['refund_status'] == 'REFUND_SUCCESS') {
            // 退款成功
            return true;
        }

        // 退款查询请求未收到 或 退款失败
        return null;
    }

    /**
     * @notes 校验字节退款
     * @param $item
     * @throws \Exception
     * @author Tab
     * @date 2021/11/18 14:51
     */
    public static function checkByteRefund($item)
    {
        // 获取售后单对应的退款记录
        $refund = Refund::withoutGlobalScope()->where([
            'after_sale_id' => $item['id'],
            'sid' => $item['sid']
        ])->findOrEmpty()->toArray();
        return (new ToutiaoPayService($item['sid']))->queryRefund($refund['sn']);
    }

    /**
     * @notes 计算退款状态
     * @param $item
     * @return int
     * @author Tab
     * @date 2021/8/18 11:51
     */
    public static function calcRefundStatus($item)
    {
        // 整单退款
        if($item['refund_type'] == AfterSaleEnum::REFUND_TYPE_ORDER) {
            $order = Order::withoutGlobalScope()->findOrEmpty($item['order_id'])->toArray();
            return $item['refund_total_amount'] == $order['order_amount'] ? AfterSaleEnum::FULL_REFUND : AfterSaleEnum::PARTIAL_REFUND;
        }
        // 商品售后
        if($item['refund_type'] == AfterSaleEnum::REFUND_TYPE_GOODS) {
            $orderGoods = OrderGoods::withoutGlobalScope()->findOrEmpty($item['order_goods_id'])->toArray();
            return $item['refund_total_amount'] == $orderGoods['total_pay_price'] ? AfterSaleEnum::FULL_REFUND : AfterSaleEnum::PARTIAL_REFUND;
        }
    }

    /**
     * @notes 校验原路退款
     * @param $item
     * @return bool
     * @author Tab
     * @date 2021/8/18 14:31
     */
    public static function originalRefund($item)
    {
        $order = Order::withoutGlobalScope()->findOrEmpty($item['order_id'])->toArray();
        if (empty($order)) {
            return null;
        }
        switch($order['pay_way']) {
            case PayEnum::WECHAT_PAY:
                return self::checkWechatRefund($item);
            case PayEnum::ALI_PAY:
                return self::checkAliRefund($item);
            case PayEnum::BYTE_PAY:
                return self::checkByteRefund($item);
        }
    }

    /**
     * @notes 退款成功后操作
     * @param $item
     * @author Tab
     * @date 2021/8/18 14:20
     */
    public static function afterSuccess($item)
    {
        $refundStauts = self::calcRefundStatus($item);
        AfterSale::duokaiUpdate([
                'id' => $item['id'],
                'refund_status' => $refundStauts,
                'status' => AfterSaleEnum::STATUS_SUCCESS,
                'sub_status' => AfterSaleEnum::SUB_STATUS_SELLER_REFUND_SUCCESS
            ], [], [], '', false);
        self::createAfterLog($item['id'], '系统已完成退款', 0, AfterSaleLogEnum::ROLE_SYS, $item['sid']);
    }

    /**
     * @notes 退款失败后操作
     * @param $item
     * @author Tab
     * @date 2021/8/18 14:21
     */
    public static function afterFail($item)
    {
        AfterSale::duokaiUpdate([
                'id' => $item['id'],
                'sub_status' => AfterSaleEnum::SUB_STATUS_SELLER_REFUND_FAIL
            ], [], [], '', false);
        self::createAfterLog($item['id'], '系统退款失败,等待卖家处理', 0, AfterSaleLogEnum::ROLE_SYS, $item['sid']);
    }

    /**
     * @notes 根据不同客户端获取不同的微信配置
     * @param $terminal
     * @param $sid
     * @return array
     * @author Tab
     * @date 2021/12/23 14:36
     */
    public static function getWechatConfigByTerminal($terminal, $sid)
    {
        switch ($terminal) {
            case UserTerminalEnum::WECHAT_MMP:
                $appid = Config::withoutGlobalScope()->where(['type' => 'mini_program', 'name' => 'app_id', 'sid' => $sid])->value('value');
                $secret = Config::withoutGlobalScope()->where(['type' => 'mini_program', 'name' => 'app_secret', 'sid' => $sid])->value('value');
                $notify_url = (string)url('pay/notifyMnp', [], false, true);
                break;
            case UserTerminalEnum::WECHAT_OA:
            case UserTerminalEnum::PC:
            case UserTerminalEnum::H5:
            $appid = Config::withoutGlobalScope()->where(['type' => 'official_account', 'name' => 'app_id', 'sid' => $sid])->value('value');
            $secret = Config::withoutGlobalScope()->where(['type' => 'official_account', 'name' => 'app_secret', 'sid' => $sid])->value('value');
                $notify_url = (string)url('pay/notifyOa', [], false, true);
                break;
            case UserTerminalEnum::ANDROID:
            case UserTerminalEnum::IOS:
            $appid = Config::withoutGlobalScope()->where(['type' => 'open_platform', 'name' => 'app_id', 'sid' => $sid])->value('value');
            $secret = Config::withoutGlobalScope()->where(['type' => 'open_platform', 'name' => 'app_secret', 'sid' => $sid])->value('value');
                $notify_url = (string)url('pay/notifyApp', [], false, true);
                break;
            default:
                $appid = '';
                $secret = '';
        }

        $pay = PayConfig::withoutGlobalScope()->where(['pay_way' => PayEnum::WECHAT_PAY, 'sid' => $sid])->findOrEmpty()->toArray();
        //判断是否已经存在证书文件夹，不存在则新建
        if (!file_exists(app()->getRootPath().'runtime/certificate')) {
            mkdir(app()->getRootPath().'runtime/certificate', 0775, true);
        }
        //写入文件
        $apiclient_cert = $pay['config']['apiclient_cert'] ?? '';
        $apiclient_key = $pay['config']['apiclient_key'] ?? '';
        $cert_path = app()->getRootPath().'runtime/certificate/'.md5($apiclient_cert).'.pem';
        $key_path = app()->getRootPath().'runtime/certificate/'.md5($apiclient_key).'.pem';
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
     * @notes 生成售后日志
     * @param $afterSaleId
     * @param $content
     * @param null $operatorId
     * @param null $operatorRole
     * @author Tab
     * @date 2021/12/23 14:36
     */
    public static function createAfterLog($afterSaleId, $content, $operatorId = null,$operatorRole = null, $sid = 0)
    {
        $data = [
            'after_sale_id' => $afterSaleId,
            'content' => $content,
            'operator_id' => $operatorId,
            'operator_role' => $operatorRole,
            'sid' => $sid,
        ];

        AfterSaleLog::duokaiCreate($data, [], false, '', false);
    }
}