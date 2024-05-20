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

namespace app\adminapi\lists\distribution;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\model\User;

/**
 * 下级列表
 * Class FansLists
 * @package app\adminapi\lists\distribution
 */
class FansLists extends BaseAdminDataLists implements ListsExtendInterface,ListsExcelInterface
{
    /**
     * @notes 导出字段
     * @return array
     * @author Tab
     * @date 2021/9/22 14:44
     */
    public function setExcelFields(): array
    {
        return [
            'user_info' => '用户信息',
            'level_name' => '分销等级',
            'earnings' => '已入账佣金',
            'unreturned_commission' => '待结算佣金',
            'first_leader_info' => '上级分销商',
            'is_freeze_desc' => '分销状态',
            'distribution_time' => '成为分销商时间',
        ];
    }

    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/22 14:44
     */
    public function setFileName(): string
    {
        return '下级列表';
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/9/14 20:10
     */
    public function setSearch(): array
    {
        // 一级粉丝
        if (isset($this->params['level']) && $this->params['level'] == 1) {
            $this->searchWhere[] = ['u.first_leader', '=', $this->params['user_id']];
        }

        // 二级粉丝
        if (isset($this->params['level']) && $this->params['level'] == 2) {
            $this->searchWhere[] = ['u.second_leader', '=', $this->params['user_id']];
        }

        // 用户信息
        if (isset($this->params['user_info']) && !empty($this->params['user_info'])) {
            $this->searchWhere[] = ['u.nickname|u.sn', 'like', '%'.$this->params['user_info'].'%'];
        }

        return $this->searchWhere;
    }

    /**
     * @notes 统计信息
     * @return array
     * @author Tab
     * @date 2021/9/14 20:23
     */
    public function extend()
    {
        $one = User::getLevelOneFans($this->params['user_id']);
        $two = User::getLevelTwoFans($this->params['user_id']);

        return [
            'one' => $one,
            'two' => $two,
        ];
    }

    /**
     * @notes 列表
     * @return array
     * @author Tab
     * @date 2021/9/14 20:23
     */
    public function lists(): array
    {
        $this->setSearch();

        $field = [
            'u.sn',
            'u.avatar',
            'u.nickname',
            'u.first_leader',
            'd.user_id',
            'd.level_id',
            'd.is_freeze',
            'd.distribution_time',
        ];
        $lists = User::withoutGlobalScope()
            ->aliasSid('u')
            ->alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->field($field)
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['level_name'] = DistributionLevel::getLevelName($item['level_id']);
            $item['earnings'] = DistributionOrderGoods::getEarnings($item['user_id']);
            $item['unreturned_commission'] = DistributionOrderGoods::getUnReturnedCommission($item['user_id']);
            $item['first_leader_info'] = User::getFirstLeader($item['first_leader'])['name'];
            $item['distribution_time'] = empty($item['distribution_time']) ? '' : date('Y-m-d H:i:s', $item['distribution_time']);
            $item['user_info'] = $item['nickname'] . '(' . $item['sn'] . ')';
            $item['is_freeze_desc'] = $item['is_freeze'] ? '冻结' : '正常';
        }

        return $lists;
    }


    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/9/14 20:22
     */
    public function count(): int
    {
        $this->setSearch();

        $count = User::withoutGlobalScope()
            ->aliasSid('u')
            ->alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}