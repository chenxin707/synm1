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


class DeliveryEnum
{
    //配送方式
    const EXPRESS_DELIVERY = 1;//快递发货
    const SELF_DELIVERY = 2;//门店自提
    const SAME_CITY = 3;//同城配送
    const DELIVERY_VIRTUAL = 4;//虚拟发货

    //配送状态
    const NOT_SHIPPED = 0;//未发货
    const SHIPPED = 1;//已发货

    //发货方式
    const EXPRESS = 1;//快递配送
    const NO_EXPRESS = 2;//无需快递

    // 所有配送方式
    const DELIVERY_TYPE = [
        self::EXPRESS_DELIVERY,
        self::SELF_DELIVERY,
//        self::SAME_CITY,
        self::DELIVERY_VIRTUAL,

    ];

    // 电子面单支付方式
    const SENDER_PAYMENT = 1;
    const ARRIVAL_PAYMENT = 2;
    const MONTHLY = 3;
    const THIRD_PARTY_PAYMENT = 4;

    /**
     * @notes 获取电子面单支付方式
     * @param null $payment
     * @return string|string[]
     * @author Tab
     * @date 2021/11/22 14:31
     */
    public static function getFaceSheetPaymentDesc($payment = null)
    {
        $desc = [
            self::SENDER_PAYMENT => '寄方付',
            self::ARRIVAL_PAYMENT => '到付',
            self::MONTHLY => '月结',
            self::THIRD_PARTY_PAYMENT => '第三方支付',
        ];
        if (is_null($payment)) {
            return $desc;
        }
        return $desc[$payment] ?? '';
    }

    /**
     * @notes 获取快递100对应的支付方式
     * @param null $payment
     * @return string|string[]
     * @author Tab
     * @date 2021/11/23 11:31
     */
    public static function getKuaidi100Desc($payment = null)
    {
        $desc = [
            self::SENDER_PAYMENT => 'SHIPPER',
            self::ARRIVAL_PAYMENT => 'CONSIGNEE',
            self::MONTHLY => 'MONTHLY',
            self::THIRD_PARTY_PAYMENT => 'THIRDPARTY',
        ];
        if (is_null($payment)) {
            return $desc;
        }
        return $desc[$payment] ?? '';
    }

    /**
     * @notes 配送方式
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/8/5 9:53 上午
     */
    public static function getDeliveryTypeDesc($value = true)
    {
        $data = [
            self::EXPRESS_DELIVERY => '快递发货',
            self::SELF_DELIVERY => '门店自提',
//            self::SAME_CITY => '同城配送',
            self::DELIVERY_VIRTUAL  => '虚拟发货'
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 配送状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/8/6 4:57 下午
     */
    public static function getDeliveryStatusDesc($value = true)
    {
        $data = [
            self::NOT_SHIPPED => '未发货',
            self::SHIPPED => '已发货',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 发货方式
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/8/13 3:38 下午
     */
    public static function getSendTypeDesc($value = true)
    {
        $data = [
            self::EXPRESS => '快递配送',
            self::NO_EXPRESS => '无需快递',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }
}