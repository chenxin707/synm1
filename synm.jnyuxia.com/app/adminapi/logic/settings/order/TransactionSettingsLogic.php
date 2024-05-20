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
namespace app\adminapi\logic\settings\order;

use app\common\logic\BaseLogic;
use app\common\service\ConfigService;

/**
 * 交易设置逻辑层
 * Class TransactionSettingsLogic
 * @package app\adminapi\logic\settings\order
 */
class TransactionSettingsLogic extends BaseLogic
{
    /**
     * @notes 获取交易设置
     * @return array
     * @author Tab
     * @date 2021/7/27 11:08
     */
    public static function getConfig()
    {
        $config = [
            'cancel_unpaid_orders' => ConfigService::get('transaction', 'cancel_unpaid_orders'),
            'cancel_unpaid_orders_times' => ConfigService::get('transaction', 'cancel_unpaid_orders_times'),
            'cancel_unshipped_orders' => ConfigService::get('transaction', 'cancel_unshipped_orders'),
            'cancel_unshipped_orders_times' => ConfigService::get('transaction', 'cancel_unshipped_orders_times'),
            'automatically_confirm_receipt' => ConfigService::get('transaction', 'automatically_confirm_receipt'),
            'automatically_confirm_receipt_days' => ConfigService::get('transaction', 'automatically_confirm_receipt_days'),
            'after_sales' => ConfigService::get('transaction', 'after_sales'),
            'after_sales_days' => ConfigService::get('transaction', 'after_sales_days'),
            'inventory_occupancy' => ConfigService::get('transaction', 'inventory_occupancy'),
            'return_inventory' => ConfigService::get('transaction', 'return_inventory'),
            'return_coupon' => ConfigService::get('transaction', 'return_coupon'),
        ];

        return $config;
    }

    /**
     * @notes 交易设置
     * @param $params
     * @author Tab
     * @date 2021/7/27 11:36
     */
    public static function setConfig($params)
    {
        ConfigService::set('transaction', 'cancel_unpaid_orders', $params['cancel_unpaid_orders']);
        ConfigService::set('transaction', 'cancel_unshipped_orders', $params['cancel_unshipped_orders']);
        ConfigService::set('transaction', 'automatically_confirm_receipt', $params['automatically_confirm_receipt']);
        ConfigService::set('transaction', 'after_sales', $params['after_sales']);
        ConfigService::set('transaction', 'inventory_occupancy', $params['inventory_occupancy']);
        ConfigService::set('transaction', 'return_inventory', $params['return_inventory']);
        ConfigService::set('transaction', 'return_coupon', $params['return_coupon']);

        if(isset($params['cancel_unpaid_orders_times'])) {
            ConfigService::set('transaction', 'cancel_unpaid_orders_times', $params['cancel_unpaid_orders_times']);
        }

        if(isset($params['cancel_unshipped_orders_times'])) {
            ConfigService::set('transaction', 'cancel_unshipped_orders_times', $params['cancel_unshipped_orders_times']);
        }

        if(isset($params['automatically_confirm_receipt_days'])) {
            ConfigService::set('transaction', 'automatically_confirm_receipt_days', $params['automatically_confirm_receipt_days']);
        }

        if(isset($params['after_sales_days'])) {
            ConfigService::set('transaction', 'after_sales_days', $params['after_sales_days']);
        }
    }
}