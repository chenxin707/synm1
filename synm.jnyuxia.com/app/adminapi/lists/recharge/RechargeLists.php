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

namespace app\adminapi\lists\recharge;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\RechargeOrder;
use app\common\service\FileService;

/**
 * 充值记录列表
 * Class RecharLists
 * @package app\adminapi\lists
 */
class RechargeLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 导出字段
     * @return array
     * @author Tab
     * @date 2021/9/22 18:20
     */
    public function setExcelFields(): array
    {
        return [
            'sn' => '充值单号',
            'nickname' => '用户昵称',
            'order_amount' => '充值金额',
            'give_money' => '赠送余额',
            'pay_way' => '支付方式',
            'pay_time' => '支付时间',
            'pay_status' => '订单状态',
            'create_time' => '下单时间',
        ];
    }

    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/22 18:20
     */
    public function setFileName(): string
    {
        return '充值记录';
    }

    /**
     * @notes 设置搜索
     * @return \string[][]
     * @author Tab
     * @date 2021/8/11 16:09
     */
    public function setSearch(): array
    {
        return [
            '=' => ['ro.sn', 'u.mobile', 'ro.pay_way', 'ro.pay_status'],
            '%like%' => ['u.nickname']
        ];
    }

    /**
     * @notes 附加搜索条件
     * @author Tab
     * @date 2021/8/11 16:11
     */
    public function attachWhere()
    {
        // 用户编号
        if(isset($this->params['user_sn']) && !empty($this->params['user_sn'])) {
            $this->searchWhere[] = ['u.sn', '=', $this->params['user_sn']];
        }

        // 支付时间
        if(isset($this->params['type_time']) && $this->params['type_time'] == 1 && isset($this->params['start_time']) && isset($this->params['end_time'])) {
            $this->searchWhere[] = ['ro.pay_time', 'between', [$this->startTime, $this->endTime]];
        }

        // 下单时间
        if(isset($this->params['type_time']) && $this->params['type_time'] == 2 && isset($this->params['start_time']) && isset($this->params['end_time'])) {
            $this->searchWhere[] = ['ro.create_time', 'between', [$this->startTime, $this->endTime]];
        }
    }

    /**
     * @notes 充值记录列表
     * @return array
     * @author Tab
     * @date 2021/8/11 16:30
     */
    public function lists(): array
    {
        // 附加搜索
        $this->attachWhere();

        $field = 'ro.sn,ro.order_amount,ro.pay_way,ro.pay_time,ro.pay_status,ro.create_time,ro.award';
        $field .= ',u.avatar,u.nickname';
        $lists = RechargeOrder::withoutGlobalScope()
            ->aliasSid('ro')
            ->alias('ro')
            ->leftJoin('user u', 'u.id = ro.user_id')
            ->field($field)
            ->where($this->searchWhere)
            ->order('ro.id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
            $item['give_money'] = $this->giveMoney($item);
            $item['pay_time'] = empty($item['pay_time']) ? '' : date('Y-m-d H:i:s', $item['pay_time']) ;
        }

        return $lists;
    }

    /**
     * @notes 充值记录数量
     * @return int
     * @author Tab
     * @date 2021/8/11 16:30
     */
    public function count(): int
    {
        // 附加搜索
        $this->attachWhere();

        $count = RechargeOrder::withoutGlobalScope()
            ->aliasSid('ro')
            ->alias('ro')
            ->leftJoin('user u', 'u.id = ro.user_id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }

    /**
     * @notes 充值赠送金额
     * @param $item
     * @return int|mixed|string
     * @author Tab
     * @date 2021/8/11 15:49
     */
    public function giveMoney($item)
    {
        if(!isset($item['award']) || empty($item['award'])) {
            return 0;
        }
        foreach($item['award'] as  $subItem) {
            if(isset($subItem['give_money'])) {
                return clear_zero($subItem['give_money']);
            }
        }
        return 0;
    }
}