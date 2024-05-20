<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\enum;

/**
 * 积分订单
 * Class IntegralOrderEnum
 * @package app\common\enum
 */
class IntegralOrderEnum
{
    // 订单状态order_status
    const ORDER_STATUS_NO_PAID = 0;//待支付
    const ORDER_STATUS_DELIVERY = 1;//待发货
    const ORDER_STATUS_GOODS = 2;//待收货
    const ORDER_STATUS_COMPLETE = 3;//已完成
    const ORDER_STATUS_DOWN = 4;//已关闭

    // 退款状态 refund_status
    const NO_REFUND = 0;//未退款
    const IS_REFUND = 1;//已退款

    // 发货状态 shipping_status
    const SHIPPING_NO = 0;   //未发货
    const SHIPPING_FINISH = 1;  //已发货


    /**
     * @notes 订单状态
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/3 14:18
     */
    public static function getOrderStatus($type=true)
    {
        $desc = [
            self::ORDER_STATUS_NO_PAID  => '待支付',
            self::ORDER_STATUS_DELIVERY => '待发货',
            self::ORDER_STATUS_GOODS    => '待收货',
            self::ORDER_STATUS_COMPLETE => '已完成',
            self::ORDER_STATUS_DOWN     => '已关闭'
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知来源';
    }


    /**
     * @notes 退款状态
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/3 14:18
     */
    public static function getRefundStatus($type=true)
    {
        $desc = [
            self::NO_REFUND   => '未退款',
            self::IS_REFUND  => '已退款',
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '--';
    }
}