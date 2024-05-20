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

namespace app\adminapi\lists\sign;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\SignLog;
use app\common\service\FileService;

/**
 * 签到记录列表
 * Class SignLists
 * @package app\adminapi\lists\sign
 */
class SignLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 导出字段
     * @return array
     * @author Tab
     * @date 2021/8/16 19:45
     */
    public function setExcelFields(): array
    {
       return [
           'nickname' => '用户昵称',
           'integral' => '每日签到奖励',
           'continuous_integral' => '连续签到奖励',
           'days' => '连续签到天数',
           'create_time' => '签到时间',
       ];
    }

    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/8/16 19:45
     */
    public function setFileName(): string
    {
        return '签到记录表';
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/8/16 19:33
     */
    public function setSearch(): array
    {
        return [
            '=' => ['u.sn', 'u.mobile'],
            '%like%' => ['u.nickname'],
            'between_time' => 'sl.create_time'
        ];
    }

    /**
     * @notes 签到记录列表
     * @return array
     * @author Tab
     * @date 2021/8/16 19:35
     */
    public function lists(): array
    {
        $field = 'sl.id,sl.integral,sl.continuous_integral,sl.days,sl.create_time';
        $field .= ',u.avatar,u.nickname';
        $lists = SignLog::withoutGlobalScope()
            ->aliasSid('sl')
            ->alias('sl')
            ->leftJoin('user u', 'u.id = sl.user_id')
            ->field($field)
            ->where($this->searchWhere)
            ->order('sl.id','desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();
        foreach($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
        }
        return $lists;
    }

    /**
     * @notes 签到记录数量
     * @return int
     * @author Tab
     * @date 2021/8/16 19:41
     */
    public function count(): int
    {
        $count = SignLog::withoutGlobalScope()
            ->aliasSid('sl')
            ->alias('sl')
            ->leftJoin('user u', 'u.id = sl.user_id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}