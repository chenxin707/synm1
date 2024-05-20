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

namespace app\adminapi\logic\toutiao;

use app\common\logic\BaseLogic;
use app\common\service\ConfigService;

/**
 * 字节小程序(头条)
 */
class ToutiaoSettingLogic extends BaseLogic
{
    /**
     * @notes 获取配置
     * @return array
     * @author Tab
     * @date 2021/11/11 15:35
     */
    public static function getConfig()
    {
        $domainName = $_SERVER['SERVER_NAME'];
        $config = [
            'appid' => ConfigService::get("toutiao", "appid", ''),
            'secret' => ConfigService::get("toutiao", "secret", ''),
            'pay_salt' => ConfigService::get("toutiao", "pay_salt", ''),
            'request_domain'        => $domainName,
            'socket_domain'         => $domainName,
            'upload_file_domain'     => $domainName,
            'download_file_domain'   => $domainName,
            'udp_domain'            => $domainName,
            'business_domain'       => $domainName,
            'notify'       => 'https://'.$domainName.'/shopapi/pay/toutiaoNotify',
        ];

        return $config;
    }

    /**
     * @notes 配置
     * @param $params
     * @author Tab
     * @date 2021/11/11 15:36
     */
    public static function setConfig($params)
    {
        try {
            if (isset($params['appid']) && isset($params['secret']) && isset($params['pay_salt'])) {
                ConfigService::set('toutiao','appid',$params['appid']);
                ConfigService::set('toutiao','secret',$params['secret']);
                ConfigService::set('toutiao','pay_salt',$params['pay_salt']);
                return true;
            }
            throw new \Exception("参数缺失");
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}