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
use app\common\enum\TeamEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\TeamActivity;
use app\common\model\TeamGoods;
use app\common\model\TeamGoodsItem;

class TeamLists  extends BaseAdminDataLists implements ListsExtendInterface, ListsExcelInterface
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
            'sn'    => '拼团编号',
            'name'  => '拼团名称',
            'goods_num'  => '活动商品',
            'activity_time'  => '活动时间',
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
        return '拼团列表';
    }

    /**
     * @notes 拼团活动数量统计
     * @return mixed
     * @author 张无忌
     * @date 2021/7/29 18:09
     */
    public function extend()
    {
        $model = new TeamActivity();
        $detail['all'] = $model->count();
        $detail['not'] = $model->where(['status' => TeamEnum::TEAM_STATUS_NOT])->count();
        $detail['conduct'] = $model->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])->count();
        $detail['end'] = $model->where(['status' => TeamEnum::TEAM_STATUS_END])->count();
        return $detail;
    }

    /**
     * @notes 拼团活动搜索条件
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:08
     */
    public function queryWhere()
    {
        $where = [];
        if (!empty($this->params['status']) and $this->params['status']) {
            $where[] = ['TA.status', '=', $this->params['status']];
        }
        if (!empty($this->params['activity']) and $this->params['activity']) {
            $where[] = ['TA.name|TA.sn', 'like', '%' . $this->params['activity'] . '%'];
        }
        if (!empty($this->params['goods']) and $this->params['goods']) {
            $where[] = ['TG.goods_snap->name|TG.goods_snap->code', 'like', '%' . $this->params['goods'] . '%'];
        }
        if (!empty($this->params['start_time']) and $this->params['start_time']) {
            $where[] = ['TA.start_time', '>=', strtotime($this->params['start_time'])];
        }
        if (!empty($this->params['end_time']) and $this->params['end_time']) {
            $where[] = ['TA.end_time', '<=', strtotime($this->params['end_time'])];
        }

        return $where;
    }

    /**
     * @notes 获取拼团活动列表
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/29 18:09
     */
    public function lists(): array
    {
        $lists = (new TeamActivity())
            ->duokaiWithoutGlobalScope()
            ->aliasSid('TA')
            ->alias('TA')
            ->field('TA.id,TA.sn,TA.name,TA.start_time,TA.end_time,TA.status,TA.create_time')
            ->join('team_goods TG', 'TG.team_id = TA.id')
            ->group('TA.id')
            ->order('TA.id desc')
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['status_text']   = TeamEnum::getActivityStatusDesc($item['status']);
            $item['activity_time'] = date('Y-m-d H:i:s', $item['start_time']) . ' ~ ' . date('Y-m-d H:i:s', $item['end_time']);
            $item['goods_num']     = TeamGoods::where(['team_id' => $item['id']])->count() . '件';
            $item['browse_volume'] = TeamGoods::where(['team_id' => $item['id']])->sum('browse_volume');
            $item['sales_amount']  = TeamGoodsItem::where(['team_id' => $item['id']])->sum('sales_amount');
            $item['sales_volume']  = TeamGoodsItem::where(['team_id' => $item['id']])->sum('sales_volume');
            $item['closing_order'] = TeamGoodsItem::where(['team_id' => $item['id']])->sum('closing_order');

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
        return (new TeamActivity())
            ->duokaiWithoutGlobalScope()
            ->aliasSid('TA')
            ->alias('TA')
            ->join('team_goods TG', 'TG.team_id = TA.id')
            ->where($this->queryWhere())->count();
    }
}