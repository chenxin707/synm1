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

namespace app\kefuapi\logic;

use app\kefuapi\service\KefuTokenService;
use app\common\model\Kefu;
use app\common\logic\BaseLogic;
use app\common\service\FileService;

/**
 * 客服登录逻辑
 * Class LoginLogic
 * @package app\kefuapi\logic
 */
class LoginLogic extends BaseLogic
{
    /**
     * @notes 账号密码登录
     * @param $params
     * @return false
     * @author 段誉
     * @date 2022/3/9 18:55
     */
    public function login($params)
    {
        try {
            $kefu = Kefu::withoutGlobalScope()->aliasSid('k')->alias('k')
                ->field([
                    'k.id', 'k.nickname', 'k.avatar', 'a.account'
                ])
                ->join('admin a', 'k.admin_id = a.id')
                ->where(['a.account' => $params['account']])
                ->findOrEmpty()->toArray();

            if (empty($kefu)) {
                throw new \Exception('客服不存在');
            }

            //返回登录信息
            $kefu['avatar'] = !empty($kefu['avatar']) ? FileService::getFileUrl($kefu['avatar']) : "";
            //设置token
            $kefu['token'] = KefuTokenService::setToken($kefu['id'], $params['terminal'])['token'] ?? '';

            return $kefu;

        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 退出登录
     * @param $info
     * @return bool
     * @author 段誉
     * @date 2022/3/9 18:56
     */
    public function logout($info)
    {
        //token不存在，不注销
        if (!isset($info['token'])) {
            return false;
        }
        //设置token过期
        return KefuTokenService::expireToken($info['token']);
    }


}