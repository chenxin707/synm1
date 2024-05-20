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
use app\common\model\User;
use app\common\model\UserLevel;

/**
 * 会员等级列表
 * Class UserLevelLists
 * @package app\adminapi\lists\user
 */
class UserLevelLists extends BaseAdminDataLists
{

    /**
     * @notes 会员等级列表
     * @return array
     * @author cjhao
     * @date 2021/7/28 15:52
     */
    public function lists(): array
    {
        $lists = UserLevel::field('id,name,rank,image,discount')
            ->limit($this->limitOffset, $this->limitLength)
            ->order('rank', 'asc')
            ->select();

        $leveCount = User::group('level')->column('count(id) as num', 'level');

        foreach ($lists as  $level) {
            if($level->discount > 0){
                $level->discount = floatval($level->discount).'折';
            }else{
                $level->discount = '无';
            }
            $level->num = $leveCount[$level['id']] ?? 0;
        }

        return $lists->toArray();
    }

    /**
     * @notes 会员等级总数
     * @return int
     * @author cjhao
     * @date 2021/7/28 15:52
     */
    public function count(): int
    {
        return UserLevel::count();
    }
}