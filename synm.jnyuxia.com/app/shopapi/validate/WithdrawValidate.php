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

namespace app\shopapi\validate;

use app\common\enum\WithdrawEnum;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

/**
 * 提现验证器
 * Class WithdrawValidate
 * @package app\shopapi\validate
 */
class WithdrawValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'type' => 'require|in:1,2,3,4,5',
        'money' => 'require|gt:0|checkMoney',
        'account' => 'requireIf:type,3|requireIf:type,4|requireIf:type,5',
        'real_name' => 'requireIf:type,3|requireIf:type,4|requireIf:type,5|chs',
        'money_qr_code' => 'requireIf:type,4|requireIf:type,5',
        'bank' => 'requireIf:type,3',
        'subbank' => 'requireIf:type,3',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'type.require' => '请选择提现类型',
        'type.in' => '提现类型状态值错误',
        'money.require' => '请输入提现金额',
        'money.gt' => '提现金额须大于0',
        'account.requireIf' => '请输入提现账号',
        'real_name.requireIf' => '请输入真实姓名',
        'real_name.chs' => '真实姓名须为中文',
        'money_qr_code.requireIf' => '请上传收款码',
        'bank.requireIf' => '请输入提现银行',
        'subbank.requireIf' => '请输入银行支行',
    ];

    /**
     * @notes 提现申请场景
     * @return WithdrawValidate
     * @author Tab
     * @date 2021/8/6 17:15
     */
    public function sceneApply()
    {
        return $this->only(['type', 'money', 'account', 'real_name', 'money_qr_code', 'bank', 'subbank']);
    }

    /**
     * @notes 提现申请详情场景
     * @return WithdrawValidate
     * @author Tab
     * @date 2021/8/6 19:02
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 校验提现金额
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/6 17:22
     */
    protected function checkMoney($value, $rule, $data)
    {
        // 可提现金额是否充足
        $user_earnings = User::where('id', $data['user_id'])->value('user_earnings');
        $user_earnings = is_null($user_earnings) ? 0 : $user_earnings;
        if ($value > $user_earnings){
            return '可提现金额不足';
        }

        // 最低提现金额
        $min_withdraw = ConfigService::get('config', 'withdraw_min_money', WithdrawEnum::DEFAULT_MIN_MONEY);
        if($value < $min_withdraw){
            return '最低提现'.$min_withdraw.'元';
        }

        // 最高提现金额
        $max_withdraw = ConfigService::get('config', 'withdraw_max_money', WithdrawEnum::DEFAULT_MAX_MONEY);
        if ($value > $max_withdraw){
            return '最高提现'.$max_withdraw.'元';
        }

        return true;
    }
}