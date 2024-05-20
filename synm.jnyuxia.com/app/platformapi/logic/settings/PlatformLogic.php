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

namespace app\platformapi\logic\settings;


use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Config;

/**
 * 平台设置
 */
class PlatformLogic extends BaseLogic
{
    /**
     * @notes 获取平台设置
     * @author Tab
     * @date 2021/12/13 17:25
     */
    public static function getBaseConfig()
    {
        $defaultPlatformName = Config::get('platform.platform_name');
        $defaultPlatformIco = Config::get('platform.default_image.platform_ico');
        $defaultPlatformLoginImage = Config::get('platform.default_image.platform_login_image');
        $config = [
            'platform_name' => ConfigService::get('platformapi', 'platform_name', $defaultPlatformName),
            'platform_ico' => ConfigService::get('platformapi', 'platform_ico', $defaultPlatformIco),
            'platform_login_image' => ConfigService::get('platformapi', 'platform_login_image', $defaultPlatformLoginImage),
            'document_status' => ConfigService::get('platformapi','document_status',1),
        ];
        $config['platform_ico'] = FileService::getFileUrl($config['platform_ico']);
        $config['platform_login_image'] = FileService::getFileUrl($config['platform_login_image']);
        $config['platform_ico_example'] = Config::get('platform.default_image.paltform_ico_example');
        $config['platform_ico_example'] = FileService::getFileUrl($config['platform_ico_example']);
        $config['platform_login_image_example'] = Config::get('platform.default_image.paltform_login_image_example');
        $config['platform_login_image_example'] = FileService::getFileUrl($config['platform_login_image_example']);
        $config['platform_version'] = Config::get('project.version');
        return $config;
    }

    /**
     * @notes 平台基础信息设置
     * @param $params
     * @author Tab
     * @date 2021/12/14 10:42
     */
    public static function setBaseConfig($params)
    {
        try {
            ConfigService::set('platformapi', 'platform_name', $params['platform_name']);
            if (isset($params['platform_ico']) && !empty($params['platform_ico'])) {
                $params['platform_ico'] = FileService::setFileUrl($params['platform_ico']);
                ConfigService::set('platformapi', 'platform_ico', $params['platform_ico']);
            }
            if (isset($params['platform_login_image']) && !empty($params['platform_login_image'])) {
                $params['platform_login_image'] = FileService::setFileUrl($params['platform_login_image']);
                ConfigService::set('platformapi', 'platform_login_image', $params['platform_login_image']);
            }

            //文档信息开关
            ConfigService::set('platformapi','document_status', $params['document_status']);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 获取备案信息
     * @author Tab
     * @date 2021/12/14 11:09
     */
    public static function getRecordConfig()
    {
        $config = [
            'copyright' => ConfigService::get('platformapi', 'copyright', ''),
            'record_no' => ConfigService::get('platformapi', 'record_no', ''),
            'record_url' => ConfigService::get('platformapi', 'record_url', ''),
        ];

        return $config;
    }

    /** 设置备案信息
     * @notes
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/12/14 11:15
     */
    public static function setRecordConfig($params)
    {
        try {
            ConfigService::set('platformapi', 'copyright', $params['copyright'] ?? '');
            ConfigService::set('platformapi', 'record_no', $params['record_no'] ?? '');
            ConfigService::set('platformapi', 'record_url', $params['record_url'] ?? '');

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 获得所有配置
     * @return array
     * @author Tab
     * @date 2021/12/17 11:29
     */
    public static function getConfig()
    {
        $defaultPlatformName = Config::get('platform.platform_name');
        $defaultPlatformIco = Config::get('platform.default_image.platform_ico');
        $defaultPlatformLoginImage = Config::get('platform.default_image.platform_login_image');
        $config = [
            'platform_name' => ConfigService::get('platformapi', 'platform_name', $defaultPlatformName),
            'platform_ico' => ConfigService::get('platformapi', 'platform_ico', $defaultPlatformIco),
            'platform_login_image' => ConfigService::get('platformapi', 'platform_login_image', $defaultPlatformLoginImage),
            'copyright' => ConfigService::get('platformapi', 'copyright', ''),
            'record_no' => ConfigService::get('platformapi', 'record_no', ''),
            'record_url' => ConfigService::get('platformapi', 'record_url', ''),
            'document_status' => ConfigService::get('platformapi','document_status',1),
        ];
        $config['platform_ico'] = FileService::getFileUrl($config['platform_ico']);
        $config['platform_login_image'] = FileService::getFileUrl($config['platform_login_image']);
        $config['platform_ico_example'] = Config::get('platform.default_image.paltform_ico_example');
        $config['platform_ico_example'] = FileService::getFileUrl($config['platform_ico_example']);
        $config['platform_login_image_example'] = Config::get('platform.default_image.paltform_login_image_example');
        $config['platform_login_image_example'] = FileService::getFileUrl($config['platform_login_image_example']);
        $config['platform_version'] = Config::get('project.version');
        return $config;
    }
}