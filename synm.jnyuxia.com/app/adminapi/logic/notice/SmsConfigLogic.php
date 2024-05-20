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

namespace app\adminapi\logic\notice;

use app\common\enum\SmsEnum;
use app\common\logic\BaseLogic;
use app\common\service\ConfigService;

/**
 * 短信配置逻辑层
 * Class SmsConfigLogic
 * @package app\adminapi\logic\notice
 */
class SmsConfigLogic extends BaseLogic
{
    /**
     * @notes 获取短信配置
     * @return array
     * @author Tab
     * @date 2021/8/19 11:54
     */
    public static function getConfig()
    {
        $config = [
            'ali' => ConfigService::get('sms', 'ali'),
            'tencent' => ConfigService::get('sms', 'tencent'),
        ];
        return $config;
    }

    /**
     * @notes 短信配置
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/19 14:31
     */
    public static function setConfig($params)
    {
        $type = $params['type'];
        unset($params['type']);
        $params['name'] = self::getNameDesc(strtoupper($type));
        ConfigService::set('sms', $type, $params);
        $default = ConfigService::get('sms', 'engine', false);
        if($params['status'] == 1 && $default === false) {
            // 启用当前短信配置 并 设置当前短信配置为默认
            ConfigService::set('sms', 'engine', strtoupper($type));
            return true;
        }
        if($params['status'] == 1 && $default != strtoupper($type)) {
            // 找到默认短信配置
            $defaultConfig = ConfigService::get('sms', strtolower($default));
            // 状态置为禁用 并 更新
            $defaultConfig['status'] = 0;
            ConfigService::set('sms', strtolower($default), $defaultConfig);
            // 设置当前短信配置为默认
            ConfigService::set('sms', 'engine', strtoupper($type));
            return true;
        }
    }

    /**
     * @notes 查看短信配置详情
     * @param $params
     * @return array|int|mixed|string
     * @author Tab
     * @date 2021/8/19 13:59
     */
    public static function detail($params)
    {
        return ConfigService::get('sms', $params['type']);
    }

    /**
     * @notes 获取短信平台名称
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/19 14:19
     */
    public  static function getNameDesc($value)
    {
        $desc = [
            'ALI' => '阿里云短信',
            'TENCENT' => '腾讯云短信',
        ];
        return $desc[$value] ?? '';
    }
}