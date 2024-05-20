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
namespace app\businessapi\validate;
use app\common\model\Goods;
use app\common\validate\BaseValidate;

/**
 * 商品验证
 * Class GoodsValidate
 * @package app\businessapi\validate
 */
class GoodsValidate  extends BaseValidate
{

    protected $rule = [
        'id'                => 'require|checkGoods',
//        'status'            => 'require|in:1,2',
        'spec_value_list'   => 'require|array|checkSpec',

    ];

    protected $message = [
        'id.require'                => '请选择商品',
        'status.require'            => '请选择商品状态',
        'status.in'                 => '商品状态错误',
        'spec_value_list.require'   => '请选择商品规格',
        'spec_value_list.array'     => '商品规格数据错误'
    ];

    protected function sceneId()
    {
        return $this->only(['id']);
    }

    protected function sceneEdit()
    {
        return $this->only(['id','spec_value_list']);
    }


    protected function checkGoods($value,$rule,$data)
    {
        $goods = Goods::findOrEmpty($value);
        if($goods->isEmpty()){
            return '商品不存在';
        }
        return true;

    }

    protected function checkSpec($value,$rule,$data)
    {
        foreach ($value as $spec)
        {
            if(!isset($spec['id']) || empty($spec['id'])){
                return '商品规格id错误';
            }
            if(empty($spec['sell_price']) || $spec['sell_price'] < 0){
                return '商品售价错误';
            }
            if($spec['cost_price'] < 0){
                return '商品成本价不能小于零';
            }
            if($spec['stock'] < 0){
                return '商品库存不能小于零';
            }

        }
        return true;
    }
}