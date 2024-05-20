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

namespace app\common\model;

/**
 * 购物车模型
 * Class Cart
 * @package app\common\model
 */
class Cart extends BaseModel
{

    /**
     * @notes 关联商品模型
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2021/7/20 16:35
     */
    public function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id')->hidden(['content'])->removeOption('soft_delete');
    }


    /**
     * @notes 关联商品规格模型
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2021/7/20 16:35
     */
    public function goodsItem()
    {
        return $this->hasOne(GoodsItem::class, 'id', 'item_id');
    }


    /**
     * @notes 获取购物车列表
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/7/20 16:36
     */
    public function getCartLists($userId)
    {
        return $this->withoutField(['create_time', 'update_time'])
            ->with([
                'goods' => function ($query) {
                    $query->field(['id', 'name', 'image', 'status', 'delete_time']);
                },
                'goods_item' => function ($query) {
                    $query->field(['id', 'spec_value_str', 'sell_price', 'image']);
                }
            ])
            ->where(['user_id' => $userId])
            ->order(['id' => 'desc'])
            ->select()->toArray();
    }


    /**
     * @notes 通过规格id获取购物车信息
     * @param $itemId
     * @param $userId
     * @return array|\think\Model
     * @author 段誉
     * @date 2021/7/20 16:36
     */
    public function getCartByItem($itemId, $userId)
    {
        return $this->with(['goods', 'goods_item'])
            ->where(['user_id' => $userId, 'item_id' => $itemId])
            ->findOrEmpty();
    }


    /**
     * @notes 通过购物车id获取信息
     * @param $id
     * @param $userId
     * @return array|\think\Model
     * @author 段誉
     * @date 2021/7/20 16:36
     */
    public function getCartById($id, $userId)
    {
        return $this->with(['goods', 'goods_item'])
            ->where(['user_id' => $userId, 'id' => $id])
            ->findOrEmpty();
    }


}