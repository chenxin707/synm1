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

namespace app\shopapi\lists;


use app\common\enum\SeckillEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\SeckillActivity;
use app\common\model\SeckillGoods;
use app\common\model\SeckillGoodsItem;

class SeckillLists extends BaseShopDataLists implements ListsSearchInterface
{
    /**
     * @notes 秒杀活动搜索条件
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:12
     */
    public function setSearch(): array
    {
        return [];
    }

    /**
     * @notes 获取秒杀活动列表
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/29 18:12
     */
    public function lists(): array
    {
        // 查询出正在进行的活动ID
        $activityIds = (new SeckillActivity())->where([
            ['start_time', '<=', time()],
            ['end_time', '>=', time()],
            ['status', '=', SeckillEnum::SECKILL_STATUS_CONDUCT]
        ])->column('id');


        // 查询出活动中的商品数据
        $lists = (new SeckillGoods())->duokaiWithoutGlobalScope()
            ->aliasSid('TG')
            ->alias('TG')
            ->field('TG.*')
            ->whereIn('TG.seckill_id', $activityIds)
            ->join('seckill_activity TA', 'TA.id = TG.seckill_id')
            ->order('TG.min_seckill_price asc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()->toArray();

        // 处理数据
        foreach ($lists as &$item) {
            $item['name'] = $item['goods_snap']['name'];
            $item['image'] = $item['goods_snap']['image'];
            $item['original_price'] = $item['goods_snap']['max_price'];
            $item['closing_order'] = SeckillGoodsItem::where([
                'seckill_id' => $item['seckill_id'],
                'goods_id' => $item['goods_id']
            ])->sum('closing_order');
            $item['sales_volume'] = SeckillGoodsItem::where([
                'seckill_id' => $item['seckill_id'],
                'goods_id' => $item['goods_id']
            ])->sum('sales_volume');

            unset($item['goods_snap']);
        }

        return $lists;
    }

    /**
     * @notes 秒杀活动数量
     * @return int
     * @author 张无忌
     * @date 2021/7/29 18:13
     */
    public function count(): int
    {
        $activityIds = (new SeckillActivity())->where([
            ['start_time', '<=', time()],
            ['end_time', '>=', time()],
            ['status', '=', SeckillEnum::SECKILL_STATUS_CONDUCT]
        ])->column('id');

        return (new SeckillGoods())->duokaiWithoutGlobalScope()
            ->aliasSid('TG')
            ->alias('TG')
            ->field('TG.*')
            ->whereIn('TG.seckill_id', $activityIds)
            ->join('seckill_activity TA', 'TA.id = TG.seckill_id')
            ->count();
    }
}