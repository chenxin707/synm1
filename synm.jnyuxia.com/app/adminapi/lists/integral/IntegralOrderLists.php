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

namespace app\adminapi\lists\integral;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\model\IntegralOrder;
use app\common\service\FileService;

class IntegralOrderLists extends BaseAdminDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2022/3/30 5:12 下午
     */
    public function setSearch(): array
    {
        $where = [];
        $params = $this->params;
        //列表状态
        if (isset($params['status']) && $params['status'] != '') {
            $where[] = ['order_status', '=', $params['status']];
        }
        //兑换单号
        if (isset($params['sn']) && $params['sn'] != '') {
            $where[] = ['sn', 'like', '%' . $params['sn'] . '%'];
        }
        //商品名称
        if (isset($params['goods_name']) && $params['goods_name'] != '') {
            $where[] = ['goods_snap->name', 'like', '%' . $params['goods_name'] . '%'];
        }
        //兑换类型
        if (isset($params['exchange_type']) && $params['exchange_type'] != '') {
            $where[] = ['exchange_type', '=', intval($params['exchange_type'])];
        }
        //订单状态
        if (isset($params['order_status']) && $params['order_status'] != '') {
            $where[] = ['order_status', '=', $params['order_status']];
        }
        //下单时间
        if (isset($params['start_time']) && $params['start_time'] != '') {
            $where[] = ['create_time', '>=', strtotime($params['start_time'])];
        }
        if (isset($params['end_time']) && $params['end_time'] != '') {
            $where[] = ['create_time', '<=', strtotime($params['end_time'])];
        }

        return $where;
    }

    /**
     * @notes 兑换订单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/30 5:40 下午
     */
    public function lists(): array
    {
        $lists = IntegralOrder::withoutField(['transaction_id', 'update_time', 'delete_time'])
            ->with(['user'])
            ->where($this->setSearch())
            ->append(['pay_status_text', 'delivery_address', 'order_status_desc', 'exchange_type_desc', 'admin_btns'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            $list['goods_snap']['image'] = FileService::getFileUrl($list['goods_snap']['image']);
        }

        return $lists;
    }

    /**
     * @notes 兑换订单数量
     * @return int
     * @author ljj
     * @date 2022/3/30 5:40 下午
     */
    public function count(): int
    {
        return IntegralOrder::where($this->setSearch())->count();
    }
}