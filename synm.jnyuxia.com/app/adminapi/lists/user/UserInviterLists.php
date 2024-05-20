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
namespace app\adminapi\lists\user;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\User;
use app\common\model\UserLevel;

/**
 * 我邀请的人
 * Class UserInviterLists
 * @package app\adminapi\lists\user
 */
class UserInviterLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/22 17:49
     */
    public function setFileName(): string
    {
        return '邀请列表';
    }

    /**
     * @notes 导出字段
     * @return array
     * @author Tab
     * @date 2021/9/22 17:50
     */
    public function setExcelFields(): array
    {
        return [
            'sn' => '用户编号',
            'nickname' => '用户昵称',
            'level_name' => '用户等级',
            'mobile' => '手机号码',
            'user_money' => '钱包金额',
            'total_order_amount' => '消费金额',
            'login_time' => '最近登录时间',
            'create_time' => '注册时间',
        ];
    }

    /**
     * @notes 设置搜索
     * @return \string[][]
     * @author Tab
     * @date 2021/9/22 17:48
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['sn', 'nickname']
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/22 18:15
     */
    public function attachSearch()
    {
        $this->searchWhere[] = ['inviter_id', '=', $this->params['user_id']];
    }

    /**
     * @notes 列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/14 10:08
     */
    public function lists(): array
    {
        $this->attachSearch();

        $field = [
            'sn',
            'nickname',
            'avatar',
            'level',
            'mobile',
            'user_money',
            'total_order_amount',
            'login_time',
            'create_time',
        ];
        $lists = User::field($field)
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['level_name'] = UserLevel::getLevelName($item['level']);
        }

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/9/14 10:07
     */
    public function count(): int
    {
        $this->attachSearch();

        $field = [
            'sn',
            'nickname',
            'avatar',
            'level',
            'mobile',
            'user_money',
            'total_order_amount',
            'login_time',
            'create_time',
        ];
        $count = User::field($field)
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}