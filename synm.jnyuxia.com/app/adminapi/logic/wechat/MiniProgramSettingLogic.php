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

namespace app\adminapi\logic\wechat;

use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;

/**
 * 微信小程序设置逻辑层
 * Class MiniProgramSettingLogic
 * @package app\adminapi\logic\wechat
 */
class MiniProgramSettingLogic extends BaseLogic
{
    public static function getConfig()
    {
        $domainName = $_SERVER['HTTP_HOST'];
        $qrCode = ConfigService::get('mini_program', 'qr_code', '');
        $qrCode = empty($qrCode) ? $qrCode : FileService::getFileUrl($qrCode);
        $config = [
            'name'                  => ConfigService::get('mini_program', 'name', ''),
            'original_id'           => ConfigService::get('mini_program', 'original_id', ''),
            'qr_code'               => $qrCode,
            'app_id'                => ConfigService::get('mini_program', 'app_id', ''),
            'app_secret'            => ConfigService::get('mini_program', 'app_secret', ''),
            'request_domain'        => 'https://'.$domainName,
            'socket_domain'         => 'wss://'.$domainName,
            'upload_file_domain'     => 'https://'.$domainName,
            'download_file_domain'   => 'https://'.$domainName,
            'udp_domain'            => 'udp://'.$domainName,
            'tcp_domain'            => 'tcp://'.$domainName,
            'business_domain'       => $domainName,
        ];

        return $config;
    }

    /**
     * @notes 微信小程序设置
     * @param $params
     * @author Tab
     * @date 2021/7/28 19:01
     */
    public static function setConfig($params)
    {
        $qrCode = isset($params['qr_code']) ? FileService::setFileUrl($params['qr_code']) : '';

        ConfigService::set('mini_program','name', $params['name'] ?? '');
        ConfigService::set('mini_program','original_id',$params['original_id'] ?? '');
        ConfigService::set('mini_program','qr_code',$qrCode);
        ConfigService::set('mini_program','app_id',$params['app_id']);
        ConfigService::set('mini_program','app_secret',$params['app_secret']);
    }
}