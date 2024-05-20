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

namespace app\adminapi\lists\lucky_draw;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\LuckyDrawEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\LuckyDraw;

/**
 * 幸运抽奖活动列表
 */
class LuckyDrawList extends BaseAdminDataLists implements ListsExtendInterface,ListsExcelInterface
{
    public function setExcelFields(): array
    {
        return [
            'name' => '活动名称',
            'start_time_desc' => '开始时间',
            'end_time_desc' => '结束时间',
            'join_num' => '抽奖人数',
            'win_num' => '中奖人数',
            'status_desc' => '活动状态',
            'create_time' => '创建时间',
        ];
    }

    public function setFileName(): string
    {
        return '幸运抽奖活动列表';
    }

    public function extend()
    {
        $all = LuckyDraw::count();
        $wait = LuckyDraw::where('status', LuckyDrawEnum::WAIT)->count();
        $ing = LuckyDraw::where('status', LuckyDrawEnum::ING)->count();
        $end = LuckyDraw::where('status', LuckyDrawEnum::END)->count();
        return [
            'all' => $all,
            'wait' => $wait,
            'ing' => $ing,
            'end' => $end,
        ];
    }

    /**
     * @notes 附加搜索条件
     * @author Tab
     * @date 2021/11/25 17:36
     */
    public function setSearch()
    {
        if (isset($this->params['status']) && trim($this->params['status']) != '') {
            $this->searchWhere[] = ['status', '=', $this->params['status']];
        }
        if (isset($this->params['name']) && !empty($this->params['name'])) {
            $this->searchWhere[] = ['name', 'like', '%' . trim($this->params['name']) . '%'];
        }
        if (isset($this->params['start_time']) && isset($this->params['end_time']) && !empty($this->params['start_time']) && !empty($this->params['end_time'])) {
            $this->searchWhere[] = ['start_time', '<=', strtotime($this->params['start_time'])];
            $this->searchWhere[] = ['end_time', '>=', strtotime($this->params['end_time'])];
        }
    }

    public function lists(): array
    {
        $this->setSearch();

        $field = [
            'id',
            'name',
            'start_time',
            'end_time',
            'status',
            'create_time',
        ];
        $lists = LuckyDraw::field($field)
            ->append(['start_time_desc', 'end_time_desc','join_num', 'win_num', 'status_desc'])
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }


    public function count(): int
    {
        $this->setSearch();
        $count = LuckyDraw::where($this->searchWhere)->count();
        return $count;
    }
}