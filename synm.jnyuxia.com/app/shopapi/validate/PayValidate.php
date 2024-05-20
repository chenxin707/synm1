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

use app\common\enum\PayEnum;
use app\common\validate\BaseValidate;

/**
 * 支付验证
 * Class PayValidate
 * @package app\shopapi\validate
 */
class PayValidate extends BaseValidate
{
    protected $rule = [
        'from'      => 'require',
        'pay_way'   => 'require|in:' . PayEnum::BALANCE_PAY . ',' . PayEnum::WECHAT_PAY . ',' . PayEnum::ALI_PAY. ',' . PayEnum::BYTE_PAY,
        'order_id'  => 'require'
    ];

    protected $message = [
        'from.require'      => '参数缺失',
        'pay_way.require'   => '支付方式参数缺失',
        'pay_way.in'        => '支付方式参数错误',
        'order_id.require'  => '订单参数缺失'
    ];

    /**
     * @notes 获取支付方式场景
     * @author Tab
     * @date 2021/8/28 10:24
     */
    public function scenePayway()
    {
        return $this->only(['from', 'order_id', 'scene'])
            ->append('scene','require');
    }

    public function scenePaystatus()
    {
        return $this->only(['from', 'order_id']);
    }
    /**
     * @notes 获取支付结果场景
     * @return PayValidate
     * @author 段誉
     * @date 2022/4/6 15:12
     */
    public function scenePayresult()
    {
        return $this->only(['from', 'order_id']);
    }
}