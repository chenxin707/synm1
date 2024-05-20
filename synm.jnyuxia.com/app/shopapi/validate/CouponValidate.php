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


use app\common\validate\BaseValidate;

class CouponValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'source' => 'require',
        'goods' => 'requireIf:source,1|array',
        'cart_ids' => 'requireIf:source,2|array',
    ];

    protected $message = [
        'id.require' => '缺少id参数',
        'id.number'  => 'id参数必须为数字',
        'cart_ids.requireIf'  => '缺少购物车数据',
        'cart_ids.array'  => '购物车数据须为数组',
        'goods.requireIf'  => '缺少商品数据',
        'goods.array'  => '商品数据须为数组',
    ];

    public function sceneReceive()
    {
        return $this->only(['id']);
    }

    public function sceneOrderCoupon()
    {
        return $this->only(['source', 'goods', 'cart_ids']);
    }
}