<?php

namespace app\shopapi\validate;


use app\common\enum\IntegralGoodsEnum;
use app\common\model\IntegralGoods;
use app\common\validate\BaseValidate;

/**
 * 积分商品验证
 * Class IntegralOrderValidate
 * @package app\api\validate
 */
class IntegralGoodsValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number|checkGoods',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'id.number' => '参数类型错误',
    ];


    /**
     * @notes 验证商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/31 9:47
     */
    protected function checkGoods($value, $rule, $data)
    {
        $goods = IntegralGoods::findOrEmpty($value);

        if ($goods->isEmpty()) {
            return '积分商品不存在';
        }

        if ($goods['status'] != IntegralGoodsEnum::STATUS_SHELVES) {
            return '商品已下架';
        }

        return true;
    }

}
