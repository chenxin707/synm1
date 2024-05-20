<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\platformapi\validate\order;

use app\common\enum\PayEnum;
use app\common\model\SetMealOrder;
use app\common\validate\BaseValidate;
use think\facade\Validate;

class SetMealOrderValidate extends BaseValidate
{
    protected $rule = [
        'set_meal_order_id' => 'require|checkSetMealOrderId',
        'voucher' => 'require|array|max:5',
        'remark' => 'require',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'voucher.require' => '请上传凭证',
        'voucher.array' => '凭证须为数组格式',
        'voucher.max' => '凭证最多上传5张',
        'remark.require' => '请输入备注内容',
    ];

    public function sceneConfirmPay() {
        return $this->only(['set_meal_order_id', 'voucher']);
    }

    public function sceneDetail() {
        return $this->only(['set_meal_order_id']);
    }

    public function sceneRemark() {
        return $this->only(['set_meal_order_id', 'remark']);
    }

    public function checkSetMealOrderId($value, $rule, $data) {
        $setMealOrder = SetMealOrder::withoutGlobalScope()->where('id', $value)->findOrEmpty();
        if ($setMealOrder->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }
}
