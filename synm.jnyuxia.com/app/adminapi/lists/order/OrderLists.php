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

namespace app\adminapi\lists\order;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\Order;
use app\common\service\FileService;

class OrderLists extends BaseAdminDataLists implements ListsExtendInterface,ListsExcelInterface
{
    /**
     * @notes 查看订单列表
     * @return array
     * @author ljj
     * @date 2021/8/6 11:25 上午
     */
    public function lists(): array
    {
        $lists = (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('user u','o.user_id = u.id')
            ->join('order_goods og','o.id = og.order_id')
            ->field('o.id,o.sn,o.order_type,o.order_amount,o.address,o.pay_status,o.order_status,o.create_time,u.id as user_id,u.nickname,u.sn as user_sn,u.avatar,o.delivery_type,o.verification_status,o.express_status')
            ->order('o.id','desc')
            ->append(['order_type_desc','pay_status_desc','order_status_desc','admin_order_btn','delivery_type_desc','delivery_address'])
            ->hidden(['pay_status','order_status','delivery_type','verification_status'])
            ->with(['order_goods' => function($query){
                $query->field('goods_id,order_id,goods_snap,goods_name,goods_price,goods_num,original_price')->append(['goods_image','spec_value_str'])->hidden(['goods_snap']);
            }])
            ->withSearch(array_diff(array_keys($this->params), ['page_no', 'page_size', 'start_time', 'end_time', 'page_size', 'export', 'file_name', 'page_type', 'page_start', 'page_end']), $this->params)
            ->limit($this->limitOffset, $this->limitLength)
            ->group('o.id')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            //获取收件人
            $list['contact'] = $list['address']->contact ?? '';
            $list['mobile'] = $list['address']->mobile ?? '';
            unset($list['address']);

            //增加订单商品信息，用于导出
            unset($val);
            $list['order_goods_snap'] = '';
            $orderGoodsList = [];
            $goodsItemList = [];
            $goodsPriceList = [];
            $goodsNumList = [];
            foreach ($list['order_goods'] as $val) {
                $list['order_goods_snap'] .= '【商品名称：'.$val['goods_name'].'；商品规格：'.$val['spec_value_str'].'；商品价格：'.$val['goods_price'].'；商品数量：'.$val['goods_num'].'】、';
                $orderGoodsList[] = $val['goods_name'];
                $goodsItemList[] = $val['spec_value_str'];
                $goodsPriceList[] = $val['goods_price'];
                $goodsNumList[] = $val['goods_num'];
            }
            $list['order_goods_list'] = implode(';',$orderGoodsList);
            $list['goods_item_list'] = implode(';',$goodsItemList);
            $list['goods_price_list'] = implode(';',$goodsPriceList);
            $list['goods_num_list'] = implode(';',$goodsNumList);
        }
        return $lists;
    }

    /**
     * @notes 查看订单列表总数
     * @return int
     * @author ljj
     * @date 2021/8/4 3:23 下午
     */
    public function count(): int
    {
        return (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('user u','o.user_id = u.id')
            ->join('order_goods og','o.id = og.order_id')
            ->withSearch(array_diff(array_keys($this->params), ['page_no', 'page_size', 'start_time', 'end_time', 'export', 'file_name', 'page_type', 'page_start', 'page_end']), $this->params)
            ->group('o.id')
            ->count();
    }

    /**
     * @notes 统计订单数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/4 7:51 下午
     */
    public function extend(): array
    {
        $lists = (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('user u','o.user_id = u.id')
            ->join('order_goods og','o.id = og.order_id')
            ->withSearch(array_diff(array_keys($this->params), ['page_no', 'page_size', 'start_time', 'end_time','order_status', 'export', 'file_name', 'page_type', 'page_start', 'page_end']), $this->params)
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

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/8/5 7:02 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'id'                => 'ID',
            'order_type_desc'   => '订单类型',
            'sn'                => '订单编号',
            'nickname'          => '用户名称',
            'order_goods_list'  => '商品信息',
            'goods_item_list'   => '规格',
            'goods_price_list'  => '商品价格',
            'goods_num_list'    => '商品数量',
            'order_amount'      => '实付金额',
            'contact'           => '收货人',
            'mobile'            => '手机号码',
            'delivery_address'  => '收货地址',
            'pay_status_desc'   => '支付状态',
            'order_status_desc' => '订单状态',
            'create_time'       => '下单时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/8/5 7:02 下午
     */
    public function setFileName(): string
    {
        return '订单列表';
    }
}