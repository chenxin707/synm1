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

namespace app\shopapi\service;


use app\common\enum\CouponEnum;
use app\common\model\CouponList;

class CouponService
{

    /**
     * @notes 验证活动商品是否可用优惠券
     * @param $user_id
     * @param $coupon_list_id
     * @param $orderStatus
     * @return array
     * @author 张无忌
     * @date 2021/8/3 18:37
     */
    public static function isUsable($user_id, $coupon_list_id, $orderStatus)
    {
        $coupon = (new CouponList())->duokaiWithoutGlobalScope()
            ->aliasSid('CL')
            ->alias('CL')
            ->field('C.*,CL.id as cl_id,CL.create_time as receive_time')
            ->join('coupon C', 'C.id = CL.coupon_id')
            ->where([
                ['CL.id', '=', intval($coupon_list_id)],
                ['CL.user_id', '=', intval($user_id)],
                ['CL.status', '=', CouponEnum::USE_STATUS_NOT],
                ['CL.invalid_time', '>', time()],
            ])->findOrEmpty()->toArray();

        if ($coupon) {
            $result = self::isUseWhere($coupon, $orderStatus['goods']['id'], $orderStatus['total_amount']);
            if ($result) {
                return [
                    'isUsable' => true,
                    'money'    => $coupon['money']
                ];
            }
        }

        return [
            'isUsable' => false,
            'money'    => 0
        ];
    }

    /**
     * @notes 验证是否满足基本使用条件
     * @param $coupon
     * @param $goods_id
     * @param $order_total_amount
     * @return bool
     * @author 张无忌
     * @date 2021/8/4 9:47
     */
    public static function isUseWhere($coupon, $goods_id, $order_total_amount)
    {
        switch ($coupon['use_time_type']) {
            case CouponEnum::USE_TIME_TYPE_FIXED:
                if ($coupon['use_time_start'] >= time() and $coupon['use_time_end'] <= time()) {
                    return false;
                }
                break;
            case CouponEnum::USE_TIME_TYPE_TODAY:
                $use_time = intval($coupon['use_time'] * 86400);
                if ($coupon['receive_time'] >= time() and $use_time <= time()) {
                    return false;
                }
                break;
            case CouponEnum::USE_TIME_TYPE_TOMORROW:
                $receive_time = date('Y-m-d', $coupon['receive_time']);
                $receive_time = strtotime($receive_time) + 86400;
                $use_time = intval($coupon['use_time'] * 86400);
                if (time() <= $receive_time and $use_time <= time()) {
                    return false;
                }
                break;
        }

        switch ($coupon['use_goods_type']) {
            case CouponEnum::USE_GOODS_TYPE_NOT:
                break;
            case CouponEnum::USE_GOODS_TYPE_ALLOW:
                if (!$coupon['use_goods_ids']) {
                    return false;
                }

                $use_goods_ids = explode(',', $coupon['use_goods_ids']);
                if (!in_array($goods_id, $use_goods_ids)) {
                    return false;
                }
                break;
            case CouponEnum::USE_GOODS_TYPE_BAN:
                if ($coupon['use_goods_ids']) {
                    $use_goods_ids = explode(',', $coupon['use_goods_ids']);
                    if (in_array($goods_id, $use_goods_ids)) {
                        return false;
                    }
                }
                break;
        }

        switch ($coupon['condition_type']) {
            case CouponEnum::CONDITION_TYPE_NOT:
                break;
            case CouponEnum::CONDITION_TYPE_FULL:
                if ($order_total_amount < $coupon['condition_money']) {
                    return false;
                }
                break;
        }


        return true;
    }
}