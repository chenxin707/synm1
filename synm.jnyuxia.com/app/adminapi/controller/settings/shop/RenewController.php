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

namespace app\adminapi\controller\settings\shop;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\settings\shop\RenewLogic;
use app\adminapi\validate\settings\shop\RenewValidate;
use think\facade\Log;

/**
 * 店铺续费
 */
class RenewController extends BaseAdminController
{
    public array $notNeedLogin = ['notifyOa', 'notifyAli'];

    /**
     * @notes 续费页面
     */
    public function index() {
            $data = RenewLogic::index();
            return $this->data($data);
    }

    /**
     * @notes 续费订单列表
     */
    public function lists() {
        return $this->dataLists();
    }

    /**
     * @notes 获取平台支付方式
     */
    public function payWays() {
        $data = RenewLogic::payWays();
        return $this->data($data);
    }

    /**
     * @notes 获取商户当前套餐
     */
    public function setMeal() {
        $data = RenewLogic::setMeal();
        return $this->data($data);
    }

    /**
     * @notes 提交续费订单
     */
    public function placeOrder() {
        $params = (new RenewValidate())->post()->goCheck('placeOrder');
        $result = RenewLogic::placeOrder($params, $this->adminId);
        if (!is_array($result)) {
            return $this->fail($result);
        }
        return $this->data($result);
    }

    /**
     * @notes 订单详情
     */
    public function detail() {
        $params = (new RenewValidate())->goCheck('detail');
        $data = RenewLogic::detail($params);
        return $this->data($data);
    }

    /**
     * @notes 取消支付
     */
    public function cancel() {
        $params = (new RenewValidate())->post()->goCheck('cancel');
        $result = RenewLogic::cancel($params);
        if ($result !== true) {
            return $this->fail($result);
        }
        return $this->success('取消成功', [], 1, 1);
    }

    /**
     * @notes 获取续费订单支付状态
     */
    public function payStatus() {
        $result = RenewLogic::payStatus(input());
        return $this->data($result);
    }

    /**
     * @notes 确认支付
     */
    public function confirmPay() {
        $params = (new RenewValidate())->post()->goCheck('confirmPay');
        $result = RenewLogic::confirmPay($params);
        if (!is_array($result)) {
            return $this->fail($result);
        }
        return $this->data($result);
    }

    /**
     * @notes 微信扫码支付回调
     */
    public function notifyOa() {
        return RenewLogic::notifyOa();
    }

    /**
     * @notes 支付宝扫码支付回调
     */
    public function notifyAli() {
        $result = RenewLogic::notifyAli(input());
        if (true === $result) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }
}
