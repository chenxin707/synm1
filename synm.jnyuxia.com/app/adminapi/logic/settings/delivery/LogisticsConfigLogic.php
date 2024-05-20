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

namespace app\adminapi\logic\settings\delivery;


use app\common\logic\BaseLogic;
use app\common\service\ConfigService;

class LogisticsConfigLogic extends BaseLogic
{
    /**
     * @notes 查看物流配置
     * @return array
     * @author ljj
     * @date 2021/7/29 2:37 下午
     */
    public function getLogisticsConfig()
    {
        $default = [
            'express_bird' => serialize([
                'set_meal' => 'free',
                'app_key' => '',
                'ebussiness_id' => '',
            ]),
            'express_hundred' => serialize([
                'interface_type' => 'enterprise',
                'app_key' => '',
                'customer' => ''
            ]),
            'express_type' => 'express_bird'
        ];
        $data['express_bird'] = unserialize(ConfigService::get('logistics_config', 'express_bird', $default['express_bird']));
        $data['express_hundred'] = unserialize(ConfigService::get('logistics_config', 'express_hundred', $default['express_hundred']));
        $data['express_type'] = ConfigService::get('logistics_config', 'express_type', $default['express_type']);
        return $data;
    }

    /**
     * @notes 设置物流配置
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/29 3:59 下午
     */
    public function setLogisticsConfig($params)
    {
        ConfigService::set('logistics_config', 'express_bird', serialize($params['express_bird']));
        ConfigService::set('logistics_config', 'express_hundred', serialize($params['express_hundred']));
        ConfigService::set('logistics_config', 'express_type', $params['express_type']);

        return true;
    }
}