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
namespace app\businessapi\logic;

use app\common\enum\AfterSaleEnum;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\GoodsEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\enum\VerificationEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\AfterSaleGoods;
use app\common\model\Delivery;
use app\common\model\Express;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderLog;
use app\common\model\Region;
use app\common\model\SelffetchShop;
use app\common\model\Verification;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\RegionService;
use app\shopapi\logic\TeamLogic;
use expressage\Kd100;
use expressage\Kdniao;
use think\facade\Db;

/**
 * 订单逻辑类
 * Class OrderLogic
 * @package app\businessapi\logic
 */
class OrderLogic  extends BaseLogic
{
    /**
     * @notes 订单详情
     * @param $params
     * @return array
     * @author 段誉
     * @date 2021/8/2 20:59
     */
    public function detail($params)
    {
        $result = (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->with(['order_goods' => function ($query) {
            $query->field([
                'id', 'order_id', 'goods_id', 'item_id', 'goods_snap',
                'goods_name', 'goods_price', 'goods_num', 'total_price', 'total_pay_price'
            ])->append(['goods_image', 'spec_value_str','original_price'])->hidden(['goods_snap']);
        },'user'])
            ->where(['id' => $params['id']])
            ->append(['businesse_btn', 'delivery_address', 'cancel_unpaid_orders_time', 'show_pickup_code','order_terminal_desc','pay_way_desc','delivery_type_desc'])
            ->hidden(['user_id', 'order_terminal', 'transaction_id', 'delete_time', 'update_time'])
            ->findOrEmpty()->toArray();

        //订单类型
        $result['order_type_desc'] = ($result['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '自提订单' : OrderEnum::getOrderTypeDesc($result['order_type']);

        //订单状态描述
        $result['order_status_desc'] = ($result['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY && $result['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '待取货' : OrderEnum::getOrderStatusDesc($result['order_status']);
        if ($result['order_type'] == OrderEnum::TEAM_ORDER && $result['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS) {
            $result['order_status_desc'] = ($result['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY) ? TeamEnum::getStatusDesc($result['is_team_success']) : OrderEnum::getOrderStatusDesc($result['order_status']);
        }

        //自提门店
        $result['selffetch_shop'] = SelffetchShop::where('id', $result['selffetch_shop_id'])
            ->field('id,name,province,city,district,address,business_start_time,business_end_time')
            ->append(['detailed_address'])
            ->hidden(['province', 'city', 'district', 'address'])
            ->find();

        //地址  省市区分隔开
        $result['address']->province = Region::where('id', $result['address']->province)->value('name');
        $result['address']->city = Region::where('id', $result['address']->city)->value('name');
        $result['address']->district = Region::where('id', $result['address']->district)->value('name');

        //订单商品原价总价
        $result['total_original_price'] = 0;
        $result['verification_time'] = '';
        if(DeliveryEnum::SELF_DELIVERY == $result['delivery_type']){
            $result['verification_time'] = date('Y-m-d H:i:s',Verification::where(['order_id'=>$result['id']])->value('create_time'));
        }
        //订单商品售后按钮处理
        foreach ($result['order_goods'] as &$goods) {
            $goods['after_sale_btn'] = 0;//售后按钮关闭
            $after_sale = AfterSale::where(['order_goods_id' => $goods['id'], 'order_id' => $params['id']])->findOrEmpty();
            $after_sale_goods = AfterSaleGoods::where(['order_goods_id' => $goods['id'], 'after_sale_id' => $after_sale['id']])->findOrEmpty();
            $goods['after_sale_id'] = $after_sale_goods['id'] ?? 0;

            if ($result['order_status'] == OrderEnum::STATUS_FINISH && $result['after_sale_deadline'] > time() && $after_sale->isEmpty()) {
                $goods['after_sale_btn'] = 1;//售后按钮开启
            }
            if ($result['order_status'] == OrderEnum::STATUS_FINISH && $result['after_sale_deadline'] > time() && $after_sale['status'] == AfterSaleEnum::STATUS_ING) {
                $goods['after_sale_btn'] = 2;//售后中
            }
            if ($result['order_status'] == OrderEnum::STATUS_FINISH && $result['after_sale_deadline'] > time() && $after_sale['status'] == AfterSaleEnum::STATUS_SUCCESS) {
                $goods['after_sale_btn'] = 3;//售后成功
            }
            if ($result['order_status'] == OrderEnum::STATUS_FINISH && $result['after_sale_deadline'] > time() && $after_sale['status'] == AfterSaleEnum::STATUS_FAIL) {
                $goods['after_sale_btn'] = 4;//售后失败
            }

            $goods['total_original_price'] = $goods['original_price'] * $goods['goods_num'];
            $result['total_original_price'] += $goods['original_price'] * $goods['goods_num'];
        }
        $result['total_original_price'] = round($result['total_original_price'],2);
        return $result;
    }


    /**
     * @notes 修改发货地址
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2023/2/17 10:20
     */
    public static function addressEdit($params)
    {
        $order = Order::find($params['id']);

        $address = [
            'contact'   => $order['address']->contact,
            'province'  => $params['province'],
            'city'      => $params['city'],
            'district'  => $params['district'],
            'address'   => $params['address'],
            'mobile'    => $order['address']->mobile,
        ];
        $order->address = $address;
        $order->duokaiSave();

        $change_address= RegionService::getAddress(
            [
                $params['province_id'] ?? '',
                $params['city_id'] ?? '',
                $params['district_id'] ?? ''
            ],
            $params['address'] ?? '',
            );

        //订单日志
        (new OrderLog())->record([
            'type' => OrderLogEnum::TYPE_SHOP,
            'channel' => OrderLogEnum::SHOP_ADDRESS_EDIT,
            'order_id' => $params['id'],
            'operator_id' => $params['admin_id'],
            'content' => '商家修改收货地址为：'.$change_address,
        ]);

    }

    /**
     * @notes 取消订单
     * @param $params
     * @return bool
     * @author cjhao
     * @date 2023/2/17 9:48
     */
    public function cancel($params)
    {
        Db::startTrans();
        try {

            $order = Order::find($params['id']);

            if ($order['order_type'] == OrderEnum::TEAM_ORDER) {

                TeamLogic::signFailTeam($order['id']);

            } else {

                //更新订单表
                $order->order_status = OrderEnum::STATUS_CLOSE;
                $order->cancel_time = time();

                //TODO  处于已支付状态的发起整单售后
                if ($order->pay_status == PayEnum::ISPAID) {
                    AfterSaleService::orderRefund([
                        'order_id' => $params['id'],
                        'scene' => AfterSaleLogEnum::SELLER_CANCEL_ORDER
                    ]);
                }
                $order->duokaiSave();

                $returnInventory = ConfigService::get('transaction', 'return_inventory');
                if ($returnInventory) {
                    // 需退还库存
                    AfterSaleService::returnInventory(['order_id' => $order['id']]);
                }

                $returnCoupon = ConfigService::get('transaction', 'return_coupon');
                if ($returnCoupon) {
                    // 需退还优惠券
                    AfterSaleService::returnCoupon($order);
                }

                //订单日志
                (new OrderLog())->record([
                    'type' => OrderLogEnum::TYPE_SHOP,
                    'channel' => OrderLogEnum::SHOP_CANCEL_ORDER,
                    'order_id' => $params['id'],
                    'operator_id' => $params['admin_id'],
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 查看物流
     * @param $params
     * @return array[]
     * @author ljj
     * @date 2021/8/13 6:07 下午
     */
    public static function orderTraces($params)
    {
        // 获取订单信息,物流信息
        $order = (new Order)->duokaiWithoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('order_goods og', 'o.id = og.order_id')
            ->join('goods g', 'og.goods_id = g.id')
            ->join('delivery d', 'd.order_id = o.id')
            ->field('g.image,o.order_status,d.express_name,d.invoice_no,o.total_num,d.contact,d.mobile,o.address,o.confirm_take_time,d.send_type,d.express_id,o.express_time,o.pay_time,o.create_time')
            ->append(['delivery_address'])
            ->where(['o.id' => $params['id']])
            ->find()
            ->toArray();

        // 判断是否为快递物流发货, 无物流的不用发货
        $traces = [];
        $shipment = [];
        if ($order['send_type'] == DeliveryEnum::EXPRESS && $order['order_status'] > OrderEnum::STATUS_WAIT_DELIVERY) {

            $shipment = [
                'title' => '已发货',
                'tips' => '商品已出库',
                'time' => $order['express_time'],
            ];

            // 获取物流查询配置, 发起查询申请
            $express_type = ConfigService::get('logistics_config', 'express_type', '');
            $express_bird = unserialize(ConfigService::get('logistics_config', 'express_bird', ''));
            $express_hundred = unserialize(ConfigService::get('logistics_config', 'express_hundred', ''));

            if (!empty($express_type) && !empty($express_bird) && !empty($express_hundred)) {

                $express_field = 'code';
                if ($express_type === 'express_bird') {
                    $expressage = (new Kdniao($express_bird['ebussiness_id'], $express_bird['app_key']));
                    $express_field = 'codebird';
                } elseif ($express_type === 'express_hundred') {
                    $expressage = (new Kd100($express_hundred['customer'], $express_hundred['app_key']));
                    $express_field = 'code100';
                }

                //快递编码
                $express_code = Express::where('id', $order['express_id'])->value($express_field);

                //获取物流轨迹
                if ($express_code === 'SF' && $express_type === 'express_bird') {
                    $expressage->logistics($express_code, $order['invoice_no'], substr($order['address']->mobile, -4));
                } else {
                    $expressage->logistics($express_code, $order['invoice_no']);
                }

                $traces = $expressage->logisticsFormat();
                if ($traces != false) {
                    foreach ($traces as &$item) {
                        $item = array_values(array_unique($item));
                    }
                }
            }
        }

        // 组装数据返回
        return [
            'order' => [
                'goods_image' => FileService::getFileUrl($order['image']),
                'goods_count' => $order['total_num'],
                'express_name' => $order['express_name'],
                'invoice_no' => $order['invoice_no'],
                'order_status' => $order['order_status'],
                'send_type' => $order['send_type'],
            ],
            'take' => [
                'contact' => $order['contact'],
                'mobile' => $order['mobile'],
                'address' => $order['delivery_address'],
            ],
            'finish' => [
                'title' => '交易完成',
                'tips' => ($order['order_status'] == OrderEnum::STATUS_FINISH) ? '订单交易完成' : '',
                'time' => ($order['order_status'] == OrderEnum::STATUS_FINISH) ? $order['confirm_take_time'] : '',
            ],
            'delivery' => [
                'title' => '运输中',
                'traces' => $traces
            ],
            'shipment' => $shipment,
            'pay' => [
                'title' => '已支付',
                'tips' => '订单支付成功，等待商家发货',
                'time' => $order['pay_time']
            ],
            'buy' => [
                'title' => '已下单',
                'tips' => '订单提交成功',
                'time' => $order['create_time']
            ],
        ];
    }


    /**
     * @notes 确认收货
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/11 10:37 上午
     */
    public function confirm($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            $order = Order::find($params['id']);
            $order->order_status = OrderEnum::STATUS_FINISH;
            $order->confirm_take_time = time();
            $order->after_sale_deadline = self::getAfterSaleDeadline();
            $order->duokaiSave();

            //订单日志
            (new OrderLog())->record([
                'type' => OrderLogEnum::TYPE_SHOP,
                'channel' => OrderLogEnum::SHOP_CONFIRM_ORDER,
                'order_id' => $params['id'],
                'operator_id' => $params['admin_id'],
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 获取当前售后
     * @return float|int
     * @author ljj
     * @date 2021/9/1 3:09 下午
     */
    public static function getAfterSaleDeadline()
    {
        //是否关闭维权
        $afterSale = ConfigService::get('transaction', 'after_sales');
        //可维权时间
        $afterSaleDays = ConfigService::get('transaction', 'after_sales_days');

        if ($afterSale == YesNoEnum::NO) {
            $afterSaleDeadline = time();
        } else {
            $afterSaleDeadline = ($afterSaleDays * 24 * 60 * 60) + time();
        }

        return $afterSaleDeadline;
    }


    /**
     * @notes 核销订单列表
     * @param $params
     * @return Order|array|string|\think\Model
     * @author cjhao
     * @date 2023/2/17 11:42
     */
    public function getVerificationOrder($params)
    {
        $order = (new Order())
            ->where(['pickup_code' => $params['code']])
            ->with(['order_goods' => function ($query) {
                $query->field('goods_id,order_id,goods_snap,goods_name,goods_price,goods_num,is_comment,original_price')
                    ->append(['goods_image', 'spec_value_str'])
                    ->hidden(['goods_snap']);
            }])
            ->append(['consignee'])
            ->hidden(['address'])
            ->field(['id', 'sn', 'user_id', 'order_type', 'order_status', 'total_num', 'order_amount', 'delivery_type', 'pay_status', 'address', 'create_time','verification_status'])
            ->order(['id' => 'desc'])
            ->findOrEmpty();
        if($order->isEmpty()) {
            return '订单不存在';
        }
        return $order->toArray();
    }


    /**
     * @notes 订单核销
     * @param $params
     * @return bool
     * @author cjhao
     * @date 2023/2/17 11:58
     */
    public function verification($params)
    {
        try {
            //校验是否有售后
            if ($params['confirm'] != 1) {
                $order_goods = OrderGoods::where(['order_id'=>$params['id']])->select()->toArray();
                foreach ($order_goods as $goods) {
                    $after_sale = AfterSale::where(['order_goods_id' => $goods['id'], 'order_id' => $goods['order_id'],'status'=>[AfterSaleEnum::STATUS_ING,AfterSaleEnum::STATUS_SUCCESS]])->findOrEmpty();
                    if (!$after_sale->isEmpty()) {
                        return ['msg'=>'有商品处于售后中或已售后成功，请谨慎操作'];
                    }
                }
            }

            $order = Order::find($params['id']);

            //添加核销记录
            $snapshot = [
                'sn' => $params['admin_info']['account'],
                'name' => $params['admin_info']['name']
            ];
            $verification = new Verification;
            $verification->order_id = $params['id'];
            $verification->selffetch_shop_id = $order['selffetch_shop_id'];
            $verification->handle_id = $params['admin_info']['admin_id'];
            $verification->verification_scene = VerificationEnum::TYPE_ADMIN;
            $verification->snapshot = json_encode($snapshot);
            $verification->duokaiSave();

            //更新订单状态
            $order->order_status = OrderEnum::STATUS_FINISH;
            $order->verification_status = OrderEnum::WRITTEN_OFF;
            $order->confirm_take_time = time();
            $order->after_sale_deadline = self::getAfterSaleDeadline();
            $order->duokaiSave();

            //订单日志
            (new OrderLog())->record([
                'type' => OrderLogEnum::TYPE_SHOP,
                'channel' => OrderLogEnum::SHOP_VERIFICATION,
                'order_id' => $params['id'],
                'operator_id' => $params['admin_info']['admin_id'],
            ]);

            return true;

        } catch (\Exception $e) {
            //错误
            self::$error = $e->getMessage();
            return false;
        }

    }

    /**
     * @notes 获取地址
     * @param $params
     * @author cjhao
     * @date 2023/2/17 17:36
     */
    public function getAddress($params)
    {

        $orderAddress = Order::where(['id'=>$params['id']])->value('address');
        $orderAddress = json_decode($orderAddress,true);
        $orderAddress['province_name'] = Region::where('id', $orderAddress['province'])->value('name');
        $orderAddress['city_name'] = Region::where('id', $orderAddress['city'])->value('name');
        $orderAddress['district_name'] = Region::where('id', $orderAddress['district'])->value('name');
        return $orderAddress;

    }

    /**
     * @notes 发货
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/10 6:25 下午
     */
    public function delivery($params)
    {
        Db::startTrans();
        try {
            $order = Order::find($params['id']);
            //虚拟发货和实物发货
            if(OrderEnum::VIRTUAL_ORDER != $order['order_type']){

                $express_name = ($params['send_type'] == 1) ? Express::where('id',$params['express_id'])->value('name') : '';
                $invoice_no = ($params['send_type'] == 1) ? $params['invoice_no'] : '';
                //添加发货单记录
                $delivery = new Delivery;
                $delivery->order_id = $params['id'];
                $delivery->order_sn = $order['sn'];
                $delivery->user_id = $order['user_id'];
                $delivery->admin_id = $params['admin_id'];
                $delivery->contact = $order['address']->contact;
                $delivery->mobile = $order['address']->mobile;
                $delivery->province = $order['address']->province;
                $delivery->city = $order['address']->city;
                $delivery->district = $order['address']->district;
                $delivery->address = $order['address']->address;
                $delivery->express_status = ($params['send_type'] == 1) ? DeliveryEnum::SHIPPED : DeliveryEnum::NOT_SHIPPED;
                $delivery->express_id = ($params['send_type'] == 1) ? $params['express_id'] : '';
                $delivery->express_name = $express_name;
                $delivery->invoice_no = $invoice_no;
                $delivery->send_type = $params['send_type'];
                $delivery->remark = $params['remark'] ?? '';
                $delivery->duokaiSave();

                //更新订单表
                $order->order_status = OrderEnum::STATUS_WAIT_RECEIVE;
                $order->express_status = DeliveryEnum::SHIPPED;
                $order->express_time = time();
                $order->delivery_id = $delivery->id;
                $order->duokaiSave();

            }else{
                $orderGoods = OrderGoods::where(['order_id'=>$order['id']])->find();
                //自动完成订单
                $order->order_status = OrderEnum::STATUS_FINISH;
                if(GoodsEnum::AFTER_DELIVERY_HANDOPERSTION == $orderGoods->goods_snap->after_delivery){
                    $order->order_status = OrderEnum::STATUS_WAIT_RECEIVE;
                }
                //更新订单表
                $order->express_status = DeliveryEnum::NOT_SHIPPED;
                $order->delivery_content = $params['delivery_content'] ?? '';
                $order->express_time = time();
                $order->delivery_id = 0;
                if(GoodsEnum::AFTER_DELIVERY_AUTO == $orderGoods->goods_snap->after_delivery){
                    $order->confirm_take_time = time();
                }
                $order->duokaiSave();

            }


            //订单日志
            (new OrderLog())->record([
                'type' => OrderLogEnum::TYPE_SHOP,
                'channel' => OrderLogEnum::SHOP_DELIVERY_ORDER,
                'order_id' => $params['id'],
                'operator_id' => $params['admin_id'],
            ]);

            // 消息通知
            event('Notice', [
                'scene_id' => NoticeEnum::ORDER_SHIP_NOTICE,
                'params' => [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'express_name' => $express_name ?? '无需快递',
                    'invoice_no' => $invoice_no ?? '',
                    'ship_time' => date('Y-m-d H:i:s')
                ]
            ]);


            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 获取发货地址接口
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2023/2/20 11:33
     */
    public function getDeliverInfo($params)
    {
        $order = Order::where(['id' => $params['id']])->findOrEmpty();
        $result = [
            'express_list'      => [],
            'delivery_content'  => '',
        ];
        if(OrderEnum::VIRTUAL_ORDER == $order->order_type){
            $result['delivery_content'] = $order->delivery_content;
            if(OrderEnum::STATUS_WAIT_DELIVERY == $order['order_status'] && empty($order['delivery_content'])){
                $orderGoods = OrderGoods::where(['order_id' => $params['id']])->field('goods_snap')->findOrEmpty();
                $result['delivery_content'] = $orderGoods['goods_snap']->delivery_content;
            }

        }else{
            $result['express_list'] = Express::field('id,name')->select()->toArray();
        }

        return $result;

    }


}