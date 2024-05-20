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
use app\common\lists\ListsExtendInterface;
use app\common\model\User;

/**
 * 用户列表
 * Class UserLists
 * @package app\adminapi\lists\user
 */
class UserLists extends BaseAdminDataLists implements ListsExcelInterface
{

    /**
     * @notes 搜索参数
     * @return array
     * @author cjhao
     * @date 2021/8/12 10:23
     */
    public function setSearch(): array
    {
        return  array_intersect(array_keys($this->params),['disable','keyword','level','label_id','min_amount','max_amount','source','create_start_time','create_end_time']);
    }


    /**
     * @notes 搜索列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/12 9:46
     */
    public function lists(): array
    {
        $lists = User::withSearch($this->setSearch(), $this->params)
            ->with(['user_level'])
            ->limit($this->limitOffset, $this->limitLength)
            ->field('id,sn,nickname,avatar,mobile,level,user_money+user_earnings as total_user_money,user_integral,total_order_amount,login_time,create_time,disable')
            ->order('id desc')
            ->withAttr('user_money',function ($value){
                return '¥'.$value;
            })
            ->withAttr('total_order_amount',function ($value){
                return '¥'.$value;
            })
            ->select()
            ->toArray();
        return $lists;
    }

    /**
     * @notes 统计数据
     * @return int
     * @author cjhao
     * @date 2021/8/12 10:23
     */
    public function count(): int
    {
        return User::withSearch($this->setSearch(), $this->params)->count();
    }

    /**
     * @notes 设置excel表名
     * @return string
     * @author cjhao
     * @date 2021/9/23 16:58
     */
    public function setFileName(): string
    {
        return '用户列表';
    }

    /**
     * @notes 设置导出字段
     * @return array
     * @author cjhao
     * @date 2021/9/23 17:03
     */
    public function setExcelFields(): array
    {
        return [
            'sn'                    => '用户编号',
            'nickname'              => '用户昵称',
            'name'                  => '用户等级',
            'mobile'                => '手机号码',
            'total_user_money'      => '钱包余额',
            'total_order_amount'    => '消费金额',
            'login_time'            => '最后登录时间',
            'create_time'           => '注册时间',
        ];
    }
}