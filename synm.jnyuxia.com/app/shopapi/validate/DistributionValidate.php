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

use app\adminapi\logic\config\UserLogic;
use app\adminapi\logic\distribution\DistributionConfigLogic;
use app\common\enum\DistributionApplyEnum;
use app\common\enum\UserEnum;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;
use app\common\model\Distribution;
use app\common\model\DistributionApply;

/**
 * 分销验证器
 * Class DistributionValidate
 * @package app\shopapi\validate
 */
class DistributionValidate extends BaseValidate
{
    protected $rule = [
        'code'      => 'require|checkCode',
        'user_id'   => 'checkApply',
        'real_name' => 'require',
        'mobile' => 'require|mobile',
        'province'  => 'require|integer',
        'city'      => 'require|integer',
        'district'  => 'require|integer',
        'reason'    => 'require',
    ];

    protected $message = [
        'code.require'      => '请输入邀请码',
        'real_name.require' => '请输入真实姓名',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '手机号码格式错误',
        'province.require'  => '请选择省份',
        'province.integer'  => '省份格式错误',
        'city.require'      => '请选择市',
        'city.integer'      => '市格式错误',
        'district.require'  => '请选择区',
        'district.integer'  => '区格式错误',
        'reason.require'    => '请输入申请原因',
    ];

    /**
     * @notes  填写验证码场景
     * @return DistributionValidate
     * @author Tab
     * @date 2021/7/17 10:19
     */
    public function sceneCode()
    {
        return $this->only(['code']);
    }

    /**
     * @notes 申请分销场景
     * @return DistributionValidate
     * @author Tab
     * @date 2021/7/17 10:18
     */
    public function sceneApply()
    {
        return $this->remove('code', 'require|checkCode')
            ->remove('terminal', 'require');
    }

    /**
     * @notes 校验邀请码
     * @param $code
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/7/16 17:50
     */
    public function checkCode($code, $rule, $data)
    {
        // 获取分销配置
        $distributionConfig = DistributionConfigLogic::getConfig();
        if(!$distributionConfig['switch']) {
            return '分销功能已关闭，无法绑定上下级';
        }
        $firstLeader = User::withoutGlobalScope()
            ->aliasSid('u')
            ->alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->field('u.id,u.ancestor_relation,d.is_distribution,u.level')
            ->where('u.code', $code)
            ->findOrEmpty();

        if($firstLeader->isEmpty()) {
            return '无效的邀请码';
        }

        $user = User::findOrEmpty($data['user_id']);
        if (empty($user->inviter_id) && $firstLeader->id != $data['user_id']) {
            // 记录邀请人(记录后不会再变更)
            $user->inviter_id = $firstLeader->id;
            $user->duokaiSave();
        }

        $inviteOpen = ConfigService::get('config', 'invite_open', config('project.default_user.invite_open'));
        if(!$inviteOpen) {
            return '邀请下级功能已关闭';
        }
        $inviteCondition= ConfigService::get('config', 'invite_condition', config('project.default_user.invite_condition'));
        if($inviteCondition != UserEnum::INVITE_CONDITION_CODE) {
            return '不支持邀请码方式建立上下级关系';
        }

        if($user->first_leader) {
            return '已有上级';
        }

        if($firstLeader->id == $data['user_id']) {
            return '上级不能是自己';
        }

        $inviteWays = ConfigService::get('config', 'invite_ways', config('project.default_user.invite_ways'));
        $inviteAppointUser = ConfigService::get('config', 'invite_appoint_user', config('project.default_user.invite_appoint_user'));
        // 邀请类型指定会员，邀请人没达到指定的等级
        if($inviteWays == 2 && !in_array($firstLeader['level'], $inviteAppointUser) && !$firstLeader['is_distribution']) {
            return '邀请下级资格未达到要求';
        }

        $ancestorRelation =explode(',', $firstLeader['ancestor_relation']);
        if(!empty($ancestorRelation) && in_array($data['user_id'], $ancestorRelation)) {
            return '不允许填写自己任一下级的邀请码';
        }
        return true;
    }

    /**
     * @notes 校验是否重复提交申请
     * @param $user_id
     * @return string
     * @author Tab
     * @date 2021/7/17 10:51
     */
    public function checkApply($user_id)
    {
        $is_distribution = Distribution::where('user_id', $user_id)->value('is_distribution');
        if($is_distribution) {
            return '您已是分销会员，无需申请';
        }
        $distributionApply = DistributionApply::where('user_id', $user_id)->findOrEmpty();
        if(!$distributionApply->isEmpty() && $distributionApply->status == DistributionApplyEnum::AUDIT_WAIT) {
            return '审核中,请勿重复提交申请';
        }

        return true;
    }
}