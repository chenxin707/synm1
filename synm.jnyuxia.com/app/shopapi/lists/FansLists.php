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

use app\common\model\User;

/**
 * 粉丝列表
 * Class FansLists
 * @package app\shopapi\lists
 */
class FansLists extends BaseShopDataLists
{
    /**
     * @notes 粉丝列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/5 18:45
     */
    public function lists(): array
    {
        // 设置搜索
        $where = $this->setWhere();

        $lists = User::field('id,avatar,nickname,mobile, create_time, id as fans, id as order_amount,id as order_num')
            ->where($where)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        // 自定义排序
        $lists = $this->customizeOrder($lists);

        // 取指定页数据
        return array_slice($lists, $this->limitOffset, $this->limitLength);
    }

    /**
     * @notes 粉丝数量
     * @return int
     * @author Tab
     * @date 2021/8/5 18:45
     */
    public function count(): int
    {
        // 设置搜索
        $where = $this->setWhere();

        $count = User::field('avatar,nickname,mobile, create_time, id as fans, id as order_amount,id as order_num')
            ->where($where)
            ->count();

        return $count;
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/8/5 18:29
     */
    public function setWhere()
    {
        // 粉丝类型
        $type = $this->params['type'] ?? 'all';
        switch ($type) {
            // 一级
            case 'first':
                $where[] = ['first_leader', '=', $this->userId];
                break;
            // 二级
            case 'second':
                $where[] = ['second_leader', '=', $this->userId];
                break;
            // 默认
            default:
                $where[] = ['first_leader|second_leader', '=', $this->userId];
        }
        // 关键字搜索
        if (isset($this->params['keyword']) && !empty($this->params['keyword'])) {
            $where[] = ['nickname|mobile', 'like', '%' . $this->params['keyword'] .'%'];
        }

        return $where;
    }

    /**
     * @notes 自定义排序
     * @return array
     * @author Tab
     * @date 2021/8/5 18:36
     */
    public function customizeOrder($lists) {
        foreach($lists as $key => $row) {
            $fans[$key] = $row['fans'];
            $orderAmount[$key] = $row['order_amount'];
            $orderNum[$key] = $row['order_num'];
        }

        // 排序 SORT_DESC = 3 倒序 , SORT_ASC = 4 升序
        // 粉丝数
        if(isset($this->params['fans']) && isset($fans)) {
            array_multisort($fans, intval($this->params['fans']), $lists);
        }
        // 已支付订单总金额
        if(isset($this->params['order_amount']) && isset($fans)) {
            array_multisort($orderAmount, intval($this->params['order_amount']), $lists);
        }
        // 已支付订单数量
        if(isset($this->params['order_num']) && isset($fans)) {
            array_multisort($orderNum, intval($this->params['order_num']), $lists);
        }

        return $lists;
    }
}