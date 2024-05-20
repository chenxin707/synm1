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

namespace app\adminapi\validate\settings\shop;


use app\common\enum\PayEnum;
use app\common\model\SetMeal;
use app\common\model\SetMealOrder;
use app\common\model\SetMealPrice;
use app\common\validate\BaseValidate;

class RenewValidate extends BaseValidate
{
    protected $rule = [
        'pay_way' => 'require|in:' . PayEnum::WECHAT_PAY . ',' . PayEnum::ALI_PAY . ',' . PayEnum::TRANSFER_PAY,
        'set_meal_price_id' => 'require|checkSetMealPriceId',
        'order_id' => 'require|checkOrderId',
        'redirect_url' => 'requireIf:pay_way,' . PayEnum::ALI_PAY,
    ];

    protected $message = [
        'pay_way.require' => '请选择支付方式',
        'pay_way.in' => '支付方式错误',
        'set_meal_price_id.require' => '请选择套餐',
        'redirect_url.requireIf' => '请提供支付成功后的跳转路由',
    ];

    /**
     * @notes 提交续费订单
     */
    public function scenePlaceOrder()
    {

        return $this->only(['pay_way', 'set_meal_price_id', 'redirect_url']);
    }

    /**
     * @notes 确认支付
     */
    public function sceneConfirmPay()
    {

        return $this->only(['pay_way', 'order_id', 'redirect_url']);
    }

    /**
     * @notes 获取详情
     */
    public function sceneDetail() {
        return $this->only(['order_id']);
    }

    /**
     * @notes 取消支付
     */
    public function sceneCancel() {
        return $this->only(['order_id']);
    }

    /**
     * @notes 校验子套餐
     */
    protected  function checkSetMealPriceId($value) {
        $setMealPrice = SetMealPrice::findOrEmpty($value)->toArray();
        if (empty($setMealPrice)) {
            return '您选择的套餐不存在';
        }
        return true;
    }

    /**
     * @notes 校验续费订单
     */
    public function checkOrderId($value) {
        $setMealOrder = SetMealOrder::findOrEmpty($value);
        if ($setMealOrder->isEmpty()) {
            return '续费订单不存在';
        }
        return true;
    }

}
