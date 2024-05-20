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
namespace app\shopapi\validate;
use app\common\{
    model\Goods,
    enum\GoodsEnum,
    validate\BaseValidate
};



/**
 * 分享验证器
 * Class ShareValidate
 * @package app\shopapi\validate
 */
class ShareValidate extends BaseValidate
{
    protected $rule = [
        'goods_id'      => 'checkGoods',
        'initiate_id'      => 'require',
    ];

    protected $message = [
        'initiate_id.require' => '发起砍价ID缺失'
    ];

    protected $scene = [
        'goods'  =>  ['goods_id'],
        'initiate'  =>  ['initiate_id'],
    ];

    protected function checkGoods($value,$rule,$data){
        $goods = Goods::find($value);
        if(empty($goods)){
            return '商品不存在';
        }
        if(GoodsEnum::STATUS_STORAGE == $goods->status){
            return '商品已下架';
        }
        return true;

    }
}