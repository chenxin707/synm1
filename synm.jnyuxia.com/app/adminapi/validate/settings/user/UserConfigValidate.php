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
namespace app\adminapi\validate\settings\user;
use app\common\model\UserLevel;
use app\common\validate\BaseValidate;

/**
 * 用户设置验证
 * Class UserConfigValidate
 * @package app\adminapi\validate\settings\user
 */
class UserConfigValidate extends BaseValidate
{
    protected $regex = ['money'=>'/^[0-9]+(.[0-9]{1,2})?$/'];

    protected $rule = [
        'register_way'              => 'requireIf:scene,register|array',
        'login_way'                 => 'requireIf:scene,register|array',
        'is_mobile_register_code'   => 'requireIf:scene,register|in:0,1',
        'coerce_mobile'             => 'requireIf:scene,register|in:0,1',
        'h5_wechat_auth'            => 'in:0,1',
        'h5_auto_wechat_auth'       => 'in:0,1',
        'mnp_wechat_auth'           => 'in:0,1',
        'mnp_auto_wechat_auth'      => 'in:0,1',
        'app_wechat_auth'           => 'in:0,1',
        'withdraw_way'              => 'requireIf:scene,withdraw|array',
        'withdraw_min_money'        => 'gt:0|regex:money|lt:withdraw_max_money',
        'withdraw_max_money'        => 'gt:0|regex:money',
        'withdraw_service_charge'   => 'egt:0|regex:money|lt:100',
        'default_avatar'            => 'require',
        'invite_open'               => 'require|in:0,1',
        'invite_ways'               => 'require|in:1,2',
        'invite_appoint_user'       => 'array|checkAppointUser',
        'invite_condition'          => 'require|in:1',
    ];
    protected $message = [
        'default_avatar.require'            => '请上传用户默认头像',
        'register_way.requireIf'            => '请选择注册方式',
        'register_way.array'                => '注册方式值错误',
        'login_way.requireIf'               => '请选择登录方式',
        'login_way.array'                   => '登录方式值错误',
        'is_mobile_register_code.requireIf' => '请选择手机号码注册需验证码',
        'is_mobile_register_code.in'        => '手机号码注册需验证码错误',
        'coerce_mobile.requireIf'           => '请选择注册强制绑定手机',
        'coerce_mobile.in'                  => '注册强制绑定手机值错误',
        'h5_wechat_auth.in'                 => '公众号微信授权登录值错误',
        'h5_auto_wechat_auth.in'            => '公众号微信授权登录值错误',
        'mnp_wechat_auth.in'                => '小程序授权登录值错误',
        'app_wechat_auth.in'                => '小程序微信授权登录值错误',
        'withdraw_way.requireIf'            => '请选择提现方式',
        'withdraw_way.in'                   => '提现方式值错误',
        'withdraw_min_money.gt'             => '最低提现金额不能小于零',
        'withdraw_min_money.regex'          => '最低提现金额只能是大于零的数字,且保留两位小数',
        'withdraw_min_money.lt'             => '最低提现金额不能大于最高提现金额',
//        'withdraw_min_money.require'        => '请输入最低提现金额',
        'withdraw_max_money.gt'             => '最高提现金额不能小于零',
        'withdraw_max_money.regex'          => '最高提现金额只能是大于零的数字,且保留两位小数',
//        'withdraw_max_money.require'        => '请输入最高提现金额',
        'withdraw_service_charge.egt'       => '提现手续费不能小于零',
        'withdraw_service_charge.regex'     => '提现手续费只能是大于零的数字,且保留两位小数',
        'withdraw_service_charge.lt'        => '提现手续费占比不能大于100%',

//        'withdraw_service_charge.require'   => '提现手续费不能小于零',
        'invite_open.require'             => '请选择邀请功能',
        'invite_open.in'                    => '邀请功能状态值错误',
        'invite_ways.require'             => '请选择邀请资格',
        'invite_ways.array'                 => '邀请资格数据格式错误',
        'invite_appoint_user.requireIf'     => '请选择指定用户',
        'invite_appoint_user.array'         => '指定用户值错误',
        'invite_condition.require'        => '请选择下级条件',
        'invite_condition.in'               => '下级条件状态码错误',
    ];

    //用户设置验证
    public function sceneUser()
    {
        return $this->only(['default_avatar','invite_open','invite_ways','invite_appoint_user','invite_condition']);
    }
    //注册验证
    public function sceneRegister()
    {
        return $this->only(['register_way','login_way','is_mobile_register_code','coerce_mobile','h5_wechat_auth','h5_auto_wechat_auth','mnp_wechat_auth','mnp_auto_wechat_auth','app_wechat_auth']);
    }
    //提现验证
    public function sceneWithdraw()
    {
        return $this->only(['withdraw_way','withdraw_min_money','withdraw_max_money','withdraw_service_charge']);
    }

    //验证会员等级
    public function checkAppointUser($value,$rule,$data){
        if(0 == $data['invite_open']){
            return true;
        }
        if(1 == $data['invite_ways']){
            return true;
        }
        if(empty($value)){
            return '请选择指定用户';
        }
        $userLevel = UserLevel::column('id');
        foreach ($value as $id){
            if(!in_array($id,$userLevel)){
                return '用户等级错误，请刷新页面';
            }
        }
        return true;
    }
}