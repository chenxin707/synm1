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
namespace app\kefuapi\lists;


use app\common\lists\ListsSearchInterface;
use app\common\model\Order;

/**
 * 订单列表(客服页面中-用户订单列表)
 * Class ChatOrderLists
 * @package app\kefuapi\lists
 */
class ChatOrderLists extends BaseKefuDataLists implements ListsSearchInterface
{

    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 段誉
     * @date 2022/3/14 14:50
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['sn'],
            '=' => ['user_id'],
        ];
    }


    /**
     * @notes 获取用户订单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/14 14:50
     */
    public function lists(): array
    {
        $goodFields = [
            'order_id', 'goods_id', 'item_id', 'goods_snap',
            'goods_name', 'goods_price', 'goods_num'
        ];

        $lists = Order::with([
            'order_goods' => function ($query) use ($goodFields) {
                $query->field($goodFields)
                    ->append(['goods_image', 'spec_value_str'])
                    ->hidden(['goods_snap']);
            }])
            ->field(['id', 'sn', 'order_status', 'order_amount','order_type', 'create_time'])
            ->where($this->searchWhere)
            ->append(['order_status_desc', 'order_type_desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id desc')
            ->select()->toArray();

        return $lists;
    }

    /**
     * @notes 记录数量
     * @return int
     * @author 段誉
     * @date 2022/3/14 14:51
     */
    public function count(): int
    {
        return Order::where($this->searchWhere)->count();
    }

}