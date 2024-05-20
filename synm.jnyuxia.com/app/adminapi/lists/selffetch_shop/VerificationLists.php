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

namespace app\adminapi\lists\selffetch_shop;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\Order;
use app\common\model\Verification;

class VerificationLists extends BaseAdminDataLists implements ListsExcelInterface
{
    /**
     * @notes 查看上门自提订单列表
     * @return array
     * @author ljj
     * @date 2021/8/12 11:37 上午
     */
    public function lists(): array
    {
        $lists = (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('user u','o.user_id = u.id')
            ->join('order_goods og','o.id = og.order_id')
            ->field('o.id,o.sn,o.order_type,o.order_amount,o.address,u.nickname,u.avatar,o.delivery_type,o.verification_status,o.create_time,o.order_status,o.selffetch_shop_id,o.is_team_success')
            ->order('o.id','desc')
            ->append(['order_type_desc','admin_order_btn','order_status_desc','delivery_type_desc','verification_status_desc'])
            ->hidden(['delivery_type','order_status'])
            ->with(['order_goods' => function($query){
                $query->field('order_id,goods_snap,goods_name,goods_price,goods_num')->append(['goods_image','spec_value_str'])->hidden(['goods_snap']);
            },'selffetch_shop' => function($query){
                $query->field('id,province,city,district,address')->append(['detailed_address'])->hidden(['goods_snap']);
            }])
            ->withSearch(array_diff(array_keys($this->params), ['page_no', 'page_size', 'start_time', 'end_time','order_status', 'export', 'file_name', 'page_type', 'page_start', 'page_end']), $this->params)
            ->where(['o.delivery_type'=>DeliveryEnum::SELF_DELIVERY])
            ->limit($this->limitOffset, $this->limitLength)
            ->group('o.id')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            //获取收件人
            $list['contact'] = $list['address']->contact;
            $list['mobile'] = $list['address']->mobile;
            unset($list['address']);

            //处理订单操作按钮
            unset($list['admin_order_btn']['delete_btn']);
            unset($list['admin_order_btn']['deliver_btn']);
            unset($list['admin_order_btn']['confirm_btn']);
            unset($list['admin_order_btn']['logistics_btn']);
            unset($list['admin_order_btn']['refund_btn']);
            unset($list['admin_order_btn']['refund_detail_btn']);
            unset($list['admin_order_btn']['print_btn']);
            unset($list['admin_order_btn']['remark_btn']);
            unset($list['admin_order_btn']['cancel_btn']);

            //获取核销员&核销时间
            $list['verifier_name'] = '-';
            $list['verification_time'] = '-';
            if ($list['verification_status'] == OrderEnum::WRITTEN_OFF) {
                $verification = Verification::where('order_id',$list['id'])->json(['snapshot'],true)->find();
                $list['verification_time'] = $verification['create_time'];
                $list['verifier_name'] = $verification['snapshot']['name'];
            }

            //增加订单商品信息，用于导出
            $list['order_goods_snap'] = '';
            foreach ($list['order_goods'] as $val) {
                $list['order_goods_snap'] .= '【商品名称：'.$val['goods_name'].'；商品规格：'.$val['spec_value_str'].'；商品价格：'.$val['goods_price'].'；商品数量：'.$val['goods_num'].'】、';
            }
            //拼团订单显示拼团状态
            if(OrderEnum::TEAM_ORDER == $list['order_type'] && 1 != $list['is_team_success']){
                0 == $list['is_team_success'] ? $tips = '（拼团中）' : $tips = '（拼团失败）';
                $list['order_type_desc'] .=$tips;
            }
            $list['shop_detailed_address'] = $list['selffetch_shop']['detailed_address'] ?? '';
        }

        return $lists;
    }

    /**
     * @notes 查看上门自提订单总数
     * @return int
     * @author ljj
     * @date 2021/8/12 11:38 上午
     */
    public function count(): int
    {
        return (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('user u','o.user_id = u.id')
            ->join('order_goods og','o.id = og.order_id')
            ->withSearch(array_diff(array_keys($this->params), ['page_no', 'page_size', 'start_time', 'end_time','order_status', 'export', 'file_name', 'page_type', 'page_start', 'page_end']), $this->params)
            ->where(['o.delivery_type'=>DeliveryEnum::SELF_DELIVERY])
            ->group('o.id')
            ->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/8/26 4:43 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'id'                        => 'ID',
            'order_type_desc'           => '订单类型',
            'sn'                        => '订单编号',
            'nickname'                  => '用户',
            'order_goods_snap'          => '商品信息',
            'order_amount'              => '实付金额',
            'contact'                   => '收货人',
            'shop_detailed_address'     => '自提地址',
            'delivery_type_desc'        => '配送方式',
            'verification_status_desc'  => '核销状态',
            'order_status_desc'         => '订单状态',
            'verifier_name'             => '核销员',
            'verification_time'         => '核销时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/8/26 4:43 下午
     */
    public function setFileName(): string
    {
        return '自提订单列表';
    }
}