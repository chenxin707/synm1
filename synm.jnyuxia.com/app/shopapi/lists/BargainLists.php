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

use app\common\enum\BargainEnum;
use app\common\enum\GoodsEnum;
use app\common\lists\BaseDataLists;
use app\common\lists\ListsExtendInterface;
use app\common\model\BargainGoods;
use app\common\model\BargainInitiate;
use app\common\service\FileService;

/**
 * 砍价活动列表
 * Class BargainLists
 * @package app\shopapi\lists
 */
class BargainLists extends BaseDataLists implements ListsExtendInterface
{
    /**
     * @notes 统计数据
     * @return mixed|void
     * @author Tab
     * @date 2021/8/28 15:14
     */
    public function extend()
    {
        // 砍价成功数量
        $success = BargainInitiate::where('status', BargainEnum::STATUS_SUCCESS)->count();

        return [
            'success' => $success
        ];
    }

    /**
     * @notes 设置搜索
     * @author Tab
     * @date 2021/8/28 15:13
     */
    public function setSearch()
    {
        $this->searchWhere = [
            // 进行中状态
            ['ba.status', '=', BargainEnum::ACTIVITY_STATUS_ING],
            // 已到活动开始时间
            ['ba.start_time', '<=', time()],
            // 未到活动结束时间
            ['ba.end_time', '>', time()],
            // 商品状态须为已上架
            ['g.status', '=', GoodsEnum::STATUS_SELL],
        ];
        // 越临近结束的砍价越靠前
        $this->sortOrder = ['ba.end_time' => 'asc'];
    }

    /**
     * @notes 列表
     * @return array
     * @author Tab
     * @date 2021/8/28 15:13
     */
    public function lists(): array
    {
        // 初始化搜索
        $this->setSearch();

        $field = [
            'g.image' => 'goods_image',
            'g.name' => 'goods_name',
            'g.max_price' => 'goods_max_price',
            'ba.end_time',
            'bg.activity_id' => 'bargain_min_price',
            'bg.activity_id',
            'bg.goods_id',
        ];
        $lists = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods g', 'g.id = bg.goods_id')
            ->leftJoin('bargain_activity ba', 'ba.id = bg.activity_id')
            ->distinct(true)
            ->field($field)
            ->where($this->searchWhere)
            ->order($this->sortOrder)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['goods_image'] = FileService::getFileUrl($item['goods_image']);
        }

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/8/28 15:13
     */
    public function count(): int
    {
        // 初始化搜索
        $this->setSearch();

        $field = [
            'g.image' => 'goods_image',
            'g.name' => 'goods_name',
            'g.max_price' => 'goods_max_price',
            'ba.end_time',
            'bg.activity_id' => 'bargain_min_price',
        ];

        $listsNoPage = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods g', 'g.id = bg.goods_id')
            ->leftJoin('bargain_activity ba', 'ba.id = bg.activity_id')
            ->distinct(true)
            ->field($field)
            ->where($this->searchWhere)
            ->order($this->sortOrder)
            ->select()
            ->toArray();

        return count($listsNoPage);
    }
}