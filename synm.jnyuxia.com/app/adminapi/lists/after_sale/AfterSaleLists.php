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

namespace app\adminapi\lists\after_sale;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\AfterSaleEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\model\AfterSale;
use app\common\service\FileService;

/**
 * 售后列表类
 * Class AfterSaleLists
 * @package app\adminapi\lists\after_sale
 */
class AfterSaleLists extends BaseAdminDataLists implements ListsExtendInterface,ListsExcelInterface
{
    /**
     * @notes 导出字段
     * @return string[]
     * @author Tab
     * @date 2021/9/22 17:41
     */
    public function setExcelFields(): array
    {
        return [
            'sn' => '售后单号',
            'nickname' => '用户昵称',
            'order_sn' => '订单编号',
            'refund_type_desc' => '售后类型',
            'refund_method_desc' => '售后方式',
            'refund_total_amount' => '售后金额',
            'sub_status_desc' => '售后状态',
            'create_time' => '申请时间',
        ];
    }

    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/22 17:40
     */
    public function setFileName(): string
    {
        return '售后列表';
    }

    /**
     * @notes 统计信息
     * @return mixed|void
     * @author Tab
     * @date 2021/8/18 19:24
     */
    public function extend()
    {
        $all = AfterSale::count();
        $ing = AfterSale::where('status', AfterSaleEnum::STATUS_ING)->count();
        $success = AfterSale::where('status', AfterSaleEnum::STATUS_SUCCESS)->count();
        $fail = AfterSale::where('status', AfterSaleEnum::STATUS_FAIL)->count();
        return [
            'all' => $all,
            'ing' => $ing,
            'success' => $success,
            'fail' => $fail
        ];
    }

    /**
     * @notes 设置搜索
     * @author Tab
     * @date 2021/8/2 16:25
     */
    public function setSearch()
    {
        // 售后编号
        if(isset($this->params['after_sale_sn']) && $this->params['after_sale_sn']) {
            $this->searchWhere[] = ['as.sn', 'like', '%'.$this->params['after_sale_sn'].'%'];
        }
        // 订单编号
        if(isset($this->params['order_sn']) && $this->params['order_sn']) {
            $this->searchWhere[] = ['o.sn', 'like', '%'.$this->params['order_sn'].'%'];
        }
        // 用户信息
        if(isset($this->params['user_info']) && $this->params['user_info']) {
            $this->searchWhere[] = ['u.sn|u.nickname|u.mobile', 'like', '%'.$this->params['user_info'].'%'];
        }
        // 售后类型
        if(isset($this->params['refund_type']) && $this->params['refund_type']) {
            $this->searchWhere[] = ['as.refund_type', '=', $this->params['refund_type']];
        }
        // 售后方式
        if(isset($this->params['refund_method']) && $this->params['refund_method']) {
            $this->searchWhere[] = ['as.refund_method', '=', $this->params['refund_method']];
        }
        // 申请时间
        if(isset($this->params['start_time']) && $this->params['start_time']) {
            $this->searchWhere[] = ['as.create_time', '>=', strtotime($this->params['start_time'])];
        }
        if(isset($this->params['end_time']) && $this->params['end_time']) {
            $this->searchWhere[] = ['as.create_time', '<=', strtotime($this->params['end_time'])];
        }
        // 售后主状态
        if(isset($this->params['status']) && $this->params['status']) {
            $this->searchWhere[] = ['as.status', '=', $this->params['status']];
        }
        // 快递单号
        if(isset($this->params['invoice_no']) && $this->params['invoice_no']) {
            $this->searchWhere[] = ['as.invoice_no', 'like', '%'.$this->params['invoice_no'].'%'];
        }
    }

    /**
     * @notes 售后列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/2 16:13
     */
    public function lists() : array
    {
        // 设置搜索
        $this->setSearch();

        $field = 'as.id,as.user_id,as.order_id,as.sn,as.refund_type,as.refund_method,as.refund_total_amount,as.status,as.sub_status,as.create_time';
        $field .= ',as.refund_type as refund_type_desc,as.refund_method as refund_method_desc,as.status as status_desc,as.sub_status as sub_status_desc';
        $field .= ',o.sn as order_sn';
        $field .= ',u.nickname,u.avatar,u.sn as user_sn';

        $lists = AfterSale::withoutGlobalScope()
            ->aliasSid('as')
            ->with(['after_sale_goods'])
            ->alias('as')
            ->leftJoin('order o', 'o.id = as.order_id')
            ->leftJoin('user u', 'u.id = as.user_id')
            ->field($field)
            ->where($this->searchWhere)
            ->withSearch(['goods_info'], $this->params)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
        }

        return $lists;
    }

    /**
     * @notes 售后记录数
     * @return int
     * @author Tab
     * @date 2021/8/2 15:44
     */
    public function  count() : int
    {
        // 设置搜索
        $this->setSearch();

        $count = AfterSale::withoutGlobalScope()
            ->aliasSid('as')
            ->alias('as')
            ->leftJoin('order o', 'o.id = as.order_id')
            ->leftJoin('user u', 'u.id = as.user_id')
            ->where($this->searchWhere)->count();

        return $count;
    }
}