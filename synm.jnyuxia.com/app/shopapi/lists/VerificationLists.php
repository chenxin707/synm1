<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\shopapi\lists;


use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\Order;

class VerificationLists extends BaseShopDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author ljj
     * @date 2021/8/27 3:39 下午
     */
    public function setSearch(): array
    {
        $where[] = ['o.verification_status', '=', $this->params['type'] ?? 0];
        $where[] = ['o.delivery_type', '=', DeliveryEnum::SELF_DELIVERY];
        $where[] = ['o.pay_status', '=', PayEnum::ISPAID];
        $where[] = ['sv.user_id', '=', $this->request->userId];
        $where[] = ['sv.status', '=', YesNoEnum::YES];

        return $where;
    }

    /**
     * @notes 查看自提订单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/27 4:14 下午
     */
    public function lists(): array
    {
        $lists = Order::withoutGlobalScope()
            ->field('o.id,o.address,o.verification_status')
            ->aliasSid('o')
            ->alias('o')
            ->join('selffetch_verifier sv', 'sv.selffetch_shop_id = o.selffetch_shop_id')
            ->with(['order_goods' => function ($query) {
                $query->field('order_id,goods_snap,goods_name,goods_num')
                    ->append(['goods_image', 'spec_value_str'])
                    ->hidden(['goods_snap']);
            }])
            ->append(['verification_status_desc'])
            ->hidden(['verification_status'])
            ->where(self::setSearch())
            ->group('o.id')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            $list['contact'] = $list['address']->contact;
            unset($list['address']);
        }

        return $lists;
    }

    /**
     * @notes 查看自提订单总数
     * @return int
     * @author ljj
     * @date 2021/8/27 4:14 下午]
     */
    public function count(): int
    {
        return Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('selffetch_verifier sv', 'sv.selffetch_shop_id = o.selffetch_shop_id')
            ->where(self::setSearch())
            ->group('o.id')
            ->count();
    }
}