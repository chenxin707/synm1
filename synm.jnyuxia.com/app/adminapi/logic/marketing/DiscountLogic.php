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
namespace app\adminapi\logic\marketing;

use app\common\enum\DiscountEnum;
use app\common\enum\GoodsEnum;
use app\common\model\DiscountGoods;
use app\common\model\DiscountGoodsItem;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\UserLevel;
use think\Exception;
use think\facade\Db;

/**
 * 会员折扣逻辑层
 * Class DiscountLogic
 * @package app\adminapi\logic\marketing
 */
class DiscountLogic
{

    /**
     * @notes 返回其他状态列表
     * @return array
     * @author cjhao
     * @date 2022/5/9 18:56
     */
    public function otherLists()
    {
        return [
            'status_list'   => GoodsEnum::getStatusDesc(),
            'type_list'     => GoodsEnum::getGoodsTypeDesc(),
            'discount_list' => DiscountEnum::getDiscountStatusDesc(),
        ];

    }

    /**
     * @notes 参与活动
     * @param $params
     * @return bool
     * @throws \Exception
     * @author cjhao
     * @date 2022/5/5 16:56
     */
    public function join($params)
    {
        $goods = Goods::column('id');

        $discountGoods = DiscountGoods::where(['goods_id' => $params['goods_ids']])->column('goods_id');
        $updateData = [];
        $addData = [];
        foreach ($params['goods_ids'] as $goodsId) {
            if (!in_array($goodsId, $goods)) {
                continue;
            }
            //更新
            if (in_array($goodsId, $discountGoods)) {
                $updateData[] = $goodsId;

            } else {
                //写入
                $addData[] = [
                    'goods_id' => $goodsId,
                    'is_discount' => 1,
                    'discount_rule' => 1,
                ];
            }
        }
        if ($updateData) {
            DiscountGoods::where(['goods_id' => $updateData])->update(['is_discount' => 1, 'update_time' => time()]);
        }
        if ($addData) {
            (new DiscountGoods)->duokaiSaveAll($addData);
        }
        return true;
    }


    /**
     * @notes 退出折扣活动
     * @param $params
     * @return bool
     * @author cjhao
     * @date 2022/5/5 17:13
     */
    public function quit($params)
    {
        DiscountGoods::where(['goods_id' => $params['goods_ids']])->update(['is_discount' => 0, 'update_time' => time()]);
        return true;
    }


    /**
     * @notes 设置折扣详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2022/5/6 18:31
     */
    public function detail($params)
    {
        //商品
        $goods = Goods::where(['id' => $params['goods_id']])->with('spec_value_list')->field('id,name,code,image,spec_type')->find();
        //折扣商品
        $discountGoods = DiscountGoods::where(['goods_id' => $params['goods_id']])->find();
        //合成会员等级和商品规格数据
        $levelGoodsItem = \app\common\logic\DiscountLogic::leveGoodsItem($goods);
        return [
            'goods_id' => $goods->id,
            'name' => $goods->name,
            'image' => $goods->image,
            'code' => $goods->code,
            'spec_type' => $goods->spec_type,
            'is_discount' => $discountGoods->is_discount ?? 0,
            'discount_rule' => $discountGoods->discount_rule ?? 1,
            'level_goods_item' => $levelGoodsItem,
        ];
    }


    /**
     * @notes 设置会员价
     * @param $params
     * @return bool|string
     * @author cjhao
     * @date 2022/5/7 9:25
     */
    public function setDiscount($params)
    {
        Db::startTrans();
        try {

            $discountGoods = DiscountGoods::where(['goods_id' => $params['goods_id']])->find();
            $discountGoods || $discountGoods = new DiscountGoods();

            $discountGoods->goods_id = $params['goods_id'];
            $discountGoods->is_discount = $params['is_discount'];
            $discountGoods->discount_rule = $params['discount_rule'];
            $discountGoods->duokaiSave();

            //不参与等级折扣， 参与会员折扣，按会员等级折扣
            if (DiscountEnum::DISCOUNT_RULE_LEVEL == $params['discount_rule']) {
                //不删除会员折扣商品表，直接提交数据
                Db::commit();
                return true;
            }

            //自定义会员价
            $goodsItemList = GoodsItem::where(['goods_id' => $params['goods_id']])->column('spec_value_str,sell_price', 'id');
            $userLevelIds = UserLevel::column('id');
            $goodsItemIds = array_keys($goodsItemList);

            //验证数据
            $data = [];
            foreach ($params['level_goods_item'] as $level) {
                if (!isset($level['id']) || empty($level['id'])) {
                    throw new Exception('数据错误');
                }
                //会员等级不存在,不处理
                if (!in_array($level['id'], $userLevelIds)) {
                    continue;
                }

                foreach ($level['goods_item'] as $goodsItem) {
                    //规格不存在,不处理
                    if (!in_array($goodsItem['item_id'], $goodsItemIds)) {
                        continue;
                    }
                    $specValueStr = $goodsItemList[$goodsItem['item_id']]['spec_value_str'] ?? '';
                    if (!isset($goodsItem['discount_price']) || $goodsItem['discount_price'] < 0) {
                        throw new Exception('请输入商品规格：' . $specValueStr . '的会员价');
                    }

                    $sellPrice = $goodsItemList[$goodsItem['item_id']]['sell_price'] ?? 0;
                    if ($goodsItem['discount_price'] > $sellPrice) {
                        throw new Exception('商品规格：' . $specValueStr . '会员价要低于售价');
                    }
                    $data[] = [
                        'discount_id'       => $discountGoods->id,
                        'goods_id'          => $params['goods_id'],
                        'item_id'           => $goodsItem['item_id'],
                        'level'             => $level['id'],
                        'discount_price'    => $goodsItem['discount_price'],
                    ];
                }


            }
            if (empty($data)) {
                throw new Exception('数据错误，请刷新页面');
            }
            //先清除之前的数据
            DiscountGoodsItem::where(['goods_id' => $params['goods_id']])->delete();
            (new DiscountGoodsItem ())->duokaiSaveAll($data);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}