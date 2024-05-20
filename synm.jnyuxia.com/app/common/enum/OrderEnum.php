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
 * 订单枚举
 * Class OrderEnum
 * @package app\common\enum
 */
class OrderEnum
{
    //订单类型
    const NORMAL_ORDER = 0; //普通订单
    const TEAM_ORDER = 1; //拼团订单
    const SECKILL_ORDER = 2; //秒杀订单
    const BARGAIN_ORDER = 3; //砍价订单
    const VIRTUAL_ORDER = 4; //虚拟订单


    //订单状态
    const STATUS_WAIT_PAY       = 0;  //待付款
    const STATUS_WAIT_DELIVERY  = 1;  //待发货
    const STATUS_WAIT_RECEIVE   = 2;  //待收货
    const STATUS_FINISH         = 3;  //已完成
    const STATUS_CLOSE          = 4;  //已关闭

    //按钮(显示/隐藏)
    const BTN_HIDE = 0;
    const BTN_SHOW = 1;

    //核销状态
    const NOT_WRITTEN_OFF = 0;//待核销
    const WRITTEN_OFF = 1;//已核销

    // 订单类型
    const ORDER_TYPE = [
        self::NORMAL_ORDER,
        self::TEAM_ORDER,
        self::SECKILL_ORDER,
        self::BARGAIN_ORDER,
        self::VIRTUAL_ORDER,

    ];

    //订单赠送积分场景
    const INTEGRAL_ORDER_PAY = 1;//订单付款
    const INTEGRAL_ORDER_DELIVERY = 2;//订单发货
    const INTEGRAL_ORDER_FINISH = 3;//订单完成
    const INTEGRAL_ORDER_AFTER_SALE_OVER = 4;//订单超过售后期


    /**
     * @notes 订单类型
     * @param bool $value
     * @return array|mixed
     * @author 段誉
     * @date 2021/7/22 17:57
     */
    public static function getOrderTypeDesc($value = true)
    {
        $data = [
            self::NORMAL_ORDER => '普通订单',
            self::TEAM_ORDER => '拼团订单',
            self::SECKILL_ORDER => '秒杀订单',
            self::BARGAIN_ORDER => '砍价订单',
            self::VIRTUAL_ORDER=> '虚拟订单',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 订单状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/8/4 2:34 下午
     */
    public static function getOrderStatusDesc($value = true)
    {
        $data = [
            self::STATUS_WAIT_PAY => '待付款',
            self::STATUS_WAIT_DELIVERY => '待发货',
            self::STATUS_WAIT_RECEIVE => '待收货',
            self::STATUS_FINISH => '已完成',
            self::STATUS_CLOSE => '已关闭',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 核销状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/8/26 4:29 下午
     */
    public static function getVerificationStatusDesc($value = true)
    {
        $data = [
            self::NOT_WRITTEN_OFF => '待核销',
            self::WRITTEN_OFF => '已核销',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }
}