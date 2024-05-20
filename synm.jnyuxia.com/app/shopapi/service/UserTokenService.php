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


namespace app\shopapi\service;


use app\common\cache\UserTokenCache;
use app\common\model\UserSession;
use think\facade\Config;

class UserTokenService
{
    /**
     * @notes 设置或更新用户token
     * @param $userId
     * @param $terminal
     * @param int $multipointLogin
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/13 23:07
     */
    public static function setToken($userId, $terminal)
    {
        $time = time();
        $userSession = UserSession::where([['user_id', '=', $userId], ['terminal', '=', $terminal]])->find();

        //获取token延长过期的时间
        $expireTime = $time + Config::get('project.user_token.expire_duration');
        $userTokenCache = new UserTokenCache();

        //token处理
        if ($userSession) {

            //清空缓存
            $userTokenCache->deleteUserInfo($userSession->token);
            //重新获取token
            $userSession->token = create_token($userId);
            $userSession->expire_time = $expireTime;
            $userSession->update_time = $time;
            $userSession->duokaiSave();
        } else {
            //找不到在该终端的token记录，创建token记录
            $userSession = UserSession::duokaiCreate([
                'user_id' => $userId,
                'terminal' => $terminal,
                'token' => create_token($userId),
                'expire_time' => $expireTime
            ]);

        }
        return $userTokenCache->setUserInfo($userSession->token);
    }

    /**
     * @notes 延长token过期时间
     * @param $token
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/5 14:25
     */
    public static function overtimeToken($token)
    {
        $time = time();
        $adminSession = UserSession::where('token', '=', $token)->find();
        //延长token过期时间
        $adminSession->expire_time = $time + Config::get('project.user_token.expire_duration');
        $adminSession->update_time = $time;
        $adminSession->duokaiSave();
        return (new UserTokenCache())->setUserInfo($adminSession->token);
    }

    /**
     * @notes 设置token为过期
     * @param $token
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/5 14:31
     */
    public static function expireToken($token)
    {
        $userSession = UserSession::where('token', '=', $token)
            ->find();
        if (empty($userSession)) {
            return false;
        }

        $time = time();
        $userSession->expire_time = $time;
        $userSession->update_time = $time;
        $userSession->duokaiSave();

        return (new  UserTokenCache())->deleteUserInfo($token);

    }

}