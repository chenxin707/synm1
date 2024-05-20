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

use app\common\lists\ListsSearchInterface;
use app\common\model\WithdrawApply;

/**
 * 提现申请列表
 * Class WithdrawLists
 * @package app\shopapi\lists
 */
class WithdrawLists extends BaseShopDataLists
{
    /**
     * @notes 提现申请列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/6 18:54
     */
    public function lists(): array
    {
        $field = 'id,type,type as type_desc,money,create_time,status,status as status_desc,audit_remark';
        $lists = WithdrawApply::field($field)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
//            ->where($this->searchWhere)
            ->where(['user_id'=>$this->userId])
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 提现申请记录数
     * @return int
     * @author Tab
     * @date 2021/8/6 18:54
     */
    public function count(): int
    {
        $count = WithdrawApply::where(['user_id'=>$this->userId])
//            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}