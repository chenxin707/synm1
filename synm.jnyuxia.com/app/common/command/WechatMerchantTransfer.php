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


use app\adminapi\logic\withdraw\WechatMerchantTransferLogic;
use app\adminapi\logic\withdraw\WithdrawLogic;
use app\common\enum\AccountLogEnum;
use app\common\enum\UserTerminalEnum;
use app\common\enum\WithdrawEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\WithdrawApply;
use app\common\service\ConfigService;
use app\common\service\WeChatConfigService;
use think\console\Command;
use think\console\Output;
use think\console\Input;
use think\facade\Log;

class WechatMerchantTransfer extends Command
{
    protected function configure()
    {
        $this->setName('wechat_merchant_transfer')
            ->setDescription('商家转账到零钱查询');
    }

    protected function execute(Input $input, Output $output)
    {
        $transfer_way = ConfigService::get('config', 'transfer_way',1);
        //选择了商家转账到零钱再进行查询
        if ($transfer_way == WithdrawEnum::ENTERPRISE) {
            return false;
        }

        //默认使用小程序配置
        $config = WeChatConfigService::getWechatConfigByTerminal(UserTerminalEnum::WECHAT_MMP);

        $lists = WithdrawApply::where(['type'=>WithdrawEnum::TYPE_WECHAT_CHANGE,'status'=>WithdrawEnum::STATUS_ING])
            ->field('id,sn,batch_no,user_id,money')
            ->select();

        foreach ($lists as $list) {
            $result = WechatMerchantTransferLogic::details($list,$config);
            if(isset($result['detail_status'])) {
                if ($result['detail_status'] == 'SUCCESS') {
                    //提现成功，更新提现申请单
                    WithdrawApply::update([
                        'status' => WithdrawEnum::STATUS_SUCCESS,
                        'pay_search_result' => json_encode($result, JSON_UNESCAPED_UNICODE),
                        'payment_no'=>$result['detail_id'],
                        'payment_time' => strtotime($result['update_time']),
                    ],['id'=>$list['id']]);
                }
                if ($result['detail_status'] == 'FAIL') {
                    //提现成功，更新提现申请单
                    WithdrawApply::update([
                        'status' => WithdrawEnum::STATUS_FAIL,
                        'pay_search_result' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ],['id'=>$list['id']]);

                    // 回退提现金额
                    WithdrawLogic::fallbackMoney($list);
                    // 记录账户流水
                    AccountLogLogic::add($list['user_id'], AccountLogEnum::BW_INC_PAYMENT_FAIL, AccountLogEnum::INC, $list['money'], $list['sn'], '付款失败回退金额');
                }
                continue;
            }else {
                Log::write($result['message'] ?? '商家转账到零钱查询失败');
                return null;
            }
        }

        return true;
    }
}