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


class CouponEnum
{
    // 使用条件
    const CONDITION_TYPE_NOT  = 1;  //无门槛
    const CONDITION_TYPE_FULL = 2;  //需订单满足金额

    // 发放数量限制
    const SEND_TOTAL_TYPE_NOT   = 1; // 无限数量
    const SEND_TOTAL_TYPE_FIXED = 2; // 固定数量

    // 用券时间
    const USE_TIME_TYPE_FIXED    = 1; //固定时间
    const USE_TIME_TYPE_TODAY    = 2; //当日起多少天内
    const USE_TIME_TYPE_TOMORROW = 3; //次日起多少天内

    // 领取方式
    const GET_TYPE_USER  = 1; //买家领取
    const GET_TYPE_STORE = 2; //商家发放

    // 领取数量限制
    const GET_NUM_TYPE_NOT   = 1; //不限制
    const GET_NUM_TYPE_LIMIT = 2; //限制张数
    const GET_NUM_TYPE_DAY   = 3; //每天限制张数

    // 允许使用商品
    const USE_GOODS_TYPE_NOT   = 1; //不限制
    const USE_GOODS_TYPE_ALLOW = 2; //允许商品
    const USE_GOODS_TYPE_BAN   = 3; //禁止商品

    // 优惠券状态
    const COUPON_STATUS_NOT     = 1; //未开始
    const COUPON_STATUS_CONDUCT = 2; //进行中
    const COUPON_STATUS_END     = 3; //已结束

    // 使用状态
    const USE_STATUS_NOT    = 0; //未使用
    const USE_STATUS_OK     = 1; //已使用
    const USE_STATUS_EXPIRE = 2; //已过期
    const USE_STATUS_VOID   = 3; //已作废


    /**
     * @notes 领取方式
     * @param bool $value
     * @return array|mixed
     * @author 张无忌
     * @date 2021/7/20 17:36
     */
    public static function getTypeDesc($value = true)
    {
        $data = [
            self::GET_TYPE_USER  => '买家领取',
            self::GET_TYPE_STORE => '卖家发放'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 优惠券状态
     * @param bool $value
     * @return array|mixed
     * @author 张无忌
     * @date 2021/7/20 17:55
     */
    public static function getCouponStatusDesc($value = true)
    {
        $data = [
            self::COUPON_STATUS_NOT     => '未开始',
            self::COUPON_STATUS_CONDUCT => '进行中',
            self::COUPON_STATUS_END     => '已结束'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 优惠券使用状态
     * @param bool $value
     * @return array|mixed
     * @author 张无忌
     * @date 2021/7/21 17:03
     */
    public static function getUseStatusDesc($value = true)
    {
        $data = [
            self::USE_STATUS_NOT     => '未使用',
            self::USE_STATUS_OK      => '已使用',
            self::USE_STATUS_EXPIRE  => '已过期',
            self::USE_STATUS_VOID    => '已作废'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }
}