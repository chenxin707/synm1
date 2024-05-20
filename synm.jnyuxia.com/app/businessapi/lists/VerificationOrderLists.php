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
use app\common\enum\AfterSaleEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\model\AfterSale;
use app\common\model\Order;

/**
 * 订单列表
 * Class OrderLists
 * @package app\businessapi\lists
 */
class VerificationOrderLists extends BaseBusinesseDataLists{


    public function searchwhere()
    {
        $where = [];
        $where[] = ['pay_status','=',PayEnum::ISPAID];
        $where[] = ['delivery_type','=',DeliveryEnum::SELF_DELIVERY];
        if(isset($this->params['type'])){
            $where[] = ['verification_status','=',$this->params['type']];
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
        $lists = Order::withSearch(['order_type'], [
            'order_type' => $this->params['type'],
        ])
            ->with(['order_goods' => function ($query) {
                $query->field('id,goods_id,order_id,goods_snap,goods_name,goods_price,goods_num,is_comment,original_price')
                    ->append(['goods_image', 'spec_value_str'])
                    ->hidden(['goods_snap']);
            }])
            ->append(['consignee','mobile'])
            ->hidden(['address'])
            ->where($this->searchwhere())
            ->field(['id', 'sn', 'user_id','order_type', 'order_status', 'total_num', 'order_amount', 'delivery_type', 'pay_status','address','create_time','verification_status'])
            ->order(['id' => 'desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()->toArray();


        foreach ($lists as &$list){
            foreach ($list['order_goods'] as &$orderGoods){
                //售后状态
                $orderGoods['after_sale_status_desc'] = '无售后';
                $after_sale = AfterSale::where(['order_goods_id' => $orderGoods['id'], 'order_id' => $orderGoods['order_id']])->findOrEmpty();
                if (!$after_sale->isEmpty()) {
                    $orderGoods['after_sale_status_desc'] = AfterSaleEnum::getStatusDesc($after_sale->status);
                }
            }
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

        return (new Order())
            ->where($this->searchwhere())
            ->count();

    }
}