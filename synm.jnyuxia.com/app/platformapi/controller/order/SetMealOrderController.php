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

namespace app\platformapi\controller\order;

use app\platformapi\controller\BasePlatformController;
use app\platformapi\logic\order\SetMealOrderLogic;
use app\platformapi\validate\order\SetMealOrderValidate;

class SetMealOrderController extends BasePlatformController
{
    /**
     * @notes 续费订单列表
     */
    public function lists() {
        return $this->dataLists();
    }

    /**
     * @notes 搜索参数
     */
    public function searchParam() {
        $data = SetMealOrderLogic::searchParam();
        return $this->data($data);
    }

    /**
     * @notes 确认支付
     */
    public function confirmPay() {
        $params = (new SetMealOrderValidate())->post()->goCheck('confirmPay');
        $result = SetMealOrderLogic::confirmPay($params);
        if ($result !== true) {
            return $this->fail($result);
        }
        return $this->success('确认成功', [], 1, 1);
    }

    /**
     * @notes 续费订单详情
     */
    public function detail() {
        $params = (new SetMealOrderValidate())->goCheck('detail');
        $data = SetMealOrderLogic::detail($params);
        return $this->data($data);
    }

    /**
     * @notes 续费订单备注
     */
    public function remark() {
        $params = (new SetMealOrderValidate())->post()->goCheck('remark');
        $result = SetMealOrderLogic::remark($params);
        if ($result !== true) {
            return $this->fail($result);
        }
        return $this->success('备注成功', [], 1, 1);
    }
}
