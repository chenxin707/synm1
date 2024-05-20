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

namespace app\platformapi\logic;


use app\common\logic\BaseLogic;
use app\common\model\PlatformAdmin;
use app\platformapi\service\PlatformAdminTokenService;
use think\Exception;
use think\facade\Config;

class LoginLogic extends BaseLogic
{
    /**
     * @notes 管理员账号登录
     * @param $params
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public function login($params)
    {
        $time = time();
        $platformAdmin = PlatformAdmin::withoutGlobalScope()->where('account', '=', $params['account'])->find();

        //登录信息更新
        $platformAdmin->login_time = $time;
        $platformAdmin->login_ip = request()->ip();
        $platformAdmin->duokaiSave([], null, false, false);

        //设置token
        $platformAdminInfo = PlatformAdminTokenService::setToken($platformAdmin->id, $params['terminal'], $platformAdmin->multipoint_login);

        return [
            'name' => $platformAdminInfo['name'],
            'avatar' => $platformAdminInfo['avatar'],
            'token' => $platformAdminInfo['token'],
        ];

    }


    /**
     * @notes 退出登录
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public function logout($platformAdminInfo)
    {
        //token不存在，不注销
        if (!isset($platformAdminInfo['token'])) {
            return false;
        }

        //设置token过期
        return PlatformAdminTokenService::expireToken($platformAdminInfo['token']);

    }

    /**
     * @notes 重置密码
     * @param $params
     * @author cjhao
     * @date 2022/3/29 18:09
     */
    public function resetPassword($params)
    {
        try{

            $passwordSalt = Config::get('platform.unique_identification');
            $password = create_password($params['password'], $passwordSalt);
            PlatformAdmin::update(['password'=>$password],['root'=>1]);
            return true;

        }catch (Exception $e) {
            return $e->getMessage();
        }

    }
}