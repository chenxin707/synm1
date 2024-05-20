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

namespace app\common\logic;

use app\common\enum\AccountLogEnum;
use app\common\model\AccountLog;
use app\common\model\User;

/**
 * 账户流水记录逻辑层
 * Class AccountLogLogic
 * @package app\common\logic
 */
class AccountLogLogic extends BaseLogic
{
    /**
     * @notes 添加账户流水记录
     * @param $userId 会员ID
     * @param $changeType 变动类型(需在AccountLogEnum预先定义好)
     * @param $action 变动动作(AccountLogEnum中有定义好常量 1-减少 2-增加)
     * @param $changeAmount 变动数量
     * @param $associationSn 关联单号(例如：订单号、充值单号、提现单号等)
     * @param string $remark 备注
     * @param array $feature 预留字段，方便存更多其它信息
     * @return false
     * @author Tab
     * @date 2021/8/4 9:58
     */
    public static function add($userId, $changeType, $action, $changeAmount, $associationSn = '', $remark = '', $feature = [], $sid = 0)
    {
        $user = User::withoutGlobalScope()->findOrEmpty($userId);
        if($user->isEmpty()) {
            return false;
        }

        $changeObject = AccountLogEnum::getChangeObject($changeType);
        if(!$changeObject) {
            return false;
        }

        switch ($changeObject) {
            // 可提现余额(用户收入)
            case AccountLogEnum::BW:
                $left_amount = $user->user_earnings;
                break;
            // 不可提现余额
            case AccountLogEnum::BNW:
                $left_amount = $user->user_money;
                break;
            // 成长值
            case AccountLogEnum::GROWTH:
                $left_amount = $user->user_growth;
                break;
            // 积分
            case AccountLogEnum::INTEGRAL:
                $left_amount = $user->user_integral;
                break;
        }

        $accountLog = new AccountLog();
        $data = [
            'sn' => generate_sn($accountLog, 'sn', 20),
            'user_id' => $userId,
            'change_object' => $changeObject,
            'change_type' => $changeType,
            'action' => $action,
            'left_amount' => $left_amount,
            'change_amount' => $changeAmount,
            'association_sn' => $associationSn,
            'remark' => $remark,
            'feature' => $feature ? json_encode($feature, JSON_UNESCAPED_UNICODE) : '',
        ];

        if ($sid === 0) {
            return $accountLog->duokaiSave($data);
        }
        // 主要用于定时任务
        $data['sid'] = $sid;
        $accountLog->duokaiWithoutGlobalScope()->save($data);
    }
}