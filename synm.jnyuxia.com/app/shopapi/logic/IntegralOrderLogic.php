<?php

namespace app\shopapi\logic;

use app\common\enum\{AccountLogEnum, IntegralGoodsEnum, IntegralOrderEnum, IntegralOrderRefundEnum, PayEnum};
use app\common\logic\{AccountLogLogic, BaseLogic, IntegralOrderRefundLogic, PayNotifyLogic};
use app\common\service\ConfigService;
use expressage\Kd100;
use expressage\Kdniao;
use app\common\model\{Express, IntegralGoods, IntegralOrder, User, UserAddress};
use app\common\service\FileService;
use think\facade\Db;

/**
 * 积分商城订单
 * Class IntegralOrderLogic
 * @package app\api\logic
 */
class IntegralOrderLogic extends BaseLogic
{

    /**
     * @notes 结算订单
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/3/31 11:34
     */
    public static function settlement($params)
    {
        // 用户地址
        $address = UserAddress::getOneAddress($params['user_id'], $params['address_id'] ?? 0);
        // 积分商品信息
        $goods = IntegralGoods::withoutField(['content'])->findOrEmpty($params['id'])->toArray();

        // 订单需支付总金额
        $orderAmount = 0;
        // 积分商品金额
        $goodsPrice = 0;

        // 兑换方式为纯积分
        if ($goods['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_HYBRID) {
            // 订单需支付总金额
            $goodsPrice = $goods['need_money'] * $params['num'];
            $orderAmount = $goodsPrice;
        }
        // 订单需支付总积分
        $orderIntegral = $goods['need_integral'] * $params['num'];

        // 运费
        $expressPrice = 0;
        // 快递配送 && 快递统一运费 && 运费>0
        if ($goods['delivery_way'] == IntegralGoodsEnum::DELIVERY_EXPRESS
            && $goods['express_type'] == IntegralGoodsEnum::EXPRESS_TYPE_UNIFIED
            && $goods['express_money'] > 0
        ) {
            $orderAmount = $orderAmount + $goods['express_money'];
            $expressPrice = $goods['express_money'];
        }

        return [
            'address' => $address,
            'goods' => $goods,
            'need_pay' => $orderAmount > 0 ? 1 : 0,
            'exchange_way' => $goods['exchange_way'],
            'delivery_way' => $goods['delivery_way'],
            'total_num' => intval($params['num']),
            'express_price' => $expressPrice, // 运费
            'goods_price' => round($goodsPrice, 2), // 商品金额(不包含运费)
            'order_amount' => round($orderAmount, 2), // 订单需要的金额(包含运费)
            'order_integral' => $orderIntegral, // 订单需支付的积分
        ];
    }


    /**
     * @notes 提交订单
     * @param $params
     * @return array|false
     * @author 段誉
     * @date 2022/3/31 14:24
     */
    public static function submitOrder($params)
    {
        Db::startTrans();
        try {
            // 结算详情(支付积分，支付金额)
            $settle = self::settlement($params);
            $settle['goods']['image'] = FileService::setFileUrl($settle['goods']['image']);

            // 提交前验证
            $user = User::findOrEmpty($params['user_id']);
            if ($user['user_integral'] < $settle['order_integral']) {
                throw new \Exception('积分不足');
            }

            if ($settle['total_num'] <= 0) {
                throw new \Exception('请选择商品数量');
            }

            // 提交订单
            $order = IntegralOrder::duokaiCreate([
                'sn' => generate_sn((new IntegralOrder()), 'sn'),
                'user_id' => $params['user_id'],
                'order_source' => $params['terminal'],
                'delivery_way' => $settle['goods']['delivery_way'],
                'exchange_type' => $settle['goods']['type'],
                'exchange_way' => $settle['goods']['exchange_way'],

                'order_amount' => $settle['order_amount'],
                'order_integral' => $settle['order_integral'],
                'total_num' => $settle['total_num'],
                'goods_price' => $settle['goods_price'],
                'express_price' => $settle['express_price'],

                'user_remark' => $params['user_remark'] ?? '',
                'goods_snap' => $settle['goods'],

                'address' => [
                    'contact' => $settle['address']['contact'],
                    'province' => $settle['address']['province_id'],
                    'city' => $settle['address']['city_id'],
                    'district' => $settle['address']['district_id'],
                    'address' => $settle['address']['address'],
                    'mobile' => $settle['address']['mobile'],
                ]
            ]);

            // 扣减应付积分
            if ($settle['order_integral'] > 0) {
                User::where(['id' => $params['user_id']])
                    ->dec('user_integral', $settle['order_integral'])
                    ->update();

                AccountLogLogic::add(
                    $params['user_id'],
                    AccountLogEnum::INTEGRAL_DEC_INTEGRAL_ORDER,
                    AccountLogEnum::DEC,
                    $settle['order_integral'], $order['sn']
                );
            }

            // 兑换方式-积分 且没有运费 扣减积分后 直接支付完成
            if ($settle['goods']['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_INTEGRAL && $settle['order_amount'] <= 0) {
                PayNotifyLogic::handle('integral', $order['sn']);
            }

            Db::commit();

            return ['order_id' => $order['id'], 'type' => 'integral'];

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 订单详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/3/2 10:22
     */
    public static function detail($id)
    {
        $order = IntegralOrder::where(['id' => $id])
            ->withoutField(['content', 'order_source', 'transaction_id', 'refund_amount'])
            ->append(['delivery_address', 'pay_way_desc', 'order_status_desc', 'btns'])
            ->findOrEmpty()->toArray();

        $goodsSnap = $order['goods_snap'];
        unset($order['goods_snap']);

        $order['goods'] = [
            'image' => FileService::getFileUrl($goodsSnap['image']),
            'name' => $goodsSnap['name'],
            'exchange_way' => $goodsSnap['exchange_way'],
            'need_integral' => $goodsSnap['need_integral'],
            'need_money' => $goodsSnap['need_money'],
            'total_num' => $order['total_num'],
        ];
        return $order;
    }


    /**
     * @notes 确认收货
     * @param $id
     * @param $userId
     * @author 段誉
     * @date 2022/3/31 15:04
     */
    public static function confirm($id, $userId)
    {
        //更新订单状态
        IntegralOrder::update([
            'order_status' => IntegralOrderEnum::ORDER_STATUS_COMPLETE,
            'confirm_time' => time(),
        ], ['id' => $id, 'user_id' => $userId]);
    }


    /**
     * @notes 删除订单
     * @param $id
     * @author 段誉
     * @date 2022/3/31 15:06
     */
    public static function del($id)
    {
        IntegralOrder::destroy($id);
    }


    /**
     * @notes 取消订单
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/1 11:27
     */
    public static function cancel($id)
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


    /**
     * @notes 物流轨迹
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/3/31 18:53
     */
    public static function orderTraces($id)
    {
        $field = [
            'o.order_status', 'o.total_num', 'o.confirm_time', 'o.address',
            'o.pay_time', 'o.express_time', 'o.create_time', 'o.goods_snap',
            'd.express_name', 'd.invoice_no', 'd.send_type', 'd.express_id'
        ];

        // 获取订单信息,物流信息
        $order = IntegralOrder::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')->field($field)
            ->join('integral_delivery d', 'd.order_id = o.id')
            ->where(['o.id' => $id])
            ->append(['delivery_address'])
            ->findOrEmpty();

        if ($order->isEmpty() || $order['send_type'] != 1) {
            return [];
        }

        $traces = [
            'order' => [
                'goods_image' => FileService::getFileUrl($order['goods_snap']['image']),
                'goods_count' => $order['total_num'],
                'express_name' => $order['express_name'],
                'invoice_no' => $order['invoice_no'],
                'order_status' => $order['order_status'],
                'send_type' => $order['send_type'],
            ],
            'take' => [
                'contact' => $order['address']['contact'],
                'mobile' => $order['address']['mobile'],
                'address' => $order['delivery_address'],
            ],
            'finish' => [
                'title' => '交易完成',
                'tips' => ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_COMPLETE) ? '订单交易完成' : '',
                'time' => ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_COMPLETE) ? $order['confirm_time'] : '',
            ],
            'delivery' => [
                'title' => '运输中',
                'traces' => self::getTracesData($order)
            ],
            'shipment' => self::getTracesShipment($order),
            'pay' => [
                'title' => '已支付',
                'tips' => '订单支付成功，等待商家发货',
                'time' => $order['pay_time']
            ],
            'buy' => [
                'title' => '已下单',
                'tips' => '订单提交成功',
                'time' => $order['create_time']
            ]
        ];

        return $traces;
    }


    /**
     * @notes 获取物流轨迹数据
     * @param $order
     * @return array|false
     * @author 段誉
     * @date 2022/3/31 18:41
     */
    public static function getTracesData($order)
    {
        // 获取物流查询配置, 发起查询申请
        $expressType = ConfigService::get('logistics_config', 'express_type', '');
        $expressBird = unserialize(ConfigService::get('logistics_config', 'express_bird', ''));
        $expressHundred = unserialize(ConfigService::get('logistics_config', 'express_hundred', ''));

        // (没有物流配置 || 发货方式不是快递配送 || 订单为发货)  不查询快递
        if (empty($expressType)
            || $order['send_type'] != 1
            || $order['order_status'] <= IntegralOrderEnum::ORDER_STATUS_DELIVERY
            || ($expressType === 'express_bird' && empty($expressBird))
            || ($expressType === 'express_hundred' && empty($expressHundred))
        ) {
            return [];
        }

        if ($expressType === 'express_bird') {
            $expressHandle = (new Kdniao($expressBird['ebussiness_id'], $expressBird['app_key']));
            $expressField = 'codebird';
        } else {
            $expressHandle = (new Kd100($expressHundred['customer'], $expressHundred['app_key']));
            $expressField = 'code100';
        }

        //快递编码
        $expressCode = Express::where('id', $order['express_id'])->value($expressField);

        //获取物流轨迹
        if ($expressCode === 'SF' && $expressType === 'express_bird') {
            $expressHandle->logistics($expressCode, $order['invoice_no'], substr($order['address']->mobile, -4));
        } else {
            $expressHandle->logistics($expressCode, $order['invoice_no']);
        }

        $traces = $expressHandle->logisticsFormat();
        if ($traces != false) {
            foreach ($traces as &$item) {
                $item = array_values(array_unique($item));
            }
        }

        return $traces;
    }


    /**
     * @notes 订单物流-待收货信息
     * @param $order
     * @return string[]
     * @author 段誉
     * @date 2022/3/3 17:30
     */
    public static function getTracesShipment($order)
    {
        $shipment = [
            'title' => '已发货',
            'tips' => '',
            'time' => '',
        ];
        //待收货
        if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_GOODS) {
            $shipment['tips'] = '商品已出库';
            $shipment['time'] = date('Y-m-d H:i:s', $order['shipping_time']);
        }
        return $shipment;
    }


}
