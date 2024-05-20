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

use app\common\enum\AccountLogEnum;
use app\common\model\AccountLog;

/**
 * 账户流水列表
 * Class AccountLogLists
 * @package app\shopapi\lists
 */
class AccountLogLists extends BaseShopDataLists
{
    /**
     * @notes 设置搜索条件
     * @author Tab
     * @date 2021/8/9 17:31
     */
    public function setWhere()
    {
        // 指定用户
        $this->searchWhere[] = ['user_id', '=', $this->userId];

        // 可提现金额类型
        if(isset($this->params['type']) && $this->params['type'] == 'bw') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::getBwChangeType()];
        }

        // 不可提现金额类型-支出
        if(isset($this->params['type']) && $this->params['type'] == 'bnw_dec') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::BNW_DEC];
        }
        // 不可提现金额类型-收入
        if(isset($this->params['type']) && $this->params['type'] == 'bnw_inc') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::BNW_INC];
        }
        // 不可提现金额类型-支出 + 收入
        if(isset($this->params['type']) && $this->params['type'] == 'bnw') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::getBnwChangeType()];
        }
        // 积分
        if(isset($this->params['type']) && $this->params['type'] == 'integral') {
            $this->searchWhere[] = ['change_type', 'in', AccountLogEnum::getIntegralChangeType()];
        }
    }

    /**
     * @notes 账户流水列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/9 17:37
     */
    public function lists(): array
    {
        // 设置搜索条件
        $this->setWhere();

        $field = 'change_type,change_amount,action,create_time,remark';
        $lists = AccountLog::field($field)
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['type_desc'] = AccountLogEnum::getChangeTypeDesc($item['change_type']);
            $item['change_amount_desc'] = $item['action'] == AccountLogEnum::DEC ? '-' . $item['change_amount'] : '+' . $item['change_amount'];
        }

        return $lists;
    }

    /**
     * @notes 账户流水记录数
     * @return int
     * @author Tab
     * @date 2021/8/9 17:36
     */
    public function count(): int
    {
        // 设置搜索条件
        $this->setWhere();

        $count = AccountLog::where($this->searchWhere)->count();
        return $count;
    }
}