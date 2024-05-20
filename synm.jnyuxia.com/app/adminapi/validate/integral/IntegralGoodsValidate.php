<?php

namespace app\adminapi\validate\integral;


use app\common\enum\IntegralGoodsEnum;
use app\common\model\IntegralGoods;
use app\common\validate\BaseValidate;


/**
 * 积分商品验证
 * Class IntegralGoodsValidate
 * @package app\admin\validate\kefu
 */
class IntegralGoodsValidate extends BaseValidate
{

    protected $rule = [
        'id'=>'require|checkGoods',
        'type' => 'require|in:1,2',
        'name' => 'require',
        'image' => 'require',
        'market_price' => 'checkMarketPrice',
        'stock' => 'require|integer|gt:0',
        'exchange_way' => 'requireIf:type,1',
        'need_integral' => 'require|integer|checkNeedIntegral',
        'need_money' => 'requireIf:exchange_way,2|checkNeedMoney',
        'delivery_way' => 'requireIf:type,1',
        'express_type' => 'requireIf:delivery_way,1',
        'express_money' => 'requireIf:express_type,2|checkExpressMoney',
        'balance' => 'requireIf:type,2|checkBalance',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'type.require' => '请选择兑换类型',
        'type.in' => '兑换类型错误',
        'name.require' => '请填写商品名称',
        'image.require' => '请上传商品封面',
        'stock.require' => '请填写发放库存',
        'stock.integer' => '请填写整数发放库存',
        'stock.gt' => '发放库存需大于0',
        'exchange_way.requireIf' => '请选择兑换方式',
        'need_integral.require' => '请填写兑换积分',
        'need_integral.integer' => '请填写整数兑换积分',
        'need_money.requireIf' => '请填写兑换金额',
        'delivery_way.requireIf' => '请选择物流配送方式',
        'express_type.requireIf' => '请选择物流方式',
        'express_money.requireIf' => '请填写运费',
        'balance.requireIf' => '请填写红包面值',
        'status.require' => '请选择商品状态',
        'status.in' => '商品状态参数错误',
    ];

    /**
     * @notes 添加场景
     * @author 段誉
     * @date 2022/3/30 14:17
     */
    public function sceneAdd()
    {
        $this->remove('id', true);
    }

    /**
     * @notes 编辑场景
     * @author 段誉
     * @date 2022/3/30 14:17
     */
    public function sceneEdit()
    {

    }

    /**
     * @notes 删除场景
     * @author 段誉
     * @date 2022/3/30 16:02
     */
    public function sceneDel()
    {
        $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @author 段誉
     * @date 2022/3/30 16:02
     */
    public function sceneDetail()
    {
        $this->only(['id']);
    }


    /**
     * @notes 切换状态场景
     * @author 段誉
     * @date 2022/3/30 16:03
     */
    public function sceneStatus()
    {
        $this->only(['id', 'status']);
    }


    /**
     * @notes 验证商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/30 16:03
     */
    protected function checkGoods($value, $rule, $data)
    {
        $goods = IntegralGoods::where(['id' => $value])->findOrEmpty();
        if ($goods->isEmpty()) {
            return '积分商品不存在';
        }
        if ($goods['del'] == 1) {
            return '积分商品已被删除';
        }
        return true;
    }



    /**
     * @notes 验证运费
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/30 16:03
     */
    protected function checkExpressMoney($value, $rule, $data)
    {
        // 快递配送  统一运费 运费须大于0
        if ($data['delivery_way'] == IntegralGoodsEnum::DELIVERY_EXPRESS
            && $data['express_type'] == IntegralGoodsEnum::EXPRESS_TYPE_UNIFIED
            && $value <= 0
        ) {
            return '请输入大于0的运费';
        }
        return true;
    }



    /**
     * @notes 验证兑换积分
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/30 16:03
     */
    protected function checkNeedIntegral($value, $rule, $data)
    {
        if ($value <= 0) {
            return '请输入大于0的兑换积分';
        }
        return true;
    }


    /**
     * @notes 验证兑换方式为 积分+金额 时，金额不小于0
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/30 16:04
     */
    protected function checkNeedMoney($value, $rule, $data)
    {
        if ($data['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_HYBRID && $value <= 0) {
            return '请输入大于0的兑换金额';
        }
        return true;
    }



    /**
     * @notes 验证兑换类型为 红包时,红包面额不小于0
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/30 16:04
     */
    protected function checkBalance($value, $rule, $data)
    {
        if ($data['type'] == IntegralGoodsEnum::TYPE_BALANCE && $value <= 0) {
            return '请输入大于0的红包面值';
        }
        return true;
    }


    protected function checkMarketPrice($value)
    {
        if (!empty($value) && $value < 0) {
            return '请输入正确市场价';
        }
        return true;
    }

}