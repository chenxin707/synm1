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


use app\common\enum\IntegralOrderEnum;
use app\common\model\IntegralOrder;
use app\common\service\FileService;

/**
 * 积分订单列表
 * Class IntegralGoodsLists
 * @package app\shopapi\lists
 */
class IntegralOrderLists extends BaseShopDataLists
{

    /**
     * @notes 搜索条件
     * @return array
     * @author 段誉
     * @date 2022/3/31 14:41
     */
    public function setSearch(): array
    {
        $where[] = ['user_id', '=', $this->userId];
        if (isset($this->params['type']) && $this->params['type'] != '') {
            $where[] = ['order_status', '=', $this->params['type']];
        }
        return $where;
    }

    /**
     * @notes 积分商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/31 9:40
     */
    public function lists(): array
    {
        $field = [
            'id', 'sn', 'order_status', 'pay_status', 'express_status',
            'express_price', 'delivery_way', 'order_amount', 'total_num',
            'order_integral', 'goods_snap', 'create_time', 'refund_status'
        ];

        $lists = IntegralOrder::where($this->setSearch())
            ->field($field)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id desc')
            ->append(['btns', 'order_status_desc'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            $goods = $item['goods_snap'];
            $item['goods'] = [
                'image' => FileService::getFileUrl($goods['image']),
                'name' => $goods['name'],
                'need_integral' => $goods['need_integral'],
                'need_money' => $goods['need_money'],
                'exchange_way' => $goods['exchange_way'],
            ];
            unset($item['goods_snap']);
        }

        return $lists;
    }


    /**
     * @notes 积分商品数量
     * @return int
     * @author 段誉
     * @date 2022/3/31 9:40
     */
    public function count(): int
    {
        return IntegralOrder::where($this->setSearch())->count();
    }
}