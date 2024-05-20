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

namespace app\adminapi\validate;

use app\common\validate\BaseValidate;

use app\common\model\Admin;
use think\facade\Config;

class MyValidate extends BaseValidate
{
    protected $rule = [
        'old_password' => 'require|checkOld',
        'new_password' => 'require|length:6,32',
        'new_password_confirm' => 'require|confirm:new_password',
    ];

    protected $message = [
        'old_password.require' => '原密码不能为空',
        'new_password.require' => '新密码不能为空',
        'new_password.length' => '新密码长度需在6-32个字符之间',
        'new_password_confirm.require' => '确认新密码不能为空',
        'new_password_confirm.confirm' => '两次输入的新密码不一致',
    ];

    /**
     * @notes 校验原密码
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/7/13 12:01
     */
    public function checkOld($value, $rule, $data)
    {
        $admin = Admin::findOrEmpty($data['id']);
        $passwordSalt = Config::get('project.unique_identification');
        $password = create_password($value, $passwordSalt);
        if ($admin->password != $password) {
            return '原密码错误';
        }
        return true;
    }
}