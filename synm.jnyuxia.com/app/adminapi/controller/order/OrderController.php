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

namespace app\adminapi\controller\order;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\order\OrderLists;
use app\adminapi\logic\order\OrderLogic;
use app\adminapi\validate\order\OrderValidate;
use app\common\service\JsonService;

class OrderController extends BaseAdminController
{
    /**
     * @notes 查看订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/4 3:05 下午
     */
    public function lists()
    {
        return $this->dataLists(new OrderLists());
    }

    /**
     * @notes 查看其他列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/4 8:51 下午
     */
    public function otherLists()
    {
        $result = (new OrderLogic())->otherLists();
        return $this->success('',$result);
    }

    /**
     * @notes 查看订单详情
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/6 4:56 下午
     */
    public function detail()
    {
        $params = (new OrderValidate())->goCheck('detail');
        $result = (new OrderLogic())->detail($params);
        return $this->success('',$result);
    }

    /**
     * @notes 修改地址
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/10 11:37 上午
     */
    public function addressEdit()
    {
        $params = (new OrderValidate())->post()->goCheck('AddressEdit', ['admin_id' => $this->adminId]);
        (new OrderLogic())->addressEdit($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 设置商家备注
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/10 11:49 上午
     */
    public function orderRemarks()
    {
        $params = (new OrderValidate())->post()->goCheck('OrderRemarks', ['admin_id' => $this->adminId]);
        (new OrderLogic())->orderRemarks($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 修改价格(订单详情)
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/10 2:52 下午
     */
    public function changePrice()
    {
        $params = (new OrderValidate())->post()->goCheck('ChangePrice', ['admin_id' => $this->adminId]);
        $result = (new OrderLogic())->changePrice($params);
        if (false === $result) {
            return $this->fail(OrderLogic::getError());
        }
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 修改运费(订单详情)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/10 3:12 下午
     */
    public function changeExpressPrice()
    {
        $params = (new OrderValidate())->post()->goCheck('ChangeExpressPrice', ['admin_id' => $this->adminId]);
        (new OrderLogic())->changeExpressPrice($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/10 4:51 下午
     */
    public function cancel()
    {
        $params = (new OrderValidate())->post()->goCheck('cancel', ['admin_id' => $this->adminId]);
        $result = (new OrderLogic())->cancel($params);
        if (false === $result) {
            return $this->fail(OrderLogic::getError());
        }
        return $this->success('取消成功',[],1,1);
    }

    /**
     * @notes 发货
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/10 6:25 下午
     */
    public function delivery()
    {
        $params = (new OrderValidate())->post()->goCheck('delivery', ['admin_id' => $this->adminId]);
        $result = (new OrderLogic())->delivery($params);
        if (false === $result) {
            return $this->fail(OrderLogic::getError());
        }
        return $this->success('发货成功',[],1,1);
    }

    /**
     * @notes 发货信息
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/10 7:16 下午
     */
    public function deliveryInfo()
    {
        $params = (new OrderValidate())->goCheck('DeliveryInfo');
        $result = (new OrderLogic())->deliveryInfo($params);
        return $this->success('',$result);
    }

    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 10:37 上午
     */
    public function confirm()
    {
        $params = (new OrderValidate())->post()->goCheck('confirm', ['admin_id' => $this->adminId]);
        (new OrderLogic())->confirm($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 物流查询
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/13 3:49 下午
     */
    public function logistics()
    {
        $params = (new OrderValidate())->goCheck('logistics');
        $result = (new OrderLogic())->logistics($params);
        return $this->success('',$result);
    }

    /**
     * @notes 小票打印
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/16 11:16
     */
    public function orderPrint()
    {
        $params = (new OrderValidate())->post()->goCheck("orderPrint");
        $result = (new OrderLogic())->orderPrint($params);
        if ($result) {
            return $this->success('打印成功，如未出小票，请检查打印机是否在线',[],1,1);
        }
        return JsonService::fail(OrderLogic::getError());
    }

    /**
     * @notes 修改物流信息
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/9/5 14:28
     */
    public function changeDelivery()
    {
        $params = $this->request->post();
        $result = (new OrderLogic())->changeDelivery($params);
        if(true === $result){
            return $this->success('修改成功',[],1,1);
        }
        return JsonService::fail($result);
    }
}