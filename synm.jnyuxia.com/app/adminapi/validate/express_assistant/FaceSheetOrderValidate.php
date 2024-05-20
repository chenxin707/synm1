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

namespace app\adminapi\validate\express_assistant;

use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\TeamEnum;
use app\common\enum\YesNoEnum;
use app\common\model\Express;
use app\common\model\FaceSheetSender;
use app\common\model\FaceSheetTemplate;
use app\common\model\Order;
use app\common\model\TeamFound;
use app\common\validate\BaseValidate;

/**
 * 打印电子面单验证器
 */
class FaceSheetOrderValidate extends BaseValidate
{
    protected $rule = [
        'order_ids' => 'require|array|checkOrder',
        'template_id' => 'require|checkTemplate',
        'sender_id' => 'require|checkSender',
    ];

    protected $message = [
        'order_ids.require' => '请选择要打印的订单',
        'order_ids.array' => '订单参数须为数组格式',
        'template_id.require' => '请选择电子模板',
        'sender_id.require' => '请选择发件人',
    ];

    /**
     * @notes 校验订单
     * @param $orderIds
     * @author Tab
     * @date 2021/11/22 16:45
     */
    public function checkOrder($orderIds)
    {
        foreach($orderIds as $orderId) {
            $order = Order::findOrEmpty($orderId);
            if ($order->isEmpty()) {
                return '不存在id为' . $orderId . '的订单';
            }
            $order = $order->toArray();
            if ($order['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY) {
                return '订单' . $order['sn'] . '不是待发货状态';
            }
            if ($order['pay_status'] == YesNoEnum::NO) {
                return '订单' . $order['sn'] . '未支付';
            }
            if ($order['delivery_type'] != DeliveryEnum::EXPRESS_DELIVERY) {
                return '订单' . $order['sn'] . '配送方式不是快递发货';
            }
            if ($order['order_type'] == OrderEnum::TEAM_ORDER && !$this->checkTeamFoundOrder($order['team_found_id'])) {
                return '拼团订单:' . $order['sn'] . '需要拼团成功后才能打印';
            }
        }
        return true;
    }

    /**
     * @notes 校验模板
     * @param $templateId
     * @author Tab
     * @date 2021/11/22 16:45
     */
    public function checkTemplate($templateId)
    {
        $template = FaceSheetTemplate::findOrEmpty($templateId);
        if ($template->isEmpty()) {
            return '模板不存在';
        }
        if (!$this->checkExpress($template['express_id'])) {
            return '模板中的快递公司不存在';
        }
        return true;
    }

    /**
     * @notes 校验发件人
     * @param $senderId
     * @author Tab
     * @date 2021/11/22 16:46
     */
    public function checkSender($senderId)
    {
        $sender = FaceSheetSender::findOrEmpty($senderId);
        if ($sender->isEmpty()) {
            return '发件人不存在';
        }
        return true;
    }

    /**
     * @notes 校验拼团订单
     * @param $id
     * @return bool
     * @author Tab
     * @date 2021/11/22 16:59
     */
    public function checkTeamFoundOrder($id)
    {
        $teamFound = TeamFound::findOrEmpty($id);
        if (!$teamFound->isEmpty() && $teamFound->status == TeamEnum::TEAM_FOUND_SUCCESS) {
            // 拼团成功
            return true;
        }
        return false;
    }

    /**
     * @notes 校验快递公司
     * @param $id
     * @return bool
     * @author Tab
     * @date 2021/11/22 17:15
     */
    public function checkExpress($id)
    {
        $express = Express::findOrEmpty($id);
        if ($express->isEmpty()) {
            return false;
        }
        return true;
    }
}