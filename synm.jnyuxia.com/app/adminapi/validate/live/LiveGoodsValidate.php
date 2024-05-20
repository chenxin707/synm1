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
namespace app\adminapi\validate\live;

use app\common\validate\BaseValidate;

/**
 * 直播商品验证
 * Class LiveGoodsValidate
 * @package app\adminapi\validate\live
 */
class LiveGoodsValidate extends BaseValidate
{
    protected $rule = [
        'goods_id'      => 'require',
        'name'          => 'require|length:0,14',
        'image'         => 'require',
        'price_type'    => 'require|in:1,2,3',
        'price'         => 'require',
        'price2'        => 'requireIf:price_type,2|requireIf:price_type,3',
        'url'           => 'require',
    ];
    protected $message = [
        'name.require'          => '请输入商品名称',
        'name.length'           => '商品名称不能超过14个汉字',
        'price_type.require'    => '请输入价格形式',
        'price_type.in'         => '价格形式类型错误',
        'price.require'         => '请输入价格',
        'price2.requireIf'      => '请输入价格',
        'url.require'           => '请输入商品链接'
    ];

    protected function sceneAdd(){
        return $this->remove(['goods_id'=>'require']);
    }

    protected function sceneDel(){
        return $this->only(['goods_id']);
    }


}