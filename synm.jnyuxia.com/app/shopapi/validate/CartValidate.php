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


namespace app\shopapi\validate;

use app\common\model\Cart;
use app\common\model\Goods;
use app\common\validate\BaseValidate;

/**
 * 购物车验证
 * Class CartValidate
 * @package app\shopapi\validate
 */
class CartValidate extends BaseValidate
{
    protected $rule = [
        'cart_id'   => 'require|checkCart',
        'goods_num' => 'require|integer|gt:0',
        'item_id'   => 'require|checkGoods',
        'selected'  => 'require|in:0,1',
    ];

    protected $message = [
        'item_id'           => '请选择商品',
        'goods_num.require' => '商品数量不能为0',
        'goods_num.gt'      => '商品数量需大于0',
        'goods_num.integer' => '商品数量需为整数',
        'cart_id.require'   => '参数错误',
        'selected.require'  => '参数错误',
        'selected.in'       => '参数错误',
    ];

    protected $scene = [
        'del'       =>  ['cart_id'],
        'add'       =>  ['item_id', 'goods_num'],
        'selected'  =>  ['cart_id', 'selected'],
        'change'    =>  ['cart_id', 'goods_num'],
    ];


    /**
     * @notes 验证购物车是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/7/16 18:32
     */
    protected function checkCart($value, $rule, $data)
    {
        $cart = (new Cart())->getCartById($value, $data['user_id']);

        if ($cart->isEmpty()) {
            return '购物车不存在';
        }
        return true;
    }

    /**
     * @notes 验证商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/7/16 18:31
     */
    protected function checkGoods($value, $rule, $data)
    {
        $goods = (new Goods())->duokaiWithoutGlobalScope()
            ->aliasSid('g')
            ->alias('g')
            ->field(['g.status', 'g.delete_time', 'gi.stock'])
            ->join('goods_item gi', 'gi.goods_id = g.id')
            ->where(['gi.id' => $value])
            ->find();

        if (empty($goods) || $goods['status'] == 0 || $goods['delete_time'] > 0) {
            return '商品已下架';
        }

        $cart = (new Cart())->getCartByItem($data['item_id'], $data['user_id']);

        $cartNum = $data['goods_num'] + $cart['goods_num'] ?? 0;
        if ($goods['stock'] < $cartNum) {
            return '很抱歉,商品库存不足';
        }
        return true;
    }

}