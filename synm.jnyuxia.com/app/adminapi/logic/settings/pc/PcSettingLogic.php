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
namespace app\adminapi\logic\settings\pc;

use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;

/**
 * PC商城渠道设置
 */
class PcSettingLogic extends BaseLogic
{
    /**
     * @notes 获取PC商城配置
     * @author Tab
     * @date 2021/11/29 18:04
     */
    public static function getConfig()
    {
        $shopName = ConfigService::get('shop', 'name');
        $config = [
            'status' => ConfigService::get('pc', 'status', YesNoEnum::YES),
            // 渠道关闭后访问页面类型 0-空白页 1-自定义页
            'redirect_type' => ConfigService::get('pc', 'redirect_type', YesNoEnum::NO),
            'redirect_content' => ConfigService::get('pc', 'redirect_content', ''),
            'visit_url' => request()->domain() . '/pc',
            'title' => ConfigService::get('pc', 'title', $shopName),
            'ico' => ConfigService::get('pc', 'ico'),
            'description' => ConfigService::get('pc', 'description', ''),
            'keywords' => ConfigService::get('pc', 'keywords', ''),
            'tools_code' => ConfigService::get('pc', 'tools_code', ''),
        ];
        $config['ico'] = empty($config['ico']) ? '' : FileService::getFileUrl($config['ico']);
        $config['redirect_content'] = html_entity_decode($config['redirect_content']);
        $config['tools_code'] = html_entity_decode($config['tools_code']);

        return $config;
    }

    /**
     * @notes PC商城设置
     * @author Tab
     * @date 2021/11/29 18:27
     */
    public static function setConfig($params)
    {
        $allowFileds = [
            'status',
            'redirect_type',
            'redirect_content',
            'title',
            'ico',
            'description',
            'keywords',
            'tools_code',
        ];

        try {
            foreach ($allowFileds as $field) {
                // 把字符转换为 HTML 实体
                if ($field == 'redirect_content' || $field == 'tools_code' && isset($params[$field])) {
                    $params[$field] = htmlentities($params[$field]);
                }
                // 网站图标
                if ($field == 'ico' && isset($params[$field]) ) {
                    $params[$field] = FileService::setFileUrl($params[$field]);
                }
                ConfigService::set('pc', $field, $params[$field] ?? '');
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }

    }
}