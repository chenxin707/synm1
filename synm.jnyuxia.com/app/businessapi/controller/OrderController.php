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

namespace app\businessapi\controller;

use app\businessapi\lists\VerificationOrderLists;
use app\businessapi\logic\OrderLogic;
use app\businessapi\validate\OrderValidate;


/**
 * 订单控制器类
 * Class OrderController
 * @package app\businessapi\controller
 */
class OrderController extends BaseBusinesseController
{

    /**
     * @notes 查看订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/4 3:05 下午
     */
    public function lists()
    {
        return $this->dataLists();
    }


    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/16 18:49
     */
    public function detail()
    {
        $params = (new OrderValidate())->goCheck('id');
        return $this->data((new OrderLogic())->detail($params));
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
     * @notes 获取地址
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/17 17:34
     */
    public function getAddress()
    {
        $params = (new OrderValidate())->goCheck('id');
        return $this->data((new OrderLogic())->getAddress($params));
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
     * @notes 查看物流
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/13 6:08 下午
     */
    public function orderTraces()
    {
        $params = (new OrderValidate())->goCheck('OrderTraces');
        $result = (new OrderLogic())->orderTraces($params);
        return $this->data($result);
    }


    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/17 10:40
     */
    public function confirm()
    {
        $params = (new OrderValidate())->post()->goCheck('confirm', ['admin_id' => $this->adminId]);
        (new OrderLogic())->confirm($params);
        return $this->success('操作成功',[],1,1);
    }


    /**
     * @notes 核销订单列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/17 10:52
     */
    public function verificationOrderLists()
    {
        return $this->dataLists(new VerificationOrderLists());
    }


    /**
     * @notes 获取核销订单
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/17 11:39
     */
    public function getVerificationOrder()
    {
        $params = (new OrderValidate())->get()->goCheck('code');
        $order = (new OrderLogic())->getVerificationOrder($params);
        return $this->success('操作成功',$order,1,1);
    }

    /**
     * @notes 提货核销
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/12 11:57 上午
     */
    public function verification()
    {
        $params = (new OrderValidate())->post()->goCheck('verification', ['admin_info'=>$this->adminInfo]);
        $result = (new OrderLogic())->verification($params);
        if (true !== $result) {
            if (is_array($result)) {
                return $this->success($result['msg'],[],10,1);
            }
            return $this->fail(OrderLogic::getError());
        }
        return $this->success('操作成功',[],1,1);
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
     * @notes 获取物流接口
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/20 11:32
     */
    public function getDeliverInfo()
    {
        $params = (new OrderValidate())->goCheck('id');
        $result = (new OrderLogic())->getDeliverInfo($params);
        return $this->success('',$result);
    }





}
