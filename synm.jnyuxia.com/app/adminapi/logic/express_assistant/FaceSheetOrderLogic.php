<?php
namespace app\adminapi\logic\express_assistant;

use app\common\enum\DeliveryEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Delivery;
use app\common\model\Express;
use app\common\model\FaceSheetSender;
use app\common\model\FaceSheetTemplate;
use app\common\model\Order;
use app\common\model\OrderLog;
use app\common\service\ConfigService;
use app\common\service\express_assistant\Kuaidi100Service;
use think\facade\Db;

class FaceSheetOrderLogic extends BaseLogic
{
    /**
     * @notes 打印电子面单
     * @param $params
     * @author Tab
     * @date 2021/11/22 17:07
     */
    public static function print($params)
    {
        $template = FaceSheetTemplate::findOrEmpty($params['template_id'])->toArray();
        $sender = FaceSheetSender::findOrEmpty($params['sender_id'])->toArray();
        $express = Express::findOrEmpty($template['express_id'])->toArray();

        foreach ($params['order_ids'] as $orderId) {
            $order = Order::with('orderGoods')->findOrEmpty($orderId)->toArray();
            $result = self::singlePrint($order, $template, $sender, $express, $params['admin_id']);
            if ($result !== true) {
                // 打印电子面单出错,中断打印
                self::$error = '订单' . $order['sn'] . '打印出错：' . $result;
                return false;
            }
        }
        return true;
    }

    /**
     * @notes 单条打印
     * @author Tab
     * @date 2021/11/22 17:09
     */
    public static function singlePrint($order, $template, $sender, $express, $adminId)
    {
        Db::startTrans();
        try {
            $goodsName = '';
            $totalWeight = 0;
            foreach($order['orderGoods'] as $item) {
                $totalWeight = round($item['goods_snap']->weight+$totalWeight,2);
                $goodsName .= $item['goods_snap']->goods_name . '(' . $item['goods_snap']->spec_value_str . $item['goods_num'] . '件)\n';
            }

            $type = ConfigService::get('face_sheet', 'type', '');
            if (empty($type)) {
                throw new \Exception('未设置面单类型');
            }
            $data = [
                'order'         => $order,
                'template'      => $template,
                'sender'        => $sender,
                'express'       => $express,
                'totalWeight'   => $totalWeight,
                'remark'        => $goodsName,
            ];
            // 打印电子面单
            $result = (new Kuaidi100Service((int)$type))->print($data);

            // 添加发货记录
            $delivery = self::addDelivery($result, $order, $express, $adminId);

            // 更新订单信息
            self::updateOrder($order, $delivery->id);

            //订单日志
            (new OrderLog())->record([
                'type' => OrderLogEnum::TYPE_SHOP,
                'channel' => OrderLogEnum::SHOP_DELIVERY_ORDER,
                'order_id' => $order['id'],
                'operator_id' => $adminId,
            ]);

            // 消息通知
            event('Notice', [
                'scene_id' => NoticeEnum::ORDER_SHIP_NOTICE,
                'params' => [
                    'user_id' => $order['user_id'],
                    'order_id' => $order['id'],
                    'express_name' => $express['name'],
                    'invoice_no' => $result['data']['kuaidinum'],
                    'ship_time' => date('Y-m-d H:i:s')
                ]
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 添加发货记录
     * @param $order
     * @param $template
     * @param $sender
     * @param $express
     * @param $adminId
     * @author Tab
     * @date 2021/11/23 9:55
     */
    public static function addDelivery($result, $order, $express, $adminId)
    {
        $deliveryData = [
            'order_id' => $order['id'],
            'order_sn' => $order['sn'],
            'user_id' => $order['user_id'],
            'admin_id' => $adminId,
            'contact' => $order['address']->contact,
            'mobile' => $order['address']->mobile,
            'province' => $order['address']->province,
            'city' => $order['address']->city,
            'district' => $order['address']->district,
            'address' => $order['address']->address,
            'express_status' => YesNoEnum::YES,
            'express_id' => $express['id'],
            'express_name' => $express['name'],
            'invoice_no' => $result['data']['kuaidinum'],
            'send_type' => DeliveryEnum::EXPRESS_DELIVERY,
            'remark' => '电子面单发货',
        ];
       return Delivery::duokaiCreate($deliveryData);
    }

    /**
     * @notes 更新订单信息
     * @param $order
     * @param $deliveryId
     * @author Tab
     * @date 2021/11/23 10:11
     */
    public static function updateOrder($order, $deliveryId)
    {
        $order = Order::findOrEmpty($order['id']);
        $order->order_status = OrderEnum::STATUS_WAIT_RECEIVE;
        $order->express_status = DeliveryEnum::SHIPPED;
        $order->express_time = time();
        $order->delivery_id = $deliveryId;
        $order->duokaiSave();
    }
}