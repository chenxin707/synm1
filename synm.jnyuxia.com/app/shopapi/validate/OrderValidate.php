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


use app\common\enum\AfterSaleEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\model\AfterSale;
use app\common\model\Order;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

/**
 * 订单验证
 * Class OrderValidate
 * @package app\shopapi\validate
 */
class OrderValidate extends BaseValidate
{

    protected $rule = [
        'id' => 'require|checkOrder'
    ];


    protected $message = [
        'id.require' => '参数缺失',
    ];


    public function sceneDetail()
    {
        return $this->only(['id']);
    }


    public function sceneCancel()
    {
        return $this->only(['id'])->append('id', 'checkCancel');
    }


    public function sceneConfirm()
    {
        return $this->only(['id'])->append('id', 'checkConfirm');
    }

    public function sceneOrderTraces()
    {
        return $this->only(['id'])->append('id','checkTraces');
    }

    public function sceneDel()
    {
        return $this->only(['id'])->append('id','checkDel');
    }


    //验证订单
    public function checkOrder($value, $rule, $data)
    {
        $order = (new Order())->getUserOrderById($value, $data['user_id']);

        if ($order->isEmpty()) {
            return '订单不存在';
        }

        return true;
    }


    /**
     * @notes 验证订单能否取消
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/8/2 15:48
     */
    public function checkCancel($value, $rule, $data)
    {
        $order = (new Order())->getUserOrderById($value, $data['user_id']);

        //已发货订单不可取消
        if ($order->isEmpty() || $order['order_status'] > OrderEnum::STATUS_WAIT_DELIVERY) {
            return '很抱歉!订单无法取消';
        }

        // 用户未支付时，允许任意取消；已支付时，在允许取消的时间内订单未发货则允许取消；
        if ($order['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY) {
            $ableCancelOrder = ConfigService::get('transaction', 'cancel_unshipped_orders');
            if ($ableCancelOrder == YesNoEnum::NO) {
                return '订单不可取消';
            }
            $configTime = ConfigService::get('transaction', 'cancel_unshipped_orders_times');
            $ableCancelTime = strtotime($order['create_time']) + ($configTime * 60);
            if (time() > $ableCancelTime) {
                return '订单不可取消';
            }
        }

        return true;
    }


    /**
     * @notes 验证能否确认收货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/8/2 15:26
     */
    public function checkConfirm($value, $rule, $data)
    {
        $order = (new Order())->getUserOrderById($value, $data['user_id']);

        if ($order->isEmpty()) {
            return '订单不存在';
        }

        if ($order['order_status'] < OrderEnum::STATUS_WAIT_RECEIVE) {
            return '订单未发货';
        }

        if ($order['order_status'] == OrderEnum::STATUS_FINISH) {
            return '订单已完成';
        }

        return true;
    }

    /**
     * @notes 检查订单是否有物流信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/13 3:56 下午
     */
    public function checkTraces($value, $rule, $data)
    {
        $order = (new Order())->getUserOrderById($value, $data['user_id']);

        if ($order->isEmpty()) {
            return '订单不存在';
        }

        if ($order['order_status'] < OrderEnum::STATUS_WAIT_RECEIVE) {
            return '订单未发货';
        }

        return true;
    }

    /**
     * @notes 检查订单是否可以删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/31 2:36 下午
     */
    public function checkDel($value, $rule, $data)
    {
        $order = Order::where('id',$value)->find()->toArray();

        if ($order['order_status'] != OrderEnum::STATUS_CLOSE) {
            return '订单无法删除';
        }
        //如果在售后中无法删除
        if(AfterSale::where(['order_id'=>$value,'status'=>AfterSaleEnum::STATUS_ING])->find()){
            return '订单正在售后中，无法删除';
        }

        return true;
    }

}