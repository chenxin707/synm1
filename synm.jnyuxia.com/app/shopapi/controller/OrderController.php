<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\shopapi\controller;

use app\shopapi\lists\OrderLists;
use app\shopapi\logic\Order\OrderLogic;
use app\shopapi\validate\PlaceOrderValidate;
use app\shopapi\validate\OrderValidate;

/**
 * 订单
 * Class OrderController
 * @package app\shopapi\controller
 */
class OrderController extends BaseShopController
{

    /**
     * @notes 提交订单/结算详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/23 15:52
     */
    public function placeOrder()
    {
        $data = [
            'terminal' => $this->userInfo['terminal'],
            'user_id'=> $this->userId
        ];
        $params = (new PlaceOrderValidate())->post()->goCheck('', $data);

        //订单结算信息
        $settlement = OrderLogic::settlement($params);
        if (false === $settlement) {
            return $this->fail(OrderLogic::getError());
        }
        //结算信息
        if ($params['action'] == 'settle') {
            return $this->data($settlement);
        }
        //提交订单
        $result = OrderLogic::submitOrder($settlement);
        if (false === $result) {
            return $this->fail(OrderLogic::getError());
        }
        return $this->data($result);
    }


    /**
     * @notes 订单列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/23 18:58
     */
    public function lists()
    {
        return $this->dataLists((new OrderLists()));
    }


    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/8/2 21:00
     */
    public function detail()
    {
        $params = (new OrderValidate())->goCheck('detail', ['user_id' => $this->userId]);
        return $this->data(OrderLogic::getDetail($params));
    }


    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/8/2 16:37
     */
    public function cancel()
    {
        $params = (new OrderValidate())->post()->goCheck('cancel', ['user_id' => $this->userId]);
        $result = OrderLogic::cancelOrder($params);
        if (false === $result) {
            return $this->fail(OrderLogic::getError());
        }
        return $this->success('取消订单成功');
    }

    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/8/2 15:21
     */
    public function confirm()
    {
        $params = (new OrderValidate())->post()->goCheck('confirm', ['user_id' => $this->userId]);
        OrderLogic::confirmOrder($params);
        return $this->success('确认收货成功');
    }

    /**
     * @notes 查看物流
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/13 6:08 下午
     */
    public function orderTraces()
    {
        $params = (new OrderValidate())->goCheck('OrderTraces', ['user_id' => $this->userId]);
        $result = OrderLogic::orderTraces($params);
        return $this->data($result);
    }

    /**
     * @notes 获取配送方式
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/27 2:33 下午
     */
    public function getDeliveryType()
    {
        $result = (new OrderLogic())->getDeliveryType();
        return $this->success('',$result);
    }

    /**
     * @notes 删除订单
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/31 2:38 下午
     */
    public function del()
    {
        $params = (new OrderValidate())->post()->goCheck('del', ['user_id' => $this->userId]);
        (new OrderLogic())->del($params);
        return $this->success('删除成功',[],1,1);
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