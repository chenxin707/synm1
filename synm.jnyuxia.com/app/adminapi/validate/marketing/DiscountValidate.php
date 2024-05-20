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
namespace app\adminapi\validate\marketing;

use app\common\model\Goods;
use app\common\validate\BaseValidate;

/**
 * 会员折扣验证器
 * Class DiscountValidate
 * @package app\adminapi\validate\marketing
 */
class DiscountValidate extends BaseValidate
{

    protected $rule = [
        'goods_ids'         => 'require|array|checkGoods',
        'goods_id'          => 'require|checkGoods',
        'is_discount'       => 'require|in:0,1',
        'discount_rule'     => 'require|in:1,2',
        'level_goods_item'  => 'require|array',
    ];


    protected $message = [
        'goods_ids.require'         => '请选择参与商品',
        'goods_ids.array'           => '数据错误',
        'goods_id.require'          => '请选择商品',
        'is_discount.require'       => '请选择是否参与折扣',
        'is_discount.in'            => '折扣错误',
        'discount_rule.require'     => '请选择折扣规则',
        'discount_rule.in'          => '折扣规则错误',
        'level_goods_item.require'  => '商品规格不能为空',
        'level_goods_item.array'    => '商品规格数据错误',
    ];

    /**
     * @notes 验证参与折扣
     * @return DiscountValidate
     * @author cjhao
     * @date 2022/5/5 16:32
     */
    protected function sceneJoin()
    {
        return $this->only(['goods_ids']);
    }

    protected function sceneDetail()
    {
        return $this->only(['goods_id']);
    }

    protected function sceneSetDiscount()
    {
        return $this->only(['goods_id', 'is_discount', 'discount_rule', 'level_goods_item']);
    }



    protected function checkGoods($value, $rule, $data)
    {

        $goods = Goods::where(['id'=>$value])->column('id');
        if(!is_array($value)){
            $value = [$value];
        }
        foreach ($value as $goodsId){
            if(!in_array($goodsId,$goods)){
                return '商品数据错误，请刷新页面';
            }
        }
        return true;


    }

}