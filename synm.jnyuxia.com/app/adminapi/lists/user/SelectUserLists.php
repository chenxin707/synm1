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
use app\common\lists\ListsSearchInterface;
use app\common\model\User;

/**
 * 选择用户列表
 * Class SelectUserLists
 * @package app\adminapi\lists\user
 */
class SelectUserLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索
     * @return \string[][]
     * @author Tab
     * @date 2021/9/14 11:18
     */
    public function setSearch(): array
    {
        return [
            '=' => ['d.is_distribution'],
            '%like%' => ['u.sn', 'u.nickname']
        ];
    }

    /**
     * @notes 搜索
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/14 11:18
     */
    public function lists(): array
    {
        $field = [
            'u.id',
            'u.sn',
            'u.nickname',
            'u.mobile',
            'u.avatar',
            'u.create_time',
            'u.user_money',
            'd.is_distribution'
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

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/9/14 11:19
     */
    public function count(): int
    {
        $count = User::withoutGlobalScope()
            ->aliasSid('u')
            ->alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}