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
use app\common\{
    model\User,
    enum\UserTerminalEnum,
    lists\ListsExtendInterface
};

/**
 * 粉丝列表
 * Class UserFansLists
 * @package app\adminapi\lists\user
 */
class UserFansLists extends BaseAdminDataLists implements ListsExtendInterface
{

    /**
     * @notes 搜索参数
     * @return array
     * @author cjhao
     * @date 2021/8/12 10:23
     */

    public function setSearch(): array
    {
        $user_id = $this->params['user_id'];
        $level = $this->params['level'] ?? 1;
        $where=  [];
        if(isset($this->params['keyword'])){
            $where[] = ['sn|nickname','like','%'.$this->params['keyword'].'%'];
        }
        switch ($level){
            case 1:
                $where[] = ['first_leader','=',$user_id];
                break;
            case 2:
                $where[] = ['second_leader','=',$user_id];
                break;
        }
        return $where;

    }

    public function extend(): array
    {
        $user_id = $this->params['user_id'];
        $statistics['first_count'] = User::where(['first_leader'=>$user_id])->count();
        $statistics['second_count'] = User::where(['second_leader'=>$user_id])->count();

        return $statistics;
    }

    /**
     * @notes 用户粉丝列表
     * @return array
     * @author cjhao
     * @date 2021/9/11 15:16
     */
    public function lists(): array
    {
        $list = User::with('user_level')
            ->where($this->setSearch())
            ->field('id,sn,nickname,level,first_leader,avatar,mobile,create_time,register_source')
            ->select()->toArray();

        $userIds = array_column($list,'first_leader');
        $userList = User::where(['id'=>$userIds])->column('nickname,avatar','id');
        foreach ($list as $key => $user){
            $list[$key]['leader_name'] = $userList[$user['first_leader']]['nickname'] ?? '';
            $list[$key]['leader_avatar'] = $userList[$user['first_leader']]['avatar'] ?? '';
            $list[$key]['register_source'] = UserTerminalEnum::getTermInalDesc($user['register_source']);
        }
        return $list;
    }

    /**
     * @notes 会员等级总数
     * @return int
     * @author cjhao
     * @date 2021/7/28 15:52
     */
    public function count(): int
    {
        return  User::where($this->setSearch())->count();
    }
}