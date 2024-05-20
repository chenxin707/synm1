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

namespace app\platformapi\logic\wechat;

use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;

/**
 * 微信公众号设置
 */
class OfficialAccountSettingLogic extends BaseLogic
{
    /**
     * @notes 获取微信公众号设置
     */
    public static function getConfig()
    {
        $domainName = $_SERVER['HTTP_HOST'];
        $config = [
            'app_id'            => ConfigService::get('official_account', 'app_id', ''),
            'app_secret'        => ConfigService::get('official_account', 'app_secret', ''),
            // url()方法返回Url实例，通过与空字符串连接触发该实例的__toString()方法以得到路由地址
            'url'               => url('platformapi/wechat.official_account_reply/index', [],'',true).'',
            'token'             => ConfigService::get('official_account', 'token', ''),
            'encoding_aes_key'  => ConfigService::get('official_account', 'encoding_aes_key', ''),
            'encryption_type'   => ConfigService::get('official_account', 'encryption_type'),
            'business_domain'   => $domainName,
            'js_secure_domain'  => $domainName,
            'web_auth_domain'   => $domainName,
            'name'   => ConfigService::get('official_account', 'name'),
            'original_id'   => ConfigService::get('official_account', 'original_id'),
            'qr_code'   => ConfigService::get('official_account', 'qr_code'),
        ];
        if (!empty($config['qr_code'])) {
            $config['qr_code'] = FileService::getFileUrl($config['qr_code']);
        }
        return $config;
    }

    /**
     * @notes 微信公众号设置
     */
    public static function setConfig($params)
    {
        ConfigService::set('official_account','app_id',$params['app_id']);
        ConfigService::set('official_account','app_secret',$params['app_secret']);
        ConfigService::set('official_account','token',$params['token'] ?? '');
        ConfigService::set('official_account','encoding_aes_key',$params['encoding_aes_key'] ?? '');
        ConfigService::set('official_account','encryption_type',$params['encryption_type']);
        ConfigService::set('official_account','name',$params['name'] ?? '');
        ConfigService::set('official_account','original_id',$params['original_id'] ?? '');
        ConfigService::set('official_account','qr_code',(isset($params['qr_code']) && !empty($params['qr_code'])) ? FileService::setFileUrl($params['qr_code']) : '');
    }
}
