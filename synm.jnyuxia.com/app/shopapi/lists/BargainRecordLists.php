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

use app\common\lists\ListsSearchInterface;
use app\common\model\BargainInitiate;
use app\common\service\FileService;

/**
 * 砍价记录列表
 * Class BargainRecordLists
 * @package app\shopapi\lists
 */
class BargainRecordLists extends BaseShopDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/8/30 14:40
     */
    public function setSearch(): array
    {
       return [
            '=' => ['status']
       ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/18 11:53
     */
    public function attachSearch()
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
    }

    /**
     * @notes 列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/18 11:54
     */
    public function lists(): array
    {
        $this->attachSearch();

        $field = [
            'id' => 'initiate_id',
            'goods_snapshot',
            'bargain_snapshot',
            'current_price',
            'status',
            'status' => 'status_desc',
            'current_price',
            'create_time'
        ];

        $lists = BargainInitiate::field($field)
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['goods_image'] = get_image([
                $item['goods_snapshot']['item_image'], $item['goods_snapshot']['goods_image']
            ]);
            $item['goods_name'] = $item['goods_snapshot']['goods_name'];
            $item['origin_price'] = $item['goods_snapshot']['item_sell_price'];
            $item['tips'] = $this->tips($item['bargain_snapshot']['buy_condition']);
            // 去除不需要输出的参数
            unset($item['goods_snapshot']);
            unset($item['bargain_snapshot']);
        }

        return $lists;
    }

    /**
     * @notes  记录数
     * @return int
     * @author Tab
     * @date 2021/9/18 11:55
     */
    public function count(): int
    {
        $this->attachSearch();

        $count = BargainInitiate::where($this->searchWhere)->count();
        return $count;
    }

    /**
     * @notes 提示消息
     * @param $buyCondition
     * @return string
     * @author Tab
     * @date 2021/9/18 11:50
     */
    public function tips($buyCondition)
    {
        if ($buyCondition == 1) {
            return '砍到任意金额可直接购买';
        }
        if ($buyCondition == 2) {
            return '须砍到最低价才可支付购买';
        }
    }
}