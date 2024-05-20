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

namespace app\adminapi\validate\order;


use app\common\enum\AfterSaleEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\TeamEnum;
use app\common\enum\YesNoEnum;
use app\common\model\AfterSale;
use app\common\model\Express;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Printer;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

class OrderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'province_id' => 'require',
        'city_id' => 'require',
        'district_id' => 'require',
        'address' => 'require',
        'order_goods_id' => 'require|checkOrderGoodsId',
        'change_price' => 'require|float|gt:0',
        'express_price' => 'require|float',
        'send_type' => 'require|in:1,2',
        'express_id' => 'requireIf:send_type,1|checkExpressId',
        'invoice_no' => 'requireIf:send_type,1|alphaNum'
    ];

    protected $message = [
        'province_id.require'   => '所选地区不能为空',
        'city_id.require'       => '请选择完整地址',
        'district_id.require'   => '请选择完整地址',
        'change_price.gt'   => '修改的价格不能为负数',
        'address.require'       => '详细地址不能为空',
        'express_id.requireIf'   => '物流公司不能为空',
        'invoice_no.requireIf'       => '快递单号不能为空',
        'invoice_no.alphaNum'       => '快递单号只能是字母和数字',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneAddressEdit()
    {
        return $this->only(['id','province_id','city_id','district_id','address'])
            ->append('id','checkAddressEdit');
    }

    public function sceneOrderRemarks()
    {
        return $this->only(['id'])
            ->append('id','array')
            ->remove('id','checkId');
    }

    public function sceneChangePrice()
    {
        return $this->only(['order_goods_id','change_price']);
    }

    public function sceneChangeExpressPrice()
    {
        return $this->only(['order_goods_id','express_price']);
    }

    public function sceneCancel()
    {
        return $this->only(['id'])
            ->append('id','checkCancel');
    }

    public function sceneDelivery()
    {
        return $this->only(['id'])
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

    public function sceneOrderPrint()
    {
        return $this->only(['id'])
            ->remove('id','checkId')
            ->append('id','checkPrint');
    }

    /**
     * @notes 检查订单ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/6 11:36 上午
     */
    public function checkId($value,$rule,$data)
    {
        $order = Order::where('id', $value)->findOrEmpty();
        if ($order->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }

    /**
     * @notes 检查订单是否可以修改地址
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/10 11:37 上午
     */
    public function checkAddressEdit($value,$rule,$data)
    {
        $order = Order::where('id', $value)->findOrEmpty();
        if ($order['express_status'] == 1) {
            return '订单已发货，不可以修改地址';
        }
        return true;
    }

    /**
     * @notes 检查订单商品id是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/10 2:00 下午
     */
    public function checkOrderGoodsId($value,$rule,$data)
    {
        $order_goods = OrderGoods::where('id', $value)->findOrEmpty();
        if ($order_goods->isEmpty()) {
            return '订单商品不存在';
        }
        return true;
    }

    /**
     * @notes 检查订单是否可以取消
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/10 4:18 下午
     */
    public function checkCancel($value,$rule,$data)
    {
        $order = Order::where('id', $value)->findOrEmpty();
        if ($order->isEmpty()) {
            return '订单不存在';
        }
        if ($order['order_status'] != OrderEnum::STATUS_WAIT_PAY && $order['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY) {
            return '订单不允许取消';
        }

        return true;
    }

    /**
     * @notes 检查物流公司是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/10 5:29 下午
     */
    public function checkExpressId($value,$rule,$data)
    {
        $order = Express::where('id', $value)->findOrEmpty();
        if ($order->isEmpty()) {
            return '物流公司不存在';
        }
        return true;
    }

    /**
     * @notes 检查订单是否可以发货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/10 5:59 下午
     */
    public function checkDelivery($value,$rule,$data)
    {
        $order = Order::where('id', $value)->findOrEmpty();
        if(OrderEnum::VIRTUAL_ORDER == $order['order_type']){
            return true;
        }
        if ($order['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY || $order['express_status'] != DeliveryEnum::NOT_SHIPPED) {
            return '订单不允许发货';
        }
        if ($order['order_type'] == OrderEnum::TEAM_ORDER && $order['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS) {
            return '该订单正在拼团中还不能发货';
        }
        if(!isset($data['send_type']) || !in_array($data['send_type'],[1,2])){
            return '发货类型错误';
        }
        if(1 == $data['send_type']){
            if((!isset($data['invoice_no'])  || empty($data['invoice_no']))) {
                return '请输入单号';
            }
            if(!isset($data['express_id']) || empty($data['express_id'])){
                return '请选择物流公司';
            }
            $this->checkExpressId($data['express_id'],[],[]);
        }

        $order_goods = OrderGoods::where(['order_id'=>$order['id']])->select()->toArray();
        foreach ($order_goods as $goods) {
            $after_sale = AfterSale::where(['order_goods_id' => $goods['id'], 'order_id' => $goods['order_id']])->findOrEmpty();
            if (!$after_sale->isEmpty() && $after_sale->status == AfterSaleEnum::STATUS_ING) {
                return '订单商品：'.$goods['goods_name'].' 处于售后中，无法发货';
            }
        }

        return true;
    }

    /**
     * @notes 检查订单是否可以确认收货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 10:19 上午
     */
    public function checkConfirm($value,$rule,$data)
    {
        $order = Order::where('id', $value)->findOrEmpty();
        if ($order['order_status'] != OrderEnum::STATUS_WAIT_RECEIVE || $order['express_status'] != DeliveryEnum::SHIPPED) {
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
     * @author ljj
     * @date 2021/8/13 11:20 上午
     */
    public function checkLogistics($value,$rule,$data)
    {
        $order = Order::where('id', $value)->findOrEmpty();
        if ($order['express_status'] != DeliveryEnum::SHIPPED) {
            return '订单未发货，暂无物流信息';
        }
        return true;
    }

    /**
     * @notes 校验打印机
     * @return bool|string
     * @author Tab
     * @date 2021/11/16 9:56
     */
    public function checkPrint()
    {
       $printer = Printer::where('status', YesNoEnum::YES)->findOrEmpty();
       if ($printer->isEmpty()) {
            return '请先添加打印机并设置为启动状态';
       }
       return true;
    }
}