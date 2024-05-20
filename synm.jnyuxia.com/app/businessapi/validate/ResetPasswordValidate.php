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
namespace app\businessapi\validate;
use app\common\model\Admin;
use app\common\validate\BaseValidate;
use think\facade\Config;

/**
 * 管理员修改密码验证器
 * Class ResetPasswordValidate
 * @package app\businessapi\validate
 */
class ResetPasswordValidate extends BaseValidate
{
    protected $rule = [
        'password'              => 'require|min:6|confirm',
        'origin_password'       => 'require|checkPassword',
    ];

    protected $message = [
        'origin_password.require'       => '请输入当前密码',
        'password.require'              => '请输入新密码',
        'password.min'                  => '新密码至少六位数',
        'password.confirm'              => '两次密码输入不一致',
        'password_confirm.require'      => '请输入确认密码',
    ];


    public function checkPassword($value,$rule,$data){
        if($value == $data['password']){
            return '新密码和当前密码一样，请重新输入密码';
        }
        $passwordSalt = Config::get('project.unique_identification');
        $adminInfo = Admin::where('id', '=', $data['admin_id'])
            ->field(['password,disable'])
            ->find();

        if ($adminInfo['password'] !== create_password($value, $passwordSalt)) {
            return '密码错误';
        }
        return true;
    }

}