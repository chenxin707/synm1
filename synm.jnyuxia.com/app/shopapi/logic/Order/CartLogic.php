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


use app\common\enum\CartEnum;
use app\common\enum\FootprintEnum;
use app\common\enum\OrderEnum;
use app\common\model\Cart;
use app\common\model\Goods;
use app\common\model\OrderGoods;
use app\common\service\FileService;


/**
 * 购物车逻辑
 * Class CartLogic
 * @package app\shopapi\logic
 */
class CartLogic
{

    /**
     * @notes 购物车列表
     * @param $userId
     * @return array
     * @author 段誉
     * @date 2021/7/20 16:32
     */
    public static function getCartLists($userId)
    {
        //列表数据
        $lists = [];
        //购物车数量
        $cartNum = 0;
        //合计
        $totalAmount = 0;

        $carts = (new Cart())->getCartLists($userId);

        foreach ($carts as $item) {
            //购物车状态
            $item['cart_status'] = CartEnum::NORMAL;

            //商品已被下架
            if ($item['goods']['status'] == 0) {
                $item['cart_status'] = CartEnum::SALE_OUT;
            }

            //商品已被删除
            if (empty($item['goods']) || empty($item['goods_item']) || $item['goods']['delete_time'] > 0) {
                continue;
            }

            //选中的商品且状态正常的才统计
            if ($item['selected'] == CartEnum::IS_SELECTED && $item['cart_status'] == CartEnum::NORMAL) {
                //购物车数量
                $cartNum += $item['goods_num'];
                //小计
                $item['sub_price'] = round($item['goods_item']['sell_price'] * $item['goods_num'], 2);
                //合计
                $totalAmount += $item['sub_price'];
            }

            //商品图片
            if (!empty($item['goods']['image'])) {
                $item['goods']['image'] = FileService::getFileUrl($item['goods']['image']);
            }

            if (!empty($item['goods_item']['image'])) {
                $item['goods_item']['image'] = FileService::getFileUrl($item['goods_item']['image']);
            }

            //购物车状态不正常时,选中状态 重置为 未选中
            if ($item['cart_status'] != CartEnum::NORMAL) {
                $item['selected'] = 0;
            }

            //用户已购买数量
            $item['buy_num'] = OrderGoods::withoutGlobalScope()
                ->aliasSid('og')
                ->alias('og')
                ->join('order o', 'o.id = og.order_id')
                ->where(['og.goods_id'=>$item['goods']['id'],'o.order_status'=>[OrderEnum::STATUS_WAIT_PAY,OrderEnum::STATUS_WAIT_DELIVERY,OrderEnum::STATUS_WAIT_RECEIVE,OrderEnum::STATUS_FINISH],'o.user_id'=>$userId,'o.order_type'=>[OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER]])
                ->sum('og.goods_num');


            $lists[] = $item;
        }

        return [
            'lists'         => $lists,
            'total_num'     => $cartNum,
            'total_amount'  => round($totalAmount, 2),
        ];
    }


    /**
     * @notes 添加购物车
     * @param $params
     * @param $userId
     * @return bool
     * @author 段誉
     * @date 2021/7/16 19:08
     */
    public static function addCart($params, $userId)
    {
        //商品信息
        $goods = (new Goods())->duokaiWithoutGlobalScope()
            ->aliasSid('g')
            ->alias('g')
            ->field(['g.status', 'g.delete_time', 'gi.stock', 'gi.goods_id'])
            ->join('goods_item gi', 'gi.goods_id = g.id')
            ->where(['gi.id' => $params['item_id']])
            ->find();

        // 汽泡足迹
        event('Footprint', ['type' => FootprintEnum::ADD_CART, 'user_id' => $userId, 'foreign_id'=>$goods->goods_id]);

        //购物车
        $cart = (new Cart())->getCartByItem($params['item_id'], $userId);

        //添加后的购物车数量(在验证器已验证是否足够库存)
        $cartNum = $params['goods_num'] + ($cart['goods_num'] ?? 0);

        if (!$cart->isEmpty()) {
            //购物车内已有该商品
            Cart::where('id', $cart['id'])->update([
                'goods_num'     => $cartNum,
                'update_time'   => time(),
            ]);
        } else {
            //新增购物车记录
            Cart::duokaiCreate([
                'user_id'       => $userId,
                'goods_id'      => $goods['goods_id'],
                'goods_num'     => $cartNum,
                'item_id'       => $params['item_id'],
                'create_time'   => time(),
            ]);
        }
        return true;
    }


    /**
     * @notes 修改购物车数量
     * @param $params
     * @return bool|string
     * @author 段誉
     * @date 2021/7/19 19:11
     */
    public static function changeCartNum($params)
    {
        $cart = (new Cart())->getCartById($params['cart_id'], $params['user_id']);

        if (intval($params['goods_num']) > $cart['goods_item']['stock']) {
            return '很抱歉,库存不足！';
        }

        $cart->goods_num = intval($params['goods_num']) <= 0 ? 1 : intval($params['goods_num']);
        $cart->duokaiSave();

        return true;
    }


    /**
     * @notes 购物车选中状态
     * @param $params
     * @return Cart
     * @author 段誉
     * @date 2021/7/19 18:56
     */
    public static function selected($params)
    {
        return Cart::duokaiUpdate(
            ['selected' => $params['selected']],
            ['id' => $params['cart_id'], 'user_id' => $params['user_id']]
        );
    }


    /**
     * @notes 购物车数量
     * @param $userId
     * @return array
     * @author 段誉
     * @date 2021/7/19 18:57
     */
    public static function getCartNum($userId)
    {
        $cartNum = (new Cart())->duokaiWithoutGlobalScope()
            ->aliasSid('c')
            ->alias('c')
            ->join('goods g', 'g.id = c.goods_id')
            ->join('goods_item i', 'i.id = c.item_id')
            ->where('c.user_id', $userId)
            ->sum('goods_num');

        return ['num' => $cartNum ?? 0];
    }


    /**
     * @notes 删除购物车
     * @param $id
     * @param $userId
     * @return bool
     * @author 段誉
     * @date 2021/7/19 18:57
     */
    public static function del($id, $userId)
    {
        //TODO 是否需要使用软删除
        return Cart::where(['id' => $id, 'user_id' => $userId])->useSoftDelete('delete_time', time())->delete();
    }

}
