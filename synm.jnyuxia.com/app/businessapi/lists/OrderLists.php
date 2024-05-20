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
namespace app\businessapi\lists;

use app\common\{enum\DeliveryEnum, enum\OrderEnum, enum\TeamEnum, lists\ListsExtendInterface, model\Order};


/**
 * 订单列表接口
 * Class GoodsLists
 * @package app\adminapi\lists\goods
 */
class OrderLists extends BaseBusinesseDataLists implements ListsExtendInterface{

    public function searchWhere()
    {
        $where = [];
        if(isset($this->params['type']) && '' != $this->params['type']) {
            $where[] = ['order_status','=',$this->params['type']];
        }
        if(isset($this->params['keyword']) && $this->params['keyword']) {
            $where[] = ['o.sn|og.goods_name','like','%'.$this->params['keyword'].'%'];
        }
        return $where;
    }

    /**
     * @notes 实现数据列表
     * @return array
     * @author 令狐冲
     * @date 2021/7/6 00:33
     */
    public function lists(): array
    {

        $lists = (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->where($this->searchWhere())
            ->join('order_goods og','o.id = og.order_id')
            ->with(['order_goods' => function ($query) {
                $query->field('goods_id,order_id,goods_snap,goods_name,goods_price,goods_num,is_comment,original_price')
                    ->append(['goods_image', 'spec_value_str'])
                    ->hidden(['goods_snap']);
            },'user'])

            ->field(['o.id', 'o.sn', 'o.user_id','o.order_type', 'o.order_status', 'o.total_num', 'o.order_amount', 'o.delivery_type', 'o.is_team_success', 'o.pay_status', 'o.express_status','o.delivery_content', 'o.create_time'])
            ->append(['businesse_btn'])
            ->order(['o.id' => 'desc'])
            ->group('o.id')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()->toArray();

        foreach ($lists as &$list){
            //查看提货码按钮
//            $list['businesse_btn']['pickup_btn'] = ($list['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY && $list['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? 1 : 0;
            //订单状态描述
            $list['order_status_desc'] = ($list['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY && $list['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '待取货' : OrderEnum::getOrderStatusDesc($list['order_status']);
            if ($list['order_type'] == OrderEnum::TEAM_ORDER && $list['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS){
//                $list['btn']['pickup_btn'] = 0;
                $list['order_status_desc'] = ($list['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY) ? TeamEnum::getStatusDesc($list['is_team_success']) : OrderEnum::getOrderStatusDesc($list['order_status']);
            }

            //订单类型
            $list['order_type_desc'] = ($list['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '自提订单' : OrderEnum::getOrderTypeDesc($list['order_type']);
        }

        return $lists;
    }

    /**
     * @notes 实现数据列表记录数
     * @return int
     * @author 令狐冲
     * @date 2021/7/6 00:34
     */
    public function count(): int
    {
        return (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('order_goods og','o.id = og.order_id')
            ->group('o.id')
            ->where($this->searchWhere())
            ->count();
    }


    /**
     * @notes 扩展字段
     * @return mixed
     * @author 令狐冲
     * @date 2021/7/21 17:45
     */
    public function extend(): array
    {
        $lists =  (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('user u','o.user_id = u.id')
            ->join('order_goods og','o.id = og.order_id')
            ->group('o.id')
            ->field('o.id,o.order_status')
            ->select()
            ->toArray();

        $data['all_count'] = 0;
        $data['pay_count'] = 0;
        $data['delivery_count'] = 0;
        $data['receive_count'] = 0;
        $data['finish_count'] = 0;
        $data['close_count'] = 0;
        foreach ($lists as $val) {
            $data['all_count'] += 1;

            if ($val['order_status'] == 0) {
                $data['pay_count'] += 1;
            }
            if ($val['order_status'] == 1) {
                $data['delivery_count'] += 1;
            }
            if ($val['order_status'] == 2) {
                $data['receive_count'] += 1;
            }
            if ($val['order_status'] == 3) {
                $data['finish_count'] += 1;
            }
            if ($val['order_status'] == 4) {
                $data['close_count'] += 1;
            }
        }
        return $data;
    }

}