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


namespace app\kefuapi\service;

use app\common\cache\KefuTokenCache;
use app\common\model\KefuSession;
use think\facade\Config;

/**
 * 客服token
 * Class KefuTokenService
 * @package app\kefuapi\service
 */
class KefuTokenService
{

    /**
     * @notes 设置或更新token
     * @param $kefuId
     * @param $terminal
     * @return array|false|mixed
     * @author 段誉
     * @date 2022/3/9 18:56
     */
    public static function setToken($kefuId, $terminal)
    {
        $time = time();
        $kefuSession = KefuSession::where([['kefu_id', '=', $kefuId], ['terminal', '=', $terminal]])->findOrEmpty();

        //获取token延长过期的时间
        $tokenCache = new KefuTokenCache();
        $expireTime = $time + Config::get('project.kefu_token.expire_duration');
        $token = create_token($kefuId);

        //token处理
        if ($kefuSession->isEmpty()) {
            //找不到在该终端的token记录，创建token记录
            KefuSession::duokaiCreate([
                'kefu_id' => $kefuId,
                'terminal' => $terminal,
                'token' => $token,
                'expire_time' => $expireTime
            ]);
        } else {
            // 清空缓存
            $tokenCache->deleteKefuInfo($kefuSession->token);
            //  更新token
            KefuSession::duokaiUpdate([
                'id' => $kefuSession['id'],
                'token' => $token,
                'expire_time' => $expireTime,
                'update_time' => $time,
            ],[], [], '', false);
        }
        return $tokenCache->setKefuInfo($token);
    }


    /**
     * @notes 延长token过期时间
     * @param $token
     * @return array|false|mixed
     * @author 段誉
     * @date 2022/3/9 18:56
     */
    public static function overtimeToken($token)
    {
        $time = time();
        $kefuSession = KefuSession::where('token', '=', $token)->findOrEmpty();
        if ($kefuSession->isEmpty()) {
            return false;
        }
        //延长token过期时间
        $kefuSession->expire_time = $time + Config::get('project.kefu_token.expire_duration');
        $kefuSession->update_time = $time;
        $kefuSession->save();
        return (new KefuTokenCache())->setKefuInfo($token);
    }





    /**
     * @notes 设置token为过期
     * @param $token
     * @return bool
     * @author 段誉
     * @date 2022/3/9 18:57
     */
    public static function expireToken($token)
    {
        $kefuSession = KefuSession::where('token', '=', $token)->findOrEmpty();
        if ($kefuSession->isEmpty()) {
            return false;
        }

        $time = time();
        $kefuSession->expire_time = $time;
        $kefuSession->update_time = $time;
        $kefuSession->save();

        return (new  KefuTokenCache())->deleteKefuInfo($token);
    }

}