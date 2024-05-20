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


namespace app\platformapi\service;

use app\common\cache\PlatformAdminTokenCache;
use app\common\model\PlatformAdminSession;
use think\facade\Config;

class PlatformAdminTokenService
{
    /**
     * @notes 设置或更新管理员token
     * @param $adminId 管理员id
     * @param $terminal 多终端名称
     * @param $multipointLogin 是否支持多处登录
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public static function setToken($platformAdminId, $terminal, $multipointLogin = 1)
    {
        $time = time();
        $platformAdminSession = PlatformAdminSession::withoutGlobalScope()
            ->where([['platform_admin_id', '=', $platformAdminId], ['terminal', '=', $terminal]])->find();

        //获取token延长过期的时间
        $expireTime = $time + Config::get('platform.platform_admin_token.expire_duration');

        $platformAdminTokenCache = new PlatformAdminTokenCache();

        //token处理
        if ($platformAdminSession) {
            if ($platformAdminSession->expire_time < $time || $multipointLogin === 0) {
                //清空缓存
                $platformAdminTokenCache->deletePlatformAdminInfo($platformAdminSession->token);
                //如果token过期或账号设置不支持多处登录，更新token
                $platformAdminSession->token = create_token($platformAdminId);
            }
            $platformAdminSession->expire_time = $expireTime;
            $platformAdminSession->update_time = $time;

            $platformAdminSession->duokaiSave([], null, false, false);

        } else {
            //找不到在该终端的token记录，创建token记录
            $platformAdminSession = PlatformAdminSession::create([
                'platform_admin_id' => $platformAdminId,
                'terminal' => $terminal,
                'token' => create_token($platformAdminId),
                'expire_time' => $expireTime
            ]);
        }

        return $platformAdminTokenCache->setPlatformAdminInfo($platformAdminSession->token);
    }

    /**
     * @notes 延长token过期时间
     * @param $token
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public static function overtimeToken($token)
    {
        $time = time();
        $platformAdminSession = PlatformAdminSession::withoutGlobalScope()->where('token', '=', $token)->findOrEmpty();
        if ($platformAdminSession->isEmpty()) {
            return false;
        }
        //延长token过期时间
        $platformAdminSession->expire_time = $time + Config::get('platform.platform_admin_token.expire_duration');
        $platformAdminSession->update_time = $time;
        $platformAdminSession->withoutGlobalScope()->save();
        return (new PlatformAdminTokenCache())->setPlatformAdminInfo($platformAdminSession->token);
    }

    /**
     * @notes 设置token为过期
     * @param $token
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public static function expireToken($token)
    {
        $platformAdminSession = PlatformAdminSession::where('token', '=', $token)
            ->with('platformAdmin')
            ->find();
        if (empty($platformAdminSession)) {
            return false;
        }

        //当支持多处登录的时候，服务端不注销
        if ($platformAdminSession->platformAdmin->multipoint_login === 1) {
            return false;
        }

        $time = time();
        $platformAdminSession->expire_time = $time;
        $platformAdminSession->update_time = $time;
        $platformAdminSession->duokaiSave([], null, false, false);

        return (new  PlatformAdminTokenCache())->deletePlatformAdminInfo($token);

    }

}