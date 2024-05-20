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
namespace app\adminapi\validate\settings\order;

use app\common\validate\BaseValidate;

/**
 * 交易设置验证器
 * Class TransactionSettingsValidate
 * @package app\adminapi\validate\settings\order
 */
class TransactionSettingsValidate extends BaseValidate
{
    protected $rule = [
        'cancel_unpaid_orders' => 'require|in:0,1',
        'cancel_unpaid_orders_times' => 'requireIf:cancel_unpaid_orders,1|integer|gt:0',
        'cancel_unshipped_orders' => 'require|in:0,1',
        'cancel_unshipped_orders_times' => 'requireIf:cancel_unshipped_orders,1|integer|gt:0',
        'automatically_confirm_receipt' => 'require|in:0,1',
        'automatically_confirm_receipt_days' => 'requireIf:automatically_confirm_receipt,1|integer|gt:0',
        'after_sales' => 'require|in:0,1',
        'after_sales_days' => 'requireIf:after_sales,1|integer|gt:0',
        'inventory_occupancy' => 'require|in:1',
        'return_inventory' => 'require|in:0,1',
        'return_coupon' => 'require|in:0,1',
    ];

    protected $message = [
        'cancel_unpaid_orders.require' => '请选择系统取消待付款订单方式',
        'cancel_unpaid_orders.in' => '系统取消待付款订单状态值有误',
        'cancel_unpaid_orders_times.requireIf' => '系统取消待付款订单时间未填写',
        'cancel_unpaid_orders_times.integer' => '系统取消待付款订单时间须为整型',
        'cancel_unpaid_orders_times.gt' => '系统取消待付款订单时间须大于0',

        'cancel_unshipped_orders.require' => '请选择买家取消待发货订单方式',
        'cancel_unshipped_orders.in' => '买家取消待发货订单状态值有误',
        'cancel_unshipped_orders_times.requireIf' => '买家取消待发货订单时间未填写',
        'cancel_unshipped_orders_times.integer' => '买家取消待发货订单时间须为整型',
        'cancel_unshipped_orders_times.gt' => '买家取消待发货订单时间须大于0',

        'automatically_confirm_receipt.require' => '请选择系统确认收货方式',
        'automatically_confirm_receipt.in' => '系统确认收货状态值有误',
        'automatically_confirm_receipt_days.requireIf' => '系统确认收货时间未填写',
        'automatically_confirm_receipt_days.integer' => '系统确认收货时间须为整型',
        'automatically_confirm_receipt_days.gt' => '系统确认收货时间须大于0',

        'after_sales.require' => '请选择买家售后维权时效方式',
        'after_sales.in' => '买家售后维权时效状态值有误',
        'after_sales_days.requireIf' => '买家售后维权时间未填写',
        'after_sales_days.integer' => '买家售后维权时间须为整型',
        'after_sales_days.gt' => '买家售后维权时间须大于0',

        'inventory_occupancy.require' => '请选择库存占用时机',
        'inventory_occupancy.in' => '库存占用时机状态值有误',

        'return_inventory.require' => '请选择取消订单退回库存方式',
        'return_inventory.in' => '取消订单退回库存状态值有误',

        'return_coupon.require' => '请选择取消订单退回优惠方式',
        'return_coupon.in' => '取消订单退回优惠状态值有误',
    ];
}