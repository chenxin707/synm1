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


use app\common\enum\GoodsEnum;
use app\common\enum\TeamEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\TeamActivity;
use app\common\model\TeamGoods;
use app\common\model\TeamGoodsItem;

class TeamLists extends BaseShopDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索
     * @return array
     * @author 张无忌
     * @date 2021/8/3 15:13
     */
    public function setSearch(): array
    {
        return [];
    }

    /**
     * @notes 拼团活动商品列表
     * @return array
     * @author 张无忌
     * @date 2021/8/3 15:11
     */
    public function lists(): array
    {
        // 查询出正在进行的活动ID
        $activityIds = (new TeamActivity())->where([
            ['start_time', '<=', time()],
            ['end_time', '>=', time()],
            ['status', '=', TeamEnum::TEAM_STATUS_CONDUCT]
        ])->column('id');

        // 查询出活动中的商品数据
        $lists = (new TeamGoods())->duokaiWithoutGlobalScope()
            ->aliasSid('TG')
            ->alias('TG')
            ->field('TG.*,TA.people_num,TA.partake_number')
            ->whereIn('TG.team_id', $activityIds)
            ->where('G.status', GoodsEnum::STATUS_SELL)
            ->join('team_activity TA', 'TA.id = TG.team_id')
            ->join('goods G', 'G.id = TG.goods_id')
            ->order('TG.min_team_price asc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()->toArray();

        $goodsIds = array_column($lists,'goods_id');
        $salesList = TeamGoodsItem::where(['team_id'=>$activityIds,'goods_id'=>$goodsIds])
            ->group('goods_id')
            ->column('sum(sales_volume)','goods_id');

        // 处理数据
        foreach ($lists as &$item) {
            $item['name']  = $item['goods_snap']['name'];
            $item['image'] = $item['goods_snap']['image'];
            $item['min_price'] = $item['goods_snap']['min_price'];
            $item['max_price'] = $item['goods_snap']['max_price'];
            unset($item['goods_snap']);
            $item['activity_sales'] = $salesList[$item['goods_id']] ?? 0;
        }

        return $lists;
    }

    /**
     * @notes 总数量
     * @return int
     * @author 张无忌
     * @date 2021/8/3 15:12
     */
    public function count(): int
    {
        $activityIds = (new TeamActivity())->where([
            ['start_time', '<=', time()],
            ['end_time', '>=', time()],
            ['status', '=', TeamEnum::TEAM_STATUS_CONDUCT]
        ])->column('id');

        return (new TeamGoods())->duokaiWithoutGlobalScope()
            ->aliasSid('TG')
            ->alias('TG')
            ->field('TG.*,TA.people_num')
            ->whereIn('TG.team_id', $activityIds)
            ->where('G.status', GoodsEnum::STATUS_SELL)
            ->join('goods G', 'G.id = TG.goods_id')
            ->join('team_activity TA', 'TA.id = TG.team_id')
            ->count();
    }
}