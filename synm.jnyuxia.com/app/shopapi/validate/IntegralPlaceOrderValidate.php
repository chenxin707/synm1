<?php

namespace app\shopapi\validate;


use app\common\enum\IntegralGoodsEnum;
use app\common\model\IntegralGoods;
use app\common\model\UserAddress;
use app\common\validate\BaseValidate;

/**
 * 积分订单下单验证
 * Class IntegralOrderValidate
 * @package app\api\validate
 */
class IntegralPlaceOrderValidate extends BaseValidate
{
    protected $rule = [
        'num' => 'require|number|gt:0',
        'id' => 'require|number|checkGoods',
        'address_id' => 'require|checkAddress',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'id.number' => '参数类型错误',
        'num.require' => '请选择商品数量',
        'num.number' => '商品数量参数类型错误',
        'num.gt' => '请选择商品数量',
        'address_id.require' => '请选择地址',
    ];

    /**
     * @notes 订单结算场景
     * @return IntegralPlaceOrderValidate
     * @author 段誉
     * @date 2022/3/31 11:35
     */
    public function sceneSettlement()
    {
        return $this->only(['id','num']);
    }


    /**
     * @notes 提交订单场景
     * @return IntegralPlaceOrderValidate
     * @author 段誉
     * @date 2022/3/31 11:36
     */
    public function sceneSubmit()
    {
        return $this->only(['id', 'num', 'address_id']);
    }



    /**
     * @notes 验证商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/31 11:37
     */
    protected function checkGoods($value, $rule, $data)
    {
        $conditon = [
            'id' => $value,
            'status' => IntegralGoodsEnum::STATUS_SHELVES
        ];
        $goods = IntegralGoods::where($conditon)->findOrEmpty();

        if ($goods->isEmpty()) {
            return '积分商品不存在';
        }

        if ($goods['stock'] < intval($data['num'])) {
            return '积分商品库存不足';
        }

        return true;
    }



    /**
     * @notes 验证地址信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/31 11:38
     */
    protected function checkAddress($value, $rule, $data)
    {
        $condition = [
            'id' => (int)$value,
            'user_id' => $data['user_id'],
        ];
        $address = UserAddress::where($condition)->findOrEmpty();

        if ($address->isEmpty()) {
            return '收货地址信息不存在';
        }

        return true;
    }

}
