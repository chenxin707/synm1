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

namespace app\adminapi\logic\integral;


use app\common\enum\IntegralDeliveryEnum;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\IntegralOrderRefundEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\PayEnum;
use app\common\logic\BaseLogic;
use app\common\logic\IntegralOrderRefundLogic;
use app\common\model\Express;
use app\common\model\IntegralDelivery;
use app\common\model\IntegralOrder;
use app\common\service\ConfigService;
use app\common\service\FileService;
use expressage\Kd100;
use expressage\Kdniao;
use think\facade\Db;

class IntegralOrderLogic extends BaseLogic
{
    /**
     * @notes 兑换订单详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/3/31 11:23 上午
     */
    public function detail($id)
    {
        $result = IntegralOrder::with(['user'])
            ->where('id', $id)
            ->append(['delivery_address', 'pay_status_desc', 'order_status_desc','exchange_type_desc','pay_way_desc','admin_btns'])
            ->findOrEmpty()
            ->toArray();

        $result['confirm_time'] = empty($result['confirm_time']) ? '-' : date('Y-m-d H:i:s', $result['confirm_time']);
        $result['goods_snap']['image'] = FileService::getFileUrl($result['goods_snap']['image']);

        return $result;
    }

    /**
     * @notes 发货
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/3/31 2:29 下午
     */
    public function delivery($params)
    {
        Db::startTrans();
        try {
            $order = IntegralOrder::where(['id'=>$params['id']])->findOrEmpty()->toArray();

            $express = Express::where('id',$params['express_id'])->findOrEmpty()->toArray();

            //添加发货单
            $delivery_data = [
                'order_id' => $order['id'],
                'order_sn' => $order['sn'],
                'user_id' => $order['user_id'],
                'admin_id' => $params['admin_id'],
                'consignee' => $order['address']['contact'],
                'mobile' => $order['address']['mobile'],
                'province' => $order['address']['province'],
                'city' => $order['address']['city'],
                'district' => $order['address']['district'],
                'address' => $order['address']['address'],
                'invoice_no' => $params['invoice_no'],
                'send_type' => 1,
                'express_id' => $params['express_id'],
                'express_name' => $express['name'],
                'express_status' => 1,
                'create_time' => time(),
            ];
            IntegralDelivery::duokaiCreate($delivery_data);

            //更新订单信息
            IntegralOrder::update([
                'express_time' => time(),
                'express_status' => IntegralOrderEnum::SHIPPING_FINISH,
                'order_status' => IntegralOrderEnum::ORDER_STATUS_GOODS,
            ],['id'=>$order['id']]);

            // 消息通知
            event('Notice', [
                'scene_id' => NoticeEnum::ORDER_SHIP_NOTICE,
                'params' => [
                    'user_id' => $order['user_id'],
                    'order_id' => $order['id'],
                    'express_name' => $express['name'],
                    'invoice_no' => $params['invoice_no'],
                    'ship_time' => date('Y-m-d H:i:s'),
                    'order_type' => 'integral'
                ]
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
//            return $e->getMessage();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 发货信息
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/31 2:37 下午
     */
    public function deliveryInfo($params)
    {
        $result = IntegralOrder::where('id', $params['id'])
            ->append(['delivery_address'])
            ->findOrEmpty()
            ->toArray();
        $exchangeNeed = $result['goods_snap']['need_integral'] . '积分';
        if ($result['goods_snap']['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_HYBRID) {
            $exchangeNeed .= $result['goods_snap']['need_money'] . '元';
        }

        $actualPay = $result['order_integral'] . '积分';
        if ($result['order_amount'] > 0) {
            $actualPay .= $result['order_amount'] . '元';
        }

        $result['order_goods'] = [
            'name' => $result['goods_snap']['name'],
            'image' => FileService::getFileUrl($result['goods_snap']['image']),
            'market_price' => $result['goods_snap']['market_price'],
            'exchange_need' => $exchangeNeed,
            'goods_num' => $result['total_num'],
            'need_integral' => $result['goods_snap']['need_integral'],
            'need_money' => $result['goods_snap']['need_money'],
            'express_price' => $result['express_price'],
            'actual_payment' => $actualPay,
            'order_amount' => $result['order_amount'],
            'order_integral' => $result['order_integral'],
        ];
        unset($result['goods_snap']);

        //获取物流公司
        $result['express'] = Express::field('id,name')->select()->toArray();

        return $result;
    }

    /**
     * @notes 确认收货
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/3/31 4:39 下午
     */
    public function confirm($params)
    {
        IntegralOrder::update([
            'order_status' => IntegralOrderEnum::ORDER_STATUS_COMPLETE,
            'confirm_time' => time(),
        ],['id'=>$params['id']]);

        return true;
    }

    /**
     * @notes 物流信息
     * @param $params
     * @return mixed
     * @author 段誉
     * @date 2022/4/1 15:01
     */
    public function logistics($params)
    {
        $order = IntegralOrder::withoutGlobalScope()->aliasSid('o')->alias('o')->field([
                'o.id', 'o.express_time', 'o.express_status', 'o.address', 'o.goods_snap',
                'o.order_integral', 'o.order_amount', 'o.total_num','o.express_price',
                'd.send_type', 'd.express_name', 'd.invoice_no', 'd.express_id'
            ])
            ->join('integral_delivery d', 'o.id = d.order_id')
            ->where('o.id', $params['id'])
            ->find()
            ->toArray();

        $exchangeNeed = $order['goods_snap']['need_integral'] . '积分';
        if ($order['goods_snap']['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_HYBRID) {
            $exchangeNeed .= $order['goods_snap']['need_money'] . '元';
        }

        $actualPay = $order['order_integral'] . '积分';
        if ($order['order_amount'] > 0) {
            $actualPay .= $order['order_amount'] . '元';
        }

        $order['order_goods'] = [
            'name' => $order['goods_snap']['name'],
            'image' => FileService::getFileUrl($order['goods_snap']['image']),
            'market_price' => $order['goods_snap']['market_price'],
            'exchange_need' => $exchangeNeed,
            'goods_num' => $order['total_num'],
            'need_integral' => $order['goods_snap']['need_integral'],
            'need_money' => $order['goods_snap']['need_money'],
            'express_price' => $order['express_price'],
            'actual_payment' => $actualPay,
            'order_amount' => $order['order_amount'],
            'order_integral' => $order['order_integral'],
        ];
        unset($order['goods_snap']);

        //发货方式
        $order['send_type_desc'] = IntegralDeliveryEnum::getSendTypeDesc($order['send_type']);

        if ($order['send_type'] == IntegralDeliveryEnum::NO_EXPRESS) {
            $order['traces'] = ['无需物流'];
            return $order;
        }

        //查询物流信息
        $expressType = ConfigService::get('logistics_config', 'express_type', '');
        $expressBird = unserialize(ConfigService::get('logistics_config', 'express_bird', ''));
        $expressHundred = unserialize(ConfigService::get('logistics_config', 'express_hundred', ''));


        if (empty($expressType) || $order['express_status'] != IntegralOrderEnum::SHIPPING_NO) {
            $order['traces'] = ['暂无物流信息'];
            return $order;
        }

        //快递配置设置为快递鸟时
        if ($expressType === 'express_bird') {
            $expressage = (new Kdniao($expressBird['ebussiness_id'], $expressBird['app_key']));
            $expressField = 'codebird';
        } elseif ($expressType === 'express_hundred') {
            $expressage = (new Kd100($expressHundred['customer'], $expressHundred['app_key']));
            $expressField = 'code100';
        }

        //快递编码
        $express_code = Express::where('id', $order['express_id'])->value($expressField);

        //获取物流轨迹
        if ($express_code === 'SF' && $expressType === 'express_bird') {
            $expressage->logistics($express_code, $order['invoice_no'], substr($order['address']->mobile, -4));
        } else {
            $expressage->logistics($express_code, $order['invoice_no']);
        }

        $order['traces'] = $expressage->logisticsFormat();
        if ($order['traces'] == false) {
            $order['traces'] = ['暂无物流信息'];
        } else {
            foreach ($order['traces'] as &$item) {
                $item = array_values(array_unique($item));
            }
        }

        return $order;
    }

    /**
     * @notes 取消订单
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/1 15:40
     */
    public function cancel($id)
    {
        Db::startTrans();
        try {
            $order = IntegralOrder::findOrEmpty($id);

            // 更新订单状态, 退回库存, 扣减销量
            IntegralOrderRefundLogic::cancelOrder($id);

            // 退回已支付积分
            IntegralOrderRefundLogic::refundOrderIntegral($id);

            // 退回订单已支付积分或已支付金额
            if ($order['pay_status'] == PayEnum::ISPAID) {
                IntegralOrderRefundLogic::refundOrderAmount($id);
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();

            IntegralOrderRefundLogic::addRefundLog(
                $order, $order['order_amount'],
                IntegralOrderRefundEnum::STATUS_FAIL,
                $e->getMessage()
            );
            return false;
        }
    }
}