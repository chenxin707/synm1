<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\shopapi\logic\Order;

use app\common\enum\CouponEnum;
use app\common\logic\BaseLogic;
use app\common\model\Coupon;


/**
 * Class OrderCouponLogic
 * 订单优惠券逻辑
 * @package app\shopapi\logic
 */
class OrderCouponLogic extends BaseLogic
{

    /**
     * @notes 计算优惠券优惠金额
     * @param $goodsLists //商品列表
     * @param $couponListsId //优惠券id(用户的领取记录id，coupon_list_id)
     * @return array
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/30 14:46
     */
    public static function calculateCouponDiscount($goodsLists, $couponListsId)
    {
        //获取优惠券信息
        $coupon = (new Coupon())->duokaiWithoutGlobalScope()
            ->aliasSid('c')
            ->alias('c')
            ->field('c.*, cl.invalid_time, cl.status as cl_status')
            ->join('coupon_list cl', 'cl.coupon_id = c.id')
            ->where(['cl.id' => $couponListsId])
            ->findOrEmpty();

        //优惠券不存在或已过优惠券可使用时间
        if ($coupon->isEmpty()
            || time() > $coupon['invalid_time']
            || $coupon['cl_status'] != CouponEnum::USE_STATUS_NOT
        ) {
            throw new \Exception('优惠券不可使用');
        }

        $couponGoods = explode(',', $coupon['use_goods_ids']);

        //可参与优惠的商品总金额和总数量
        $ableDiscount = self::ableDiscount($goodsLists, $coupon, $couponGoods);

        $totalDiscount = 0;
        $discountKey = 0;

        //计算优惠金额和每个商品项的可得优惠金额
        foreach ($goodsLists as &$goods) {
            //指定商品可用
            if ($coupon['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_ALLOW
                && !in_array($goods['goods_id'], $couponGoods)
            ) {
                continue;
            }

            //指定商品不可用
            if ($coupon['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_BAN
                && in_array($goods['goods_id'], $couponGoods)
            ) {
                continue;
            }

            //订单商品小计
            $sub_price = round($goods['sell_price'] * $goods['goods_num'], 2);

            //每个订单商品可以获得的优惠金额  (订单商品/可优惠商品总金额) * 优惠券金额
            $discount = round($sub_price / $ableDiscount['price'] * $coupon['money'], 2);

            //用于判断当前是否为最后一个商品
            if (($discountKey + 1) == $ableDiscount['count']) {
                $discount = round($coupon['money'] - $totalDiscount, 2);
            }

            //当前可获得优惠大于当前订单商品时
            $discount = ($discount > $sub_price) ? $sub_price : $discount;

            //每个商品优惠的金额
            $goods['discount_price'] = $discount;

            $discountKey += 1;
            $totalDiscount += $discount;
        }
        return [
            'goods' => $goodsLists,
            'discount' => round($totalDiscount, 2),
            'coupon_discount' => $coupon['money'],
        ];
    }



    /**
     * @notes 可参与优惠券优惠的总金额和总数量
     * @param $goods //订单商品列表
     * @param $coupon //优惠券信息
     * @param $couponGoods //优惠券关联商品id
     * @return array
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/30 14:44
     */
    public static function ableDiscount($goods, $coupon, $couponGoods)
    {
        $discountPrice = 0;
        $discountCount = 0;

        //1-全部商品；2-指定商品；3-指定商品不可用
        foreach ($goods as $good) {
            //全部商品
            if ($coupon['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_NOT) {
                $discountPrice += $good['sell_price'] * $good['goods_num'];
                $discountCount += 1;
            }

            //指定商品
            if (($coupon['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_ALLOW)
                && in_array($good['goods_id'], $couponGoods)
            ) {
                $discountPrice += $good['sell_price'] * $good['goods_num'];
                $discountCount += 1;
            }

            //指定商品不可用
            if ($coupon['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_BAN
                && !in_array($good['goods_id'], $couponGoods)
            ) {
                $discountPrice += $good['sell_price'] * $good['goods_num'];
                $discountCount += 1;
            }
        }

        if ($coupon['condition_type'] == CouponEnum::CONDITION_TYPE_FULL
            && $discountPrice < $coupon['condition_money']
        ) {
            throw new \Exception('所结算的商品中未满足使用的金额');
        }

        return [
            'count' => $discountCount,
            'price' => $discountPrice
        ];
    }

}