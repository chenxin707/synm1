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

namespace app\adminapi\lists\marketing;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\SeckillEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\SeckillActivity;
use app\common\model\SeckillGoods;
use app\common\model\SeckillGoodsItem;

class SeckillLists extends BaseAdminDataLists implements ListsExtendInterface, ListsExcelInterface
{

    /**
     * @notes 设置导出字段
     * @return array
     * @author 张无忌
     * @date 2021/7/31 16:28
     */
    public function setExcelFields(): array
    {
        return [
            'sn'    => '秒杀编号',
            'name'  => '秒杀名称',
            'goods_num'  => '活动商品',
            'activity_time' => '活动时间',
            'browse_volume'  => '浏览量',
            'sales_amount'  => '销售金额',
            'sales_volume'  => '销售量',
            'closing_order'  => '成交订单数',
            'status_text'  => '活动状态',
            'create_time' => '创建时间'
        ];
    }

    /**
     * @notes 设置导出文件名
     * @return string
     * @author 张无忌
     * @date 2021/7/31 16:28
     */
    public function setFileName(): string
    {
        return '秒杀列表';
    }

    /**
     * @notes 秒杀活动搜索条件
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:08
     */
    public function queryWhere()
    {
        $where = [];
        if (!empty($this->params['status']) and $this->params['status']) {
            $where[] = ['SA.status', '=', $this->params['status']];
        }
        if (!empty($this->params['activity']) and $this->params['activity']) {
            $where[] = ['SA.name|SA.sn', 'like', '%' . $this->params['activity'] . '%'];
        }
        if (!empty($this->params['start_time']) and $this->params['start_time']) {
            $where[] = ['SA.start_time', '>=', strtotime($this->params['start_time'])];
        }
        if (!empty($this->params['end_time']) and $this->params['end_time']) {
            $where[] = ['SA.end_time', '<=', strtotime($this->params['end_time'])];
        }

        return $where;
    }

    /**
     * @notes 秒杀活动数量统计
     * @return mixed
     * @author 张无忌
     * @date 2021/7/29 18:09
     */
    public function extend()
    {
        $detail['all']     = (new SeckillActivity())->duokaiWithoutGlobalScope()->alias('SA')->aliasSid('SA')->withSearch(['goods'], $this->params)->count();
        $detail['not']     = (new SeckillActivity())->duokaiWithoutGlobalScope()->alias('SA')->aliasSid('SA')->where(['status' => SeckillEnum::SECKILL_STATUS_NOT])->withSearch(['goods'], $this->params)->count();
        $detail['conduct'] = (new SeckillActivity())->duokaiWithoutGlobalScope()->alias('SA')->aliasSid('SA')->where(['status' => SeckillEnum::SECKILL_STATUS_CONDUCT])->withSearch(['goods'], $this->params)->count();
        $detail['end']     = (new SeckillActivity())->duokaiWithoutGlobalScope()->alias('SA')->aliasSid('SA')->where(['status' => SeckillEnum::SECKILL_STATUS_END])->withSearch(['goods'], $this->params)->count();
        return $detail;
    }

    /**
     * @notes 获取秒杀活动列表
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/29 18:09
     */
    public function lists(): array
    {

        $lists = (new SeckillActivity())->duokaiWithoutGlobalScope()
            ->aliasSid('SA')
            ->alias('SA')
            ->field(['SA.id,SA.sn,SA.name,SA.start_time,SA.end_time,SA.status,SA.create_time'])
            ->where($this->queryWhere())
            ->withSearch(['goods'], $this->params)
            ->order('SA.id desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {

            $item['status_text']   = SeckillEnum::getSeckillStatusDesc($item['status']);
            $item['activity_time'] = date('Y-m-d H:i:s', $item['start_time']) . ' ~ ' . date('Y-m-d H:i:s', $item['end_time']);
            $item['goods_num']     = SeckillGoods::where(['seckill_id' => $item['id']])->count() . '件';
            $item['browse_volume'] = SeckillGoods::where(['seckill_id' => $item['id']])->sum('browse_volume');
            $item['sales_amount']  = SeckillGoodsItem::where(['seckill_id' => $item['id']])->sum('sales_amount');
            $item['sales_volume']  = SeckillGoodsItem::where(['seckill_id' => $item['id']])->sum('sales_volume');
            $item['closing_order'] = SeckillGoodsItem::where(['seckill_id' => $item['id']])->sum('closing_order');

            unset($item['start_time']);
            unset($item['end_time']);
        }

        return $lists;
    }

    /**
     * @notes 获取秒杀活动数量
     * @return int
     * @author 张无忌
     * @date 2021/7/29 18:10
     */
    public function count(): int
    {
        $count = (new SeckillActivity())->duokaiWithoutGlobalScope()
            ->aliasSid('SA')
            ->alias('SA')
            ->field(['SA.id,SA.sn,SA.name,SA.start_time,SA.end_time,SA.status,SA.create_time'])
            ->where($this->queryWhere())
            ->withSearch(['goods'], $this->params)
            ->count();

        return $count;
    }
}