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

namespace app\adminapi\controller\integral;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\integral\IntegralOrderLists;
use app\adminapi\logic\integral\IntegralOrderLogic;
use app\adminapi\validate\integral\IntegralOrderValidate;

class IntegralOrderController extends BaseAdminController
{
    /**
     * @notes 兑换订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/30 5:40 下午
     */
    public function lists()
    {
        return $this->dataLists(new IntegralOrderLists());
    }

    /**
     * @notes 兑换订单详情
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/31 11:23 上午
     */
    public function detail()
    {
        $params = (new IntegralOrderValidate())->goCheck('detail');
        $result = (new IntegralOrderLogic())->detail($params['id']);
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 发货
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/31 2:29 下午
     */
    public function delivery()
    {
        $params = (new IntegralOrderValidate())->post()->goCheck('delivery', ['admin_id' => $this->adminId]);
        $result = (new IntegralOrderLogic())->delivery($params);
        if (false === $result) {
            return $this->fail(IntegralOrderLogic::getError());
        }
        return $this->success('发货成功',[],1,1);
    }

    /**
     * @notes 发货信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/31 2:37 下午
     */
    public function deliveryInfo()
    {
        $params = (new IntegralOrderValidate())->goCheck('DeliveryInfo');
        $result = (new IntegralOrderLogic())->deliveryInfo($params);
        return $this->success('',$result);
    }

    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/31 4:39 下午
     */
    public function confirm()
    {
        $params = (new IntegralOrderValidate())->post()->goCheck('confirm', ['admin_id' => $this->adminId]);
        (new IntegralOrderLogic())->confirm($params);
        return $this->success('操作成功',[],1,1);
    }


    /**
     * @notes 物流查询
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/1 14:05
     */
    public function logistics()
    {
        $params = (new IntegralOrderValidate())->goCheck('logistics');
        $result = (new IntegralOrderLogic())->logistics($params);
        return $this->success('', $result);
    }


    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/1 15:41
     */
    public function cancel()
    {
        $params = (new IntegralOrderValidate())->post()->goCheck('cancel', ['admin_id' => $this->adminId]);
        $result = (new IntegralOrderLogic())->cancel($params['id']);
        if (false === $result) {
            return $this->fail(IntegralOrderLogic::getError());
        }
        return $this->success('取消成功', [], 1, 1);
    }
}