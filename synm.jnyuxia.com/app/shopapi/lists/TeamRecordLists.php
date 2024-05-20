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


use app\common\enum\TeamEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\TeamJoin;
use app\common\service\FileService;

class TeamRecordLists extends BaseShopDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索
     * @return array
     * @author 张无忌
     * @date 2021/8/3 15:13
     */
    public function setSearch(): array
    {
        return [
            '=' => ['TJ.status']
        ];
    }

    /**
     * @notes 拼团记录列表
     * @return array
     * @author 张无忌
     * @date 2021/8/3 15:11
     */
    public function lists(): array
    {
        $lists = (new TeamJoin())->duokaiWithoutGlobalScope()
            ->aliasSid('TJ')
            ->alias('TJ')
            ->field(['TJ.*,O.order_amount,O.pay_status'])
            ->where(['TJ.user_id'=>$this->userId])
            ->where($this->searchWhere)
            ->join('Order O', 'O.id = TJ.order_id')
            ->order('TJ.id desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()->toArray();

        $data = [];
        foreach ($lists as &$item) {
            $teamSnap  = json_decode($item['team_snap'], true);
            $goodsSnap = json_decode($item['goods_snap'], true);
            $data[] = [
                'id'         => $item['id'],
                'team_id'    => $item['team_id'],
                'found_id'   => $item['found_id'],
                'order_id'   => $item['order_id'],
                'people_num' => $teamSnap['people_num'],
                'name'       => $goodsSnap['name'],
                'image'      => FileService::getFileUrl($goodsSnap['image']),
                'sell_price' => $goodsSnap['sell_price'],
                'count'      => $goodsSnap['count'],
                'spec_value_str' => $goodsSnap['spec_value_str'],
                'order_amount'   => $item['order_amount'],
                'status'         => $item['status'],
                'identity'       => $item['identity'],
                'invalid_time'   => $item['invalid_time'],
                'surplus_time'   => $item['invalid_time'] - time(),
                'identity_text'  => $item['identity'] == 1 ? '团长' : '团员',
                'status_text'    => TeamEnum::getStatusDesc($item['status']),
                'pay_status'     => $item['pay_status'],
                'team_end_time'  => $item['team_end_time'] ? date('Y-m-d H:i:s', $item['team_end_time']) : 0
            ];
        }

        return $data;
    }

    /**
     * @notes 总数量
     * @return int
     * @author 张无忌
     * @date 2021/8/3 15:12
     */
    public function count(): int
    {
        return (new TeamJoin())->duokaiWithoutGlobalScope()
            ->aliasSid('TJ')
            ->alias('TJ')
            ->field(['TJ.*,O.order_amount'])
            ->where($this->searchWhere)
            ->where(['TJ.user_id'=>$this->userId])
            ->join('Order O', 'O.id = TJ.order_id')
            ->count();
    }
}