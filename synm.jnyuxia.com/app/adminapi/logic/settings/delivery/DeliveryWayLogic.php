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

class DeliveryWayLogic extends BaseLogic
{
    /**
     * @notes 查看配送方式配置
     * @return array|int|mixed|string|null
     * @author ljj
     * @date 2021/8/2 4:55 下午
     */
    public function getConfig()
    {
        return [
            'express' => [
                'is_express' => ConfigService::get('delivery_type', 'is_express', 1),
                'express_name' => ConfigService::get('delivery_type', 'express_name', '快递发货'),
            ],
            'selffetch' => [
                'is_selffetch' => ConfigService::get('delivery_type', 'is_selffetch', 0),
                'selffetch_name' => ConfigService::get('delivery_type', 'selffetch_name', '上门自提'),
            ],
        ];
    }

    /**
     * @notes 设置配送方式配置
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/2 5:09 下午
     */
    public function setConfig($params)
    {
        ConfigService::set('delivery_type', 'is_express', $params['is_express']);
        ConfigService::set('delivery_type', 'express_name', $params['express_name']);
        ConfigService::set('delivery_type', 'is_selffetch', $params['is_selffetch']);
        ConfigService::set('delivery_type', 'selffetch_name', $params['selffetch_name']);
        return true;
    }
}