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


namespace app\kefuapi\validate;

use app\common\cache\KefuAccountSafeCache;
use app\common\model\Kefu;
use app\common\validate\BaseValidate;
use think\facade\Config;

/**
 * 客服登录验证
 * Class LoginValidate
 * @package app\kefuapi\validate
 */
class LoginValidate extends BaseValidate
{
    protected $rule = [
        'terminal' => 'require',
        'account' => 'require',
        'password' => 'require|checkPassword',
    ];

    protected $message = [
        'account.require' => '请输入账号',
        'password.require' => '请输入密码',
        'password.checkPassword' => '账号或密码错误',
        'terminal.require' => '客户端参数缺失'
    ];



    /**
     * @notes 密码验证
     * @param $password
     * @param $other
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/9 18:54
     */
    public function checkPassword($password, $other, $data)
    {
        //后台账号安全机制，连续输错后锁定，防止账号密码暴力破解
        $SafeCache = new KefuAccountSafeCache();
        if (!$SafeCache->isSafe()) {
            return '密码连续' . $SafeCache->count . '次输入错误，请' . $SafeCache->minute . '分钟后重试';
        }

        $kefu = Kefu::withoutGlobalScope()->aliasSid('k')->alias('k')->field([
                'a.password',
                'a.disable' => 'admin_disable',
                'k.disable' => 'kefu_disable'
            ])
            ->join('admin a', 'k.admin_id = a.id')
            ->where(['a.account' => $data['account']])
            ->findOrEmpty();

        if ($kefu->isEmpty()) {
            return '用户不存在';
        }

        if ($kefu['admin_disable'] || $kefu['kefu_disable']) {
            return '账号已禁用';
        }

        if (empty($kefu['password'])) {
            $SafeCache->record();
            return '客服不存在';
        }

        $passwordSalt = Config::get('project.unique_identification');
        if ($kefu['password'] !== create_password($password, $passwordSalt)) {
            $SafeCache->record();
            return '密码错误';
        }

        $SafeCache->relieve();

        return true;
    }


}