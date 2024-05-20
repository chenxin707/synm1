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

namespace app\adminapi\validate\integral;



use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\model\IntegralOrder;
use app\common\validate\BaseValidate;

class IntegralOrderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'send_type' => 'require|in:1,2',
        'express_id' => 'requireIf:send_type,1',
        'invoice_no' => 'requireIf:send_type,1|alphaNum'
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'express_id.requireIf'   => '物流公司不能为空',
        'invoice_no.requireIf'       => '快递单号不能为空',
        'invoice_no.alphaNum'       => '快递单号只能是字母和数字',
    ];


    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneDelivery()
    {
        return $this->only(['id','send_type','express_id','invoice_no'])
            ->append('id','checkDelivery');
    }

    public function sceneDeliveryInfo()
    {
        return $this->only(['id']);
    }

    public function sceneConfirm()
    {
        return $this->only(['id'])
            ->append('id','checkConfirm');
    }

    public function sceneLogistics()
    {
        return $this->only(['id'])
            ->append('id','checkLogistics');
    }

    public function sceneCancel()
    {
        return $this->only(['id'])
            ->append('id','checkCancel');
    }



    /**
     * @notes 检验订单能否发货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/3/31 12:09 下午
     */
    public function checkDelivery($value,$rule,$data)
    {
        $result = IntegralOrder::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        if ($result['delivery_way'] == 0) {
            return '订单无需快递';
        }
        if ($result['express_status'] == 1) {
            return '订单已发货';
        }
        if ($result['order_status'] != IntegralOrderEnum::ORDER_STATUS_DELIVERY) {
            return '订单状态不正确，无法发货';
        }
        return true;
    }

    /**
     * @notes 检验订单是否可以确认收货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/3/31 4:04 下午
     */
    public function checkConfirm($value,$rule,$data)
    {
        $result = IntegralOrder::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        if ($result['order_status'] != IntegralOrderEnum::ORDER_STATUS_GOODS) {
            return '订单不允许确认收货';
        }
        return true;
    }


    /**
     * @notes 检查订单是否已发货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/1 15:00
     */
    public function checkLogistics($value,$rule,$data)
    {
        $order = IntegralOrder::findOrEmpty($value);
        if ($order['express_status'] == IntegralOrderEnum::SHIPPING_NO) {
            return '订单未发货，暂无物流信息';
        }
        return true;
    }


    /**
     * @notes 校验取消
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/1 15:37
     */
    public function checkCancel($value, $rule, $data)
    {
        $order = IntegralOrder::findOrEmpty($value);
        if ($order->isEmpty()) {
            return '订单不存在';
        }

        // 商品类型为红包的不可取消
        if ($order['goods_snap']['type'] == IntegralGoodsEnum::TYPE_BALANCE) {
            return '类型为红包的订单不可取消';
        }

        if ($order['order_status'] >= IntegralOrderEnum::ORDER_STATUS_GOODS) {
            return '已发货订单不可取消';
        }

        return true;
    }
}