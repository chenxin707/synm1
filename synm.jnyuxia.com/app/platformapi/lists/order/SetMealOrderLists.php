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

namespace app\platformapi\lists\order;

use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\SetMealOrder;
use app\platformapi\lists\BasePlatformDataLists;

class SetMealOrderLists extends BasePlatformDataLists implements ListsExtendInterface,ListsExcelInterface
{
    public function setFileName(): string
    {
        return '续费订单';
    }

    public function setExcelFields(): array
    {
        return [
            'sn' => '订单编号',
            'shop_name' => '商家名称',
            'set_meal_name' => '套餐名称',
            'time_desc' => '套餐时长',
            'price' => '支付金额',
            'pay_way_desc' => '支付方式',
            'pay_status_desc' => '支付状态',
            'order_status_desc' => '订单状态',
            'create_time' => '下单时间',
        ];
    }

    public function extend()
    {
        $where = $this->where();
        foreach ($where as $key=>$val) {
            if ($val[0] == 'smo.order_status') {
                unset($where[$key]);
            }
        }

        return [
            'all' => SetMealOrder::withoutGlobalScope()->alias('smo')->leftJoin('platform_shop ps', 'smo.sid = ps.id')->where($where)->count(),
            'wait' => SetMealOrder::withoutGlobalScope()->alias('smo')->leftJoin('platform_shop ps', 'smo.sid = ps.id')->where($where)->where('smo.order_status', 0)->count(),
            'finish' => SetMealOrder::withoutGlobalScope()->alias('smo')->leftJoin('platform_shop ps', 'smo.sid = ps.id')->where($where)->where('smo.order_status', 1)->count()
        ];
    }

    public function where(): array
    {
        $where = [];
        if (isset($this->params['sn']) && $this->params['sn'] != '') {
            $where[] = ['smo.sn','like','%'.$this->params['sn'].'%'];
        }
        if (isset($this->params['shop_name']) && $this->params['shop_name'] != '') {
            $where[] = ['ps.name','like','%'.$this->params['shop_name'].'%'];
        }

        if (isset($this->params['set_meal_id']) && $this->params['set_meal_id'] !== '') {
            $where[] = ['smo.set_meal_id','=', $this->params['set_meal_id']];
        }

        if (isset($this->params['pay_status']) && $this->params['pay_status'] !== '') {
            $where[] = ['smo.pay_status','=', $this->params['pay_status']];
        }

        if (isset($this->params['pay_way']) && $this->params['pay_way'] !== '') {
            $where[] = ['smo.pay_way','=', $this->params['pay_way']];
        }

        if (isset($this->params['order_status']) && $this->params['order_status'] !== '') {
            $where[] = ['smo.order_status','=', $this->params['order_status']];
        }

        if (isset($this->params['time_type']) && isset($this->params['start_time'])) {
            switch ($this->params['time_type']) {
                case 'pay_time':
                    $where[] = ['smo.pay_time','>=', strtotime($this->params['start_time'])];
                    break;
                case 'create_time':
                    $where[] = ['smo.create_time','>=', strtotime($this->params['start_time'])];
                    break;
            }
        }

        if (isset($this->params['time_type']) && isset($this->params['end_time'])) {
            switch ($this->params['time_type']) {
                case 'pay_time':
                    $where[] = ['smo.pay_time','<=', strtotime($this->params['end_time'])];
                    break;
                case 'create_time':
                    $where[] = ['smo.create_time','<=', strtotime($this->params['end_time'])];
                    break;
            }
        }

        return $where;
    }

    public function lists(): array
    {
        $lists = SetMealOrder::withoutGlobalScope()
            ->alias('smo')
            ->leftJoin('platform_shop ps', 'smo.sid = ps.id')
            ->field('smo.id, smo.sn,smo.sid,smo.set_meal_price_snapshot,smo.pay_way,smo.pay_status,smo.order_status,smo.create_time,ps.name as shop_name')
            ->append(['shop_logo','set_meal_name', 'time_desc', 'price', 'pay_way_desc', 'pay_status_desc', 'order_status_desc'])
            ->where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('smo.id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }

    public function count(): int
    {
        return SetMealOrder::withoutGlobalScope()
            ->alias('smo')
            ->leftJoin('platform_shop ps', 'smo.sid = ps.id')
            ->where($this->where())
            ->count();
    }


}
