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

namespace app\adminapi\lists\withdraw;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\WithdrawEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\UserLevel;
use app\common\model\WithdrawApply;
use app\common\service\FileService;

/**
 * 提现列表
 * Class WithdrawLists
 * @package app\adminapi\lists\withdraw
 */
class WithdrawLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExtendInterface,ListsExcelInterface
{
    /**
     * @notes 设置导出字段
     * @return array
     * @author Tab
     * @date 2021/8/9 16:24
     */
    public function setExcelFields(): array
    {
        return [
            'sn' => '提现单号',
            'nickname' => '用户昵称',
            'money' => '提现金额',
            'type_desc' => '提现方式',
            'status_desc' => '提现状态',
            'apply_remark' => '提现说明',
            'create_time' => '提现时间',
        ];
    }

    /**
     * @notes 设置导出的表名
     * @return string
     * @author Tab
     * @date 2021/8/9 16:24
     */
    public function setFileName(): string
    {
        return '提现申请表';
    }

    /**
     * @notes 统计信息
     * @return array
     * @author Tab
     * @date 2021/8/6 20:12
     */
    public function extend()
    {
        $all = WithdrawApply::count();
        $statusWait = WithdrawApply::where('status', WithdrawEnum::STATUS_WAIT)->count();
        $statusIng = WithdrawApply::where('status', WithdrawEnum::STATUS_ING)->count();
        $statusSuccess = WithdrawApply::where('status', WithdrawEnum::STATUS_SUCCESS)->count();
        $statusFail = WithdrawApply::where('status', WithdrawEnum::STATUS_FAIL)->count();
        return [
            'all' => $all,
            'status_wait' => $statusWait,
            'status_ing' => $statusIng,
            'status_success' => $statusSuccess,
            'status_fail' => $statusFail,
        ];
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/8/6 20:12
     */
    public function setSearch(): array
    {
        return [
            '=' =>['wa.type','wa.sn', 'wa.status'],
            'between_time' => 'wa.create_time'
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/17 16:26
     */
    public function attachSearch()
    {
        // 用户信息
        if(isset($this->params['user_info']) && !empty($this->params['user_info'])) {
            $this->searchWhere[] = ['u.sn|u.nickname|u.mobile', 'like', '%'.$this->params['user_info'].'%'];
        }
    }

    /**
     * @notes 提现列表
     * @return array
     * @author Tab
     * @date 2021/8/6 20:12
     */
    public function lists(): array
    {
        $this->attachSearch();

        $field = 'wa.id,wa.sn,wa.money,wa.type,wa.status,wa.apply_remark,wa.create_time,wa.type as type_desc,wa.status as status_desc,wa.handling_fee,wa.left_money';
        $field .= ',u.avatar,u.sn as user_sn,u.nickname,u.level,u.mobile';
        $lists = WithdrawApply::withoutGlobalScope()
            ->aliasSid('wa')
            ->alias('wa')
            ->leftJoin('user u', 'u.id = wa.user_id')
            ->field($field)
            ->where($this->searchWhere)
            ->order('wa.id', 'desc')
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
            $item['level_name'] = UserLevel::getLevelName($item['level']);
        }

        return $lists;
    }

    /**
     * @notes 提现记录数
     * @return int
     * @author Tab
     * @date 2021/8/6 20:12
     */
    public function count(): int
    {
        $this->attachSearch();

        $count = WithdrawApply::withoutGlobalScope()
            ->aliasSid('wa')
            ->alias('wa')
            ->leftJoin('user u', 'u.id = wa.user_id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}