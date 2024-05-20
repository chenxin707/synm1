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
use app\common\model\UserTransfer;

/**
 * 转账记录列表
 * Class TransferLists
 * @package app\shopapi\lists
 */
class TransferLists extends BaseShopDataLists
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author Tab
     * @date 2021/8/12 11:51
     */
    public function setSearch()
    {
        if(isset($this->params['type']) && $this->params['type'] == 'in') {
            // 转入
            $this->searchWhere[] = ['transfer_in', '=', $this->userId];
        }else if (isset($this->params['type']) && $this->params['type'] == 'out') {
            // 转出
            $this->searchWhere[] = ['transfer_out', '=', $this->userId];
        }else{
            // 转入 + 转出
            $this->searchWhere[] = ['transfer_out|transfer_in', '=', $this->userId];
        }
    }

    /**
     * @notes 转账列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/12 14:14
     */
    public function lists(): array
    {
        // 设置搜索
        $this->setSearch();

        $lists = UserTransfer::field('transfer_in,transfer_out,money,money as money_desc,create_time')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $this->format($item);
        }

        return $lists;
    }

    /**
     * @notes 转账记录数量
     * @return int
     * @author Tab
     * @date 2021/8/12 14:14
     */
    public function count(): int
    {
        // 设置搜索
        $this->setSearch();

        $count = UserTransfer::where($this->searchWhere)->count();

        return $count;
    }

    /**
     * @notes 格式化数据
     * @param $item
     * @author Tab
     * @date 2021/8/12 14:12
     */
    public function format(&$item) {
        // 转入
        if($item['transfer_in'] == $this->userId) {
            $item['money_desc'] = '+' . $item['money'];
            $user = User::findOrEmpty($item['transfer_out']);
        }
        // 转出
        if($item['transfer_out'] == $this->userId) {
            $item['money_desc'] = '-' . $item['money'];
            $user = User::findOrEmpty($item['transfer_in']);
        }
        $item['nickname'] = '转账-' . $user->nickname;
        $item['avatr'] = $user->avatar;
        $item['sn'] = $user->sn;
    }
}
