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

namespace app\adminapi\lists\free_shipping;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\FreeShippingEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\FreeShipping;


/**
 * 包邮活动列表
 */
class FreeShippingLists extends BaseAdminDataLists implements ListsExtendInterface
{
    public function extend()
    {
        $all = FreeShipping::count();
        $wait = FreeShipping::where('status', FreeShippingEnum::WAIT)->count();
        $ing = FreeShipping::where('status', FreeShippingEnum::ING)->count();
        $end = FreeShipping::where('status', FreeShippingEnum::END)->count();
        return [
            'all' => $all,
            'wait' => $wait,
            'ing' => $ing,
            'end' => $end,
        ];
    }

    /**
     * @notes 附加搜索条件
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

    /**
     * @notes 列表
     */
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
        $lists = FreeShipping::field($field)
            ->append(['order_num', 'order_amount','btn'])
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }


    /**
     * @notes 总记录数
     */
    public function count(): int
    {
        $this->setSearch();
        $count = FreeShipping::where($this->searchWhere)->count();
        return $count;
    }
}
