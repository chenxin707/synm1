<?php

namespace app\shopapi\controller;

use app\shopapi\lists\IntegralOrderLists;
use app\shopapi\logic\IntegralOrderLogic;
use app\shopapi\validate\IntegralOrderValidate;
use app\shopapi\validate\IntegralPlaceOrderValidate;


/**
 * 积分商城订单
 * Class IntegralOrder
 * @package app\api\controller
 */
class IntegralOrderController extends BaseShopController
{

    /**
     * @notes 订单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/2 9:39
     */
    public function lists()
    {
        return $this->dataLists(new IntegralOrderLists());
    }


    /**
     * @notes 结算订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/2 9:51
     */
    public function settlement()
    {
        $params = (new IntegralPlaceOrderValidate())->goCheck('settlement', ['user_id' => $this->userId]);
        $result = IntegralOrderLogic::settlement($params);
        return $this->success('获取成功', $result);
    }


    /**
     * @notes 提交订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/31 12:14
     */
    public function submitOrder()
    {
        $params = (new IntegralPlaceOrderValidate())->post()->goCheck('submit', [
            'user_id' => $this->userId,
            'terminal' => $this->userInfo['terminal'],
        ]);
        $result = IntegralOrderLogic::submitOrder($params);
        if (false === $result) {
            return $this->fail(IntegralOrderLogic::getError());
        }
        return $this->success('提交成功', $result);
    }


    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 10:23
     */
    public function detail()
    {
        $params = (new IntegralOrderValidate())->goCheck('detail', ['user_id' => $this->userId]);
        $result = IntegralOrderLogic::detail($params['id']);
        return $this->success('获取成功', $result);
    }


    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 14:57
     */
    public function cancel()
    {
        $params = (new IntegralOrderValidate())->post()->goCheck('cancel', ['user_id' => $this->userId]);
        $result = IntegralOrderLogic::cancel($params['id']);
        if (false === $result) {
            return $this->fail(IntegralOrderLogic::getError());
        }
        return $this->success('取消成功');
    }


    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 10:59
     */
    public function confirm()
    {
        $params = (new IntegralOrderValidate())->post()->goCheck('confirm', ['user_id' => $this->userId]);
        IntegralOrderLogic::confirm($params['id'], $this->userId);
        return $this->success('确认成功');
    }


    /**
     * @notes 删除订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 10:59
     */
    public function del()
    {
        $params = (new IntegralOrderValidate())->post()->goCheck('del', ['user_id' => $this->userId]);
        IntegralOrderLogic::del($params['id']);
        return $this->success('删除成功');
    }


    /**
     * @notes 查看物流
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/3 17:31
     */
    public function orderTraces()
    {
        $params = (new IntegralOrderValidate())->goCheck('traces', ['user_id' => $this->userId]);
        $result = IntegralOrderLogic::orderTraces($params['id']);
        return $this->success('获取成功', $result);
    }


}
