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
use app\common\model\DistributionLevel;

/**
 * 分销会员等级列表
 * Class DistributionLevelLists
 * @package app\adminapi\lists\distribution
 */
class DistributionLevelLists extends BaseAdminDataLists
{
    /**
     * @notes 分销会员等级列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/23 10:14
     */
    public function lists(): array
    {
        $lists = DistributionLevel::field('id,name,weights,self_ratio,first_ratio,second_ratio,is_default,weights as weights_desc,id as member_num')
            ->order('weights', 'asc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 分销会员等级数量
     * @return int
     * @author Tab
     * @date 2021/7/23 10:14
     */
    public function count(): int
    {
        return DistributionLevel::count();
    }
}