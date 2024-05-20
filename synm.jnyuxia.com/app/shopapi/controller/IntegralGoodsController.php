<?php

namespace app\shopapi\controller;

use app\shopapi\logic\IntegralGoodsLogic;
use app\shopapi\validate\IntegralGoodsValidate;

/**
 * 积分商品
 * Class IntegralGoods
 * @package app\api\controller
 */
class IntegralGoodsController extends BaseShopController
{

    /**
     * @notes 积分商品列表
     * @author 段誉
     * @date 2022/3/31 9:46
     */
    public function lists()
    {
        $lists = IntegralGoodsLogic::lists($this->userId);
        return $this->data($lists);
    }


    /**
     * @notes 商品详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/31 9:48
     */
    public function detail()
    {
        $params = (new IntegralGoodsValidate())->goCheck();
        $detail = IntegralGoodsLogic::detail($params);
        return $this->success('获取成功', $detail);
    }

}
