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
namespace app\common\logic;

use app\common\enum\DiscountEnum;
use app\common\model\DiscountGoods;
use app\common\model\DiscountGoodsItem;
use app\common\model\Goods;
use app\common\model\User;
use app\common\model\UserLevel;

/**
 * 会员折扣逻辑层
 * Class DiscountLogic
 * @package app\common\logic
 */
class DiscountLogic
{

    /**
     * @notes 生成会员等级和商品规格的数据
     * @param $goods
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2022/5/6 18:51
     */
    public static function leveGoodsItem($goods)
    {
        $userLevel = UserLevel::field('id,name,discount')->order('rank asc')->select();
        $discountGoods = DiscountGoodsItem::where(['goods_id' => $goods->id])->column('discount_price', 'CONCAT_WS(\'-\',level,item_id)');
        $specValueList = $goods->spec_value_list;

        foreach ($userLevel as $key => $level) {
            $goodsItem = [];
            foreach ($specValueList as $specValue) {
                $searchKey = $level->id . '-' . $specValue->id;
                //折扣价
                $discountPrice = $discountGoods[$searchKey] ?? $specValue->sell_price;

                $goodsItem[] = [
                    'item_id' => $specValue['id'],
                    'spec_value_str' => $specValue->spec_value_str,
                    'sell_price' => $specValue->sell_price,
                    'discount_price' => $discountPrice,
                ];
            }
            $userLevel[$key]['goods_item'] = $goodsItem;
        }
        return $userLevel->toArray();
    }


    /**
     * @notes 根据用户当前等级获取商品折扣价 tips：如果商品没折扣价则不返回
     * @param $userId
     * @param $goodsIds
     * @return array
     * @author cjhao
     * @date 2022/5/7 15:09
     * tips: 返回数据格式 =>[
     *      'goods_id'  => [
     *          '规格id'  => []
     *      ]
     *  ]
     */
    public static function getGoodsDiscount(int $userId, array $goodsIds)
    {
        $userLevel = User::where(['id' => $userId])
            ->field('id,level')
            ->with('user_level')
            ->find();
        $levelDiscount = $userLevel->discount;
        $discountGoods = DiscountGoods::where(['goods_id' => $goodsIds])->column('*', 'goods_id');
        $discountGoodsItem = DiscountGoodsItem::where(['goods_id' => $goodsIds,'level'=>$userLevel->level])->column('discount_price', 'item_id');

        $goodsLists = Goods::where(['id' => $goodsIds])->with('spec_value_list')->field('id')->select()->toArray();
        $discountData = [];
        /**
         * 1.判断是否参与折扣，否-直接跳过
         * 2.判断当前会员等级是否有折扣权益，否-看该商品是否自定义折扣
         */
        foreach ($goodsLists as $goods) {
            $discount = $discountGoods[$goods['id']] ?? [];
            if (empty($discount)) {
                continue;
            }
            //没参与折扣
            if (DiscountEnum::DISCOUNT_NOT_JOIN == $discount['is_discount']) {
                continue;
            }
            //参与折扣，当前等级没有折扣权益
            if (DiscountEnum::DISCOUNT_RULE_LEVEL == $discount['discount_rule'] && $levelDiscount <= 0) {
                continue;
            }


            foreach ($goods['spec_value_list'] as $goodsItem) {

                if (DiscountEnum::DISCOUNT_RULE_LEVEL == $discount['discount_rule']) {
                    //参与折扣，按等级折扣计算
                    $discountPrice = round($goodsItem['sell_price'] * ($levelDiscount / 10), 2);

                } else {
                    //自定义会员价
                    $discountPrice = $discountGoodsItem[$goodsItem['id']] ?? '';
                }
                //只返回折扣价大于等于零的
                if($discountPrice >= 0){
                    $discountData[$goodsItem['goods_id']][$goodsItem['id']] = [
                        'sell_price'        => $goodsItem['sell_price'],     //售价
                        'discount_price'    => $discountPrice,               //折扣价
                    ];
                }

            }

        }
        return $discountData;
    }
}