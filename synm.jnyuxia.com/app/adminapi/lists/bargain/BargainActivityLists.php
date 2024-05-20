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

namespace app\adminapi\lists\bargain;

use app\adminapi\lists\BaseAdminDataLists;

use app\common\enum\BargainEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsExtendInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\BargainActivity;
use app\common\model\BargainGoods;
use app\common\model\BargainInitiate;
use app\common\model\Order;

/**
 * 砍价活动列表
 * Class BargainActivityLists
 * @package app\adminapi\lists\bargain
 */
class BargainActivityLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExtendInterface
{
    /**
     * @notes 统计信息
     * @return mixed|void
     * @author Tab
     * @date 2021/8/27 18:19
     */
    public function extend()
    {
        $all = BargainActivity::count();
        $wait = BargainActivity::where('status', BargainEnum::ACTIVITY_STATUS_WAIT)->count();
        $ing = BargainActivity::where('status', BargainEnum::ACTIVITY_STATUS_ING)->count();
        $end = BargainActivity::where('status', BargainEnum::ACTIVITY_STATUS_END)->count();

        return [
            'all' => $all,
            'wait' => $wait,
            'ing' => $ing,
            'end' => $end
        ];
    }

    /**
     * @notes 设置搜索
     * @return \string[][]
     * @author Tab
     * @date 2021/8/27 18:19
     */
    public function setSearch(): array
    {
        return [
            '=' => ['status']
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/24 11:52
     */
    public function attachSearch()
    {
        // 活动信息
        if (isset($this->params['activity_info']) && !empty($this->params['activity_info'])) {
            $this->searchWhere[] = ['sn|name', 'like', '%'. $this->params['activity_info'] .'%'];
        }
    }

    /**
     * @notes 列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/27 18:26
     */
    public function lists() : array
    {
        $this->attachSearch();
        $field = [
            'id',
            'sn',
            'name',
            'start_time' => 'start_time_desc',
            'end_time' => 'end_time_desc',
            'visited',
            'status',
            'status' => 'status_desc',
            'create_time',
        ];
        $lists = BargainActivity::field($field)
            ->where($this->searchWhere)
            ->order('id','desc')
            ->withSearch(['activity_time', 'goods_info'], $this->params)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['goods_count'] = $this->goodsCount($item);
            $orderData= $this->orderData($item);
            $item['total_amount'] = $orderData['total_amount'];
            $item['total_num'] = $orderData['total_num'];
            $item['order_count'] = $orderData['order_count'];
        }

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/8/27 18:25
     */
    public function  count() : int
    {
        $this->attachSearch();
        $count = BargainActivity::where($this->searchWhere)
            ->withSearch(['activity_time', 'goods_name', 'goods_code'], $this->params)
            ->count();
        return $count;
    }

    /**
     * @notes 活动商品数量
     * @param $item
     * @return int
     * @author Tab
     * @date 2021/9/24 9:10
     */
    public function goodsCount($item)
    {
        $goodsIds = BargainGoods::distinct(true)
            ->field('goods_id')
            ->where('activity_id', $item['id'])
            ->select()
            ->toArray();

        return count($goodsIds);
    }

    /**
     * @notes 订单数据
     * @param $item
     * @author Tab
     * @date 2021/9/24 9:22
     */
    public function orderData($item)
    {
        // 活动对应的订单id
        $orderIds = BargainInitiate::where('activity_id', $item['id'])->whereNotNull('order_id')->column('order_id');
        // 成交订单数
        $orderCount = Order::where([
            ['order_type', '=' ,OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->count();
        // 销量
        $totalNum = Order::where([
            ['order_type', '=' ,OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->sum('total_num');
        // 销售额
        $totalAmount = Order::where([
            ['order_type', '=' ,OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->sum('order_amount');

        return [
            'total_amount' => $totalAmount,
            'total_num' => $totalNum,
            'order_count' => $orderCount,
        ];
    }
}