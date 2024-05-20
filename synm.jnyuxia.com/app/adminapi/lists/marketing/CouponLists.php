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

namespace app\adminapi\lists\marketing;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\CouponEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\Coupon;
use app\common\model\CouponList;

class CouponLists extends BaseAdminDataLists implements ListsExtendInterface, ListsExcelInterface, ListsSearchInterface
{


    /**
     * @notes 设置导出字段
     * @return array
     * @author 张无忌
     * @date 2021/7/31 16:28
     */
    public function setExcelFields(): array
    {
        return [
            'id'                => '优惠券ID',
            'sn'                => '优惠券编号',
            'name'              => '优惠券名称',
            'discount_content'  => '优惠券内容',
            'get_method'        => '推广方式',
            'use_time_text'     => '用卷时间',
            'send_total_text'   => '发放总量',
            'receive_number'    => '已领取',
            'surplus_number'    => '剩余',
            'use_number'        => '已使用',
            'status_text'       => '优惠券状态',
            'create_time'       => '创建时间'
        ];
    }

    /**
     * @notes 设置导出文件名
     * @return string
     * @author 张无忌
     * @date 2021/7/31 16:28
     */
    public function setFileName(): string
    {
        return '优惠券列表';
    }

    /**
     * @notes 优惠券搜索条件
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:07
     */
    public function setSearch(): array
    {
        return [
            '=' => ['status', 'get_type'],
            '%like%' => ['name'],
            "between_time" => 'create_time'
        ];
    }

    /**
     * @notes 优惠券扩展统计
     * @return mixed
     * @author 张无忌
     * @date 2021/7/29 18:07
     */
    public function extend()
    {
        $searchwhere = $this->searchWhere;
        $model = new Coupon();
        $detail['all'] = $model->where($searchwhere)->count();
        $detail['not'] = $model->where($searchwhere)->where(['status' => CouponEnum::COUPON_STATUS_NOT])->count();
        $detail['conduct'] = $model->where($searchwhere)->where(['status' => CouponEnum::COUPON_STATUS_CONDUCT])->count();
        $detail['end'] = $model->where($searchwhere)->where(['status' => CouponEnum::COUPON_STATUS_END])->count();

        return $detail;
    }

    /**
     * @notes 获取优惠券列表
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/29 18:07
     */
    public function lists(): array
    {
        $couponListModel = new CouponList();

        $lists = (new Coupon())->withoutField('update_time,delete_time')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id desc')
            ->select()
            ->toArray();

        foreach ($lists as &$item) {

            $item['get_method'] = CouponEnum::getTypeDesc($item['get_type']);

            $item['discount_content'] =
                $item['condition_type'] == CouponEnum::CONDITION_TYPE_NOT
                    ? '无门槛, ' . '减' . $item['money'] . '元'
                    : '订单满' . $item['condition_money'] . '元, ' . '减' . $item['money'] . '元';

            $item['send_total_text'] = $item['send_total_type'] == CouponEnum::SEND_TOTAL_TYPE_NOT
                ? '不限量'
                : $item['send_total'];

            switch ($item['use_time_type']) {
                case CouponEnum::USE_TIME_TYPE_FIXED:
                    $start = date('Y-m-d H:i:s', $item['use_time_start']);
                    $end = date('Y-m-d H:i:s', $item['use_time_end']);
                    $item['use_time_text'] = $start . ' ~ ' . $end;
                    break;
                case CouponEnum::USE_TIME_TYPE_TODAY:
                    $item['use_time_text'] = '领取当日起' . $item['use_time'] . '天内可用';
                    break;
                case CouponEnum::USE_TIME_TYPE_TOMORROW:
                    $item['use_time_text'] = '领取次日起' . $item['use_time'] . '天内可用';
                    break;
            }

            $item['status_text'] = CouponEnum::getCouponStatusDesc($item['status']);
            $item['use_number'] = $couponListModel->where(['coupon_id' => $item['id'], 'status' => 1])->count();
            $item['receive_number'] = $couponListModel->where(['coupon_id' => $item['id']])->count();
            $item['surplus_number'] = $item['send_total_type'] == CouponEnum::SEND_TOTAL_TYPE_NOT
                ? '不限制'
                : $item['send_total'] - $item['receive_number'];

            unset($item['condition_type']);
            unset($item['condition_money']);
            unset($item['send_total_type']);
            unset($item['send_total']);
            unset($item['use_time_type']);
            unset($item['use_time_start']);
            unset($item['use_time_end']);
            unset($item['use_time']);
            unset($item['get_num_type']);
            unset($item['get_num']);
            unset($item['use_goods_type']);
            unset($item['use_goods_ids']);
        }

        return $lists;
    }

    /**
     * @notes 获取优惠券数量统计
     * @return int
     * @author 张无忌
     * @date 2021/7/29 18:07
     */
    public function count(): int
    {
        return (new Coupon())->where($this->searchWhere)->count();
    }
}