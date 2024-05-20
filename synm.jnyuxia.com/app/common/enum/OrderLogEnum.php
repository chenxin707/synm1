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


namespace app\common\enum;

/**
 * 订单日志
 * Class OrderLogEnum
 * @package app\common\enum
 */
class OrderLogEnum
{
    //操作人类型
    const TYPE_SYSTEM   = 1;//系统
    const TYPE_SHOP     = 2;//商家
    const TYPE_USER     = 3;//会员


    //订单动作
    const USER_ADD_ORDER        = 101;//提交订单
    const USER_CANCEL_ORDER     = 102;//取消订单
    const USER_CONFIRM_ORDER    = 103;//确认收货
    const USER_PAID_ORDER       = 104;//支付订单
    const USER_VERIFICATION     = 105;//会员核销订单

    const SHOP_CANCEL_ORDER     = 201;//商家取消订单
    const SHOP_DELIVERY_ORDER   = 202;//商家发货
    const SHOP_CONFIRM_ORDER    = 203;//商家确认收货
    const SHOP_ADDRESS_EDIT     = 204;//商家修改地址
    const SHOP_ORDER_REMARKS    = 205;//商家备注
    const SHOP_CHANGE_PRICE     = 206;//商家修改价格
    const SHOP_EXPRESS_PRICE    = 207;//商家修改运费
    const SHOP_VERIFICATION    = 208;//商家提货核销

    const SYSTEM_CANCEL_ORDER   = 301;//系统取消订单
    const SYSTEM_CONFIRM_ORDER  = 302;//系统确认订单


    /**
     * @notes 订单日志明细
     * @param bool $value
     * @return string|string[]
     * @author 段誉
     * @date 2021/8/2 14:32
     */
    public static function getRecordDesc($value = true)
    {
        $desc = [
            //系统
            self::SYSTEM_CANCEL_ORDER   => '系统取消订单',
            self::SYSTEM_CONFIRM_ORDER  => '系统确认收货',

            //商家
            self::SHOP_CANCEL_ORDER     => '商家取消订单',
            self::SHOP_DELIVERY_ORDER   => '商家发货',
            self::SHOP_CONFIRM_ORDER    => '商家确认收货',
            self::SHOP_ADDRESS_EDIT     => '商家修改地址',
            self::SHOP_ORDER_REMARKS    => '商家备注',
            self::SHOP_CHANGE_PRICE     => '商家修改价格',
            self::SHOP_EXPRESS_PRICE    => '商家修改运费',
            self::SHOP_VERIFICATION     => '商家提货核销',

            //会员
            self::USER_ADD_ORDER        => '会员提交订单',
            self::USER_CANCEL_ORDER     => '会员取消订单',
            self::USER_CONFIRM_ORDER    => '会员确认收货',
            self::USER_PAID_ORDER       => '会员支付订单',
            self::USER_VERIFICATION     => '会员核销订单',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }
}