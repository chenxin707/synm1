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

namespace app\adminapi\lists;

use app\common\enum\AccountLogEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\AccountLog;

/**
 * 账记流水列表
 * Class AccountLogLists
 * @package app\adminapi\lists
 */
class AccountLogLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/22 18:40
     */
    public function setFileName(): string
    {
        return '余额明细';
    }

    /**
     * @notes 导出字段
     * @return array
     * @author Tab
     * @date 2021/9/22 18:40
     */
    public function setExcelFields(): array
    {
        return [
            'nickname' => '用户昵称',
            'sn' => '用户编号',
            'mobile' => '手机号码',
            'change_amount' => '变动金额',
            'left_amount' => '剩余金额',
            'change_type_desc' => '变动类型',
            'association_sn' => '来源单号',
            'create_time' => '记录时间',
        ];
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/8/12 15:32
     */
    public function setSearch(): array
    {
        return [
            '=' => ['u.sn', 'u.mobile', 'al.change_type'],
            '%like%' => ['u.nickname'],
            'between_time' => 'al.create_time'
        ];
    }

    /**
     * @notes 自定义搜索
     * @author Tab
     * @date 2021/8/25 20:39
     */
    public function queryWhere()
    {
        // 可提现余额类型
        if(isset($this->params['type']) && $this->params['type'] == 'bw') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::getBwChangeType()];
        }

        // 不可提现余额类型
        if(isset($this->params['type']) && $this->params['type'] == 'bnw') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::getBnwChangeType()];
        }

        // 积分类型
        if(isset($this->params['type']) && $this->params['type'] == 'integral') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::getIntegralChangeType()];
        }
    }

    /**
     * @notes 账户流水列表
     * @return array
     * @author Tab
     * @date 2021/8/12 15:32
     */
    public function lists(): array
    {
        $this->queryWhere();
        $field = 'u.nickname,u.sn,u.mobile,change_amount,left_amount,change_type,association_sn,al.create_time,al.action';
        $lists = AccountLog::withoutGlobalScope()
            ->aliasSid('al')
            ->alias('al')
            ->leftJoin('user u', 'u.id = al.user_id')
            ->field($field)
            ->where($this->searchWhere)
            ->order('al.id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['change_type_desc'] = AccountLogEnum::getChangeTypeDesc( $item['change_type']);
            $symbol = $item['action'] == AccountLogEnum::INC ? '+' : '-';
            // 积分、成长值转整型
            $item['change_amount'] = in_array($item['change_type'], AccountLogEnum::getIntegralChangeType()) ||  in_array($item['change_type'], AccountLogEnum::getGrowthChangeType()) ? (int)$item['change_amount'] : $item['change_amount'];
            $item['left_amount'] = in_array($item['change_type'], AccountLogEnum::getIntegralChangeType()) ||  in_array($item['change_type'], AccountLogEnum::getGrowthChangeType()) ? (int)$item['left_amount'] : $item['left_amount'];
            $item['change_amount'] = $symbol . $item['change_amount'];
        }

        return $lists;
    }

    /**
     * @notes 账户流水数量
     * @return int
     * @author Tab
     * @date 2021/8/12 15:33
     */
    public function count(): int
    {
        $this->queryWhere();
        $count = AccountLog::withoutGlobalScope()
            ->aliasSid('al')
            ->alias('al')
            ->leftJoin('user u', 'u.id = al.user_id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}