<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\shopapi\logic\Order;


use app\common\enum\AfterSaleEnum;
use app\common\enum\AfterSaleLogEnum;
use app\common\enum\BargainEnum;
use app\common\enum\CouponEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\FreeShippingEnum;
use app\common\enum\GoodsEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\DiscountLogic;
use app\common\model\AfterSale;
use app\common\model\AfterSaleGoods;
use app\common\model\BargainInitiate;
use app\common\model\Cart;
use app\common\model\Coupon;
use app\common\model\CouponList;
use app\common\model\Express;
use app\common\model\FreeShipping;
use app\common\model\FreeShippingOrder;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderLog;
use app\common\model\Region;
use app\common\model\SeckillGoodsItem;
use app\common\model\SelffetchShop;
use app\common\model\TeamActivity;
use app\common\model\TeamFound;
use app\common\model\TeamGoods;
use app\common\model\TeamGoodsItem;
use app\common\model\TeamJoin;
use app\common\model\User;
use app\common\logic\BaseLogic;
use app\common\model\UserAddress;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\shopapi\logic\BargainLogic;
use app\shopapi\logic\TeamLogic;
use expressage\Kd100;
use expressage\Kdniao;
use think\Exception;
use think\facade\Db;
use think\facade\Validate;

/**
 * 订单逻辑
 * Class OrderLogic
 * @package app\shopapi\logic
 */
class OrderLogic extends BaseLogic
{

    /**
     * 下单用户
     * @var
     */
    protected static $user;

    /**
     * 订单类型
     * @var int
     */
    protected static $OrderType = OrderEnum::NORMAL_ORDER;


    /**
     * 订单数量
     * @var int
     */
    protected static $totalNum = 0;


    /**
     * 订单金额
     * @var array
     */
    protected static $orderPrice = [
        'total_goods_price' => 0,//订单商品金额
        'express_price'     => 0,//运费
        'total_amount'      => 0,//订单金额
        'order_amount'      => 0,//订单实付金额
        'discount_amount'   => 0,//优惠金额
        'member_amount'     => 0,//会员折扣价
        'total_goods_original_price'  => 0,//订单商品原价总价
    ];


    /**
     * @notes 当前下单用户
     * @param $userId
     * @return array
     * @author 段誉
     * @date 2021/7/23 15:52
     */
    public static function setOrderUser($userId)
    {
        self::$user = User::findOrEmpty($userId)->toArray();
        return self::$user;
    }


    /**
     * @notes 订单结算详情
     * @param $params
     * @return array|bool
     * @author 段誉
     * @date 2021/7/23 15:52
     */
    public static function settlement($params)
    {
        try {
            //设置订单类型
            self::$OrderType = $params['order_type'];

            //设置用户信息
            $user = self::setOrderUser($params['user_id']);

            //设置用户地址
            $userAddress = UserAddress::getOneAddress($params['user_id'], $params['address_id'] ?? 0);

            //获取商品信息
            $goodsLists = self::getOrderGoodsData($params);

            //判断是否需要地址
            $is_address = 1;
            foreach ($goodsLists as $goods) {
                //非虚拟商品必填地址
                if ($goods['type'] != GoodsEnum::GOODS_VIRTUAL) {
                    break;
                }
                $is_address = $goods['is_address'];
            }
            if ($is_address == 0) {
                $userAddress = [];
            }

            //计算运费(自提订单不需要运费)
            if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) {
                self::$orderPrice['express_price'] = 0;
            } else {
                $express_data = FreightLogic::calculateFreight($goodsLists, $userAddress);
                self::$orderPrice['express_price'] = $express_data['express_price'];
                $goodsLists = $express_data['goods'];
            }

            // 普通订单使用优惠券
            if (!empty($params['coupon_list_id']) && in_array(self::$OrderType,[OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER])) {
                $discountData = OrderCouponLogic::calculateCouponDiscount($goodsLists, $params['coupon_list_id']);
                self::$orderPrice['discount_amount'] = $discountData['discount'];
                self::$orderPrice['order_amount'] -= $discountData['discount'];
                $goodsLists = $discountData['goods'];

                //优惠券金额
                $coupon_discount = $discountData['coupon_discount'];
            }

            // 订单金额
            self::$orderPrice['total_amount'] += self::$orderPrice['express_price'];

            //订单应付金额
            self::$orderPrice['order_amount'] += self::$orderPrice['express_price'];

            $result = [
                'terminal'          => $params['terminal'],
                'delivery_type'     => intval($params['delivery_type']),
                'delivery_type_desc'=> DeliveryEnum::getDeliveryTypeDesc($params['delivery_type']),
                'cart_id'           => $params['cart_id'] ?? [],
                'order_type'        => self::$OrderType,
                'coupon_list_id'    => intval($params['coupon_list_id'] ?? 0),
                'total_num'         => self::$totalNum,

                'total_goods_price' => round(self::$orderPrice['total_goods_price'], 2),
                'total_amount'      => round(self::$orderPrice['total_amount'], 2),
                'order_amount'      => round(self::$orderPrice['order_amount'], 2),
                'discount_amount'   => round(self::$orderPrice['discount_amount'], 2),
                'member_amount'     => round(self::$orderPrice['member_amount'],2),
                'express_price'     => round(self::$orderPrice['express_price'], 2),
                'total_goods_original_price'     => round(self::$orderPrice['total_goods_original_price'], 2),

                'user_id'        => $user['id'],
                'user_money'        => $user['user_money'],
                'user_remark'       => $params['user_remark'] ?? '',
                'address'           => $userAddress,

                'goods'             => $goodsLists,

                'selffetch_shop_id' => $params['selffetch_shop_id'] ?? '',
                'selffetch_info'    => [],
                'contact'           => $params['contact'] ?? '',
                'mobile'            => $params['mobile'] ?? '',

                'is_address'        => $is_address,
                'coupon_discount'  => $coupon_discount ?? '',
            ];

            //门店自提显示上次提货人信息
            if($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY){
                $selffetch_info = Order::where(['user_id'=>$user['id'],'delivery_type'=>DeliveryEnum::SELF_DELIVERY])
                    ->field('address,selffetch_shop_id')
                    ->findOrEmpty()->toArray();

                //上一次提货人
                if($selffetch_info){
                    $result['selffetch_info'] = [
                        'selffetch_shop_id' => $selffetch_info['selffetch_shop_id'] ?? '',
                        'contact'           => $selffetch_info['address']->contact ?? '',
                        'mobile'            => $selffetch_info['address']->mobile ?? '',
                    ];
                }

            }
            // 营销活动附加参数: 秒杀、拼团、砍价
            switch($params['order_type']) {
                // 拼团
                case OrderEnum::TEAM_ORDER:
                    $result['team_id'] = $params['team_id'];
                    $result['found_id'] = $params['found_id'] ?? null;
                    $teamFound = (new TeamFound())
                        ->where(['id'=>$result['found_id'], 'user_id'=>$params['user_id']])
                        ->findOrEmpty();
                    if (!$teamFound->isEmpty()) {
                        self::$error = '不允许参与自己开的团';
                        return false;
                    }
                    break;
                // 秒杀
                case OrderEnum::SECKILL_ORDER:
                    $result['seckill_id'] = $params['seckill_id'];
                    break;
                // 砍价
                case OrderEnum::BARGAIN_ORDER:
                    $result['initiate_id'] = $params['initiate_id'];
                    break;
            }

            // 判断是否符合包邮活动条件
            if (self::isFreeShipping($result)) {
                $result['order_amount'] = $result['order_amount'] - $result['express_price'];
                $result['total_amount'] = $result['total_amount'] - $result['express_price'];
                $result['express_price'] = 0;
            }

            return $result;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 是否符合包邮活动条件
     */
    public static function isFreeShipping(&$params) {
        $params['is_free_shipping'] = false;
        if (empty($params['address'])) {
            return false;
        }
        // 获取当前时间进行中的包邮活动
        $activity = FreeShipping::where([
            ['start_time', '<=', time()],
            ['end_time', '>', time()],
            ['status', '=', FreeShippingEnum::ING],
        ])->find();
        if (!$activity) {
            // 没有包邮活动
            return false;
        }
        if ($activity->getData('condition_type') == FreeShippingEnum::BY_AMOUNT) {
            // 去除优惠金额
            $threshold = $params['total_goods_price'] - $params['discount_amount'];
        } else {
            $threshold = $params['total_num'];
        }

        $nationThreshold = 0;
        foreach($activity->region as $item) {
            if ($item->region_id == "100000") {
                // 全国区域留在最后判断
                $nationThreshold = $item->threshold;
                continue;
            }

            if (strpos($item->region_id, $params['address']->district_id) !== false) {
                if ($threshold >= $item->threshold) {
                    $params['is_free_shipping'] = true;
                    $params['free_shipping_id'] = $activity->id;
                    return true;
                } else {
                    return false;
                }
            }

            if (strpos($item->region_id, $params['address']->city_id) !== false) {
                if ($threshold >= $item->threshold) {
                    $params['is_free_shipping'] = true;
                    $params['free_shipping_id'] = $activity->id;
                    return true;
                } else {
                    return false;
                }
            }

            if (strpos($item->region_id, $params['address']->province_id) !== false) {
                if ($threshold >= $item->threshold) {
                    $params['is_free_shipping'] = true;
                    $params['free_shipping_id'] = $activity->id;
                    return true;
                } else {
                    return false;
                }
            }
        }

        if ($threshold >= $nationThreshold) {
            $params['is_free_shipping'] = true;
            $params['free_shipping_id'] = $activity->id;
            return true;
        }
        return false;
    }



    /**
     * @notes 提交订单前验证
     * @param $params
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/23 15:53
     */
    public static function submitBeforeCheck($params)
    {
        //配送方式为快递配送时,检测地址
        if (empty($params['address']) && $params['delivery_type'] == DeliveryEnum::EXPRESS_DELIVERY && $params['is_address'] == 1) {
            throw new \Exception('请选择收货地址');
        }

        //配送方式为门店自提时
        if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY && empty($params['selffetch_shop_id'])) {
            throw  new \Exception('自提门店不能为空');
        }
        if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY && !empty($params['selffetch_shop_id'])) {
            $selffetch_shop = SelffetchShop::where('id',$params['selffetch_shop_id'])->findOrEmpty();
            if ($selffetch_shop->isEmpty()) {
                throw  new \Exception('自提门店不存在');
            }
        }
        if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY && empty($params['contact'])) {
            throw  new \Exception('取货人不能为空');
        }
        if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY && empty($params['mobile'])) {
            throw  new \Exception('联系电话不能为空');
        }

        if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY && !Validate::mobile($params['mobile'])) {
            throw  new \Exception('联系电话格式不正确');
        }

        // 校验商品数量
        $limit_arr = [];
        foreach ($params['goods'] as $goods) {
            if ($goods['goods_num'] <= 0) {
                throw  new \Exception('请选择商品' . ($goods['goods_name'] ?? '') . '数量');
            }

            $limit_arr[$goods['goods_id']]['goods_num'] = ($limit_arr[$goods['goods_id']]['goods_num'] ?? 0) + $goods['goods_num'];
            $limit_arr[$goods['goods_id']]['limit_type'] = $goods['limit_type'];
            $limit_arr[$goods['goods_id']]['limit_value'] = $goods['limit_value'];
            $limit_arr[$goods['goods_id']]['goods_name'] = $goods['goods_name'];
            $limit_arr[$goods['goods_id']]['goods_id'] = $goods['goods_id'];
        }

        //校验限购商品
        foreach ($limit_arr as $limit_val) {
            if ($limit_val['limit_type'] == GoodsEnum::LIMIT_TYPE_USER) {
                $order_goods_num = OrderGoods::withoutGlobalScope()
                    ->aliasSid('og')
                    ->alias('og')
                    ->join('order o', 'o.id = og.order_id')
                    ->where(['og.goods_id'=>$limit_val['goods_id'],'o.order_status'=>[OrderEnum::STATUS_WAIT_PAY,OrderEnum::STATUS_WAIT_DELIVERY,OrderEnum::STATUS_WAIT_RECEIVE,OrderEnum::STATUS_FINISH],'o.user_id'=>$params['user_id'],'o.order_type'=>[OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER]])
                    ->sum('og.goods_num');
                if (($order_goods_num + $limit_val['goods_num']) > $limit_val['limit_value']) {
                    throw  new \Exception('商品：' . ($limit_val['goods_name'] ?? '') . ' 超过限购数量');
                }
            }
            if ($limit_val['limit_type'] == GoodsEnum::LIMIT_TYPE_ORDER) {
                if ($limit_val['goods_num'] > $limit_val['limit_value']) {
                    throw  new \Exception('商品：' . ($limit_val['goods_name'] ?? '') . ' 超过限购数量');
                }
            }
        }

        //验证订单商品是否支持对应的配送方式
        $is_express = ConfigService::get('delivery_type', 'is_express', 1);
        $is_selffetch = ConfigService::get('delivery_type', 'is_selffetch', 0);
        $item_ids = implode(',',array_column($params['goods'],'item_id'));
        $goods_ids = implode(',',GoodsItem::where('id','in', $item_ids)->column('goods_id'));
        $goods = Goods::where('id','in', $goods_ids)->select();
        $goods_name = [];
        //门店自提
        if ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) {
            if ($is_selffetch == 0) {
                throw  new \Exception('系统未开启门店自提配送方式');
            }
            foreach ($goods as $val) {
                //虚拟商品不支持门店自提
                if(GoodsEnum::GOODS_VIRTUAL == $val['type']){
                    throw new \Exception($val['name'].'不支持门店自提');
                }
                if ($val['is_selffetch'] == 0) {
                    $goods_name[] = $val['name'];
                }
            }
        }elseif ($params['delivery_type'] == DeliveryEnum::EXPRESS_DELIVERY) { //快递配送
            if ($is_express == 0) {
                throw  new \Exception('系统未开启快递配送方式');
            }
            foreach ($goods as $val) {
                if ($val['is_express'] == 0 && GoodsEnum::GOODS_REALITY  ==  $val['type']) {
                    $goods_name[] = $val['name'];
                }
            }
        }
        if (!empty($goods_name)) {
            throw new \Exception(implode('、',$goods_name) . '不支持'.DeliveryEnum::getDeliveryTypeDesc($params['delivery_type']) . ',请重新选择配送方式');
        }
    }


    /**
     * @notes 提交订单
     * @param $params
     * @return array|bool
     * @author 段誉
     * @date 2021/7/23 15:53
     */
    public static function submitOrder($params)
    {
        Db::startTrans();
        try {
            //提交前验证
            self::submitBeforeCheck($params);

            //删除购物车
            self::delCartByOrder($params);

            //下单扣除库存
            self::decStock($params['goods']);

            //提交订单
            $order = self::addOrder($params, self::$user['id']);

            //下单增加商品销量
            self::incSale($params['goods']);

            //有使用优惠券时更新coupon_list
            if ($params['coupon_list_id'] > 0){
                self::handleCouponByOrder($params['coupon_list_id'], $order['id']);
            }

            // 营销活动下单后的操作
            switch($params['order_type']) {
                // 拼团
                case OrderEnum::TEAM_ORDER:
                    self::teamAfter($params, $order);
                    break;
                // 秒杀
                case OrderEnum::SECKILL_ORDER:
                    self::seckillAfter($params, $order);
                    break;
                // 砍价
                case OrderEnum::BARGAIN_ORDER:
                    self::bargainAfter($params, $order);
                    break;
            }


            // 判断是否为包邮活动订单
            if ($params['is_free_shipping']) {
                FreeShippingOrder::duokaiCreate([
                    'free_shpping_id' => $params['free_shipping_id'],
                    'order_id' => $order['id'],
                    'amount' => $order['order_amount']
                ]);
            }

            //订单日志
            (new OrderLog())->record([
                'type' => OrderLogEnum::TYPE_USER,
                'channel' => OrderLogEnum::USER_ADD_ORDER,
                'order_id' => $order['id'],
                'operator_id' => self::$user['id'],
            ]);

            //提交事务
            Db::commit();
            return ['order_id' => $order['id'], 'type' => 'order'];
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 更新订单优惠券状态
     * @param $coupon_list_id
     * @param $order_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/9/14 11:39 上午
     */
    public static function handleCouponByOrder($coupon_list_id,$order_id)
    {
        $coupon_list = CouponList::find($coupon_list_id);
        $coupon_list->order_id = $order_id;
        $coupon_list->status = CouponEnum::USE_STATUS_OK;
        $coupon_list->use_time = time();
        $coupon_list->update_time = time();
        $coupon_list->duokaiSave();
    }


    /**
     * @notes 扣减库存
     * @param $goodLists
     * @throws \Exception
     * @author 段誉
     * @date 2021/8/9 17:47
     */
    public static function decStock($goodLists)
    {
        foreach ($goodLists as $goods) {
            $goodsItem = GoodsItem::where(['id'=> $goods['item_id']])->findOrEmpty()->toArray();
            if ($goodsItem['stock'] < $goods['goods_num']) {
                throw new \Exception('商品库存不足');
            }
            //更新商品总库存
            Goods::duokaiUpdate(['total_stock' => ['dec', $goods['goods_num']]], ['id' => $goods['goods_id']]);
            //更新规格库存
            GoodsItem::duokaiUpdate(['stock' => ['dec', $goods['goods_num']]], ['id' => $goods['item_id']]);
        }
    }

    /**
     * @notes 下单增加销量
     * @param $goodLists
     * @throws \Exception
     * @author ljj
     * @date 2021/9/22 5:30 下午
     */
    public static function incSale($goodLists)
    {
        foreach ($goodLists as $goods) {
            Goods::duokaiUpdate(['sales_num' => ['inc', $goods['goods_num']]], ['id' => $goods['goods_id']]);
        }
    }


    /**
     * @notes 删除购物车(购物车下单情况)
     * @param $params
     * @author 段誉
     * @date 2021/7/23 15:54
     */
    public static function delCartByOrder($params)
    {
        if (!empty($params['cart_id'])) {
            Cart::where(['id' => $params['cart_id'], 'user_id' => self::$user['id']])->useSoftDelete('delete_time', time())->delete();
        }
    }


    /**
     * @notes 添加订单
     * @param $params
     * @param $user_id
     * @author 段誉
     * @date 2021/8/9 17:42
     */
    public static function addOrder($params, $user_id)
    {
        $order = Order::duokaiCreate([
            'sn'                => generate_sn((new Order()), 'sn'),
            'order_type'        => $params['order_type'],
            'user_id'           => $user_id,
            'order_terminal'    => $params['terminal'],
            'coupon_list_id'    => $params['coupon_list_id'],
            'total_num'         => $params['total_num'],
            'total_amount'      => $params['total_amount'],
            'goods_price'       => $params['total_goods_price'],
            'order_amount'      => $params['order_amount'],
            'express_price'     => $params['express_price'],
            'discount_amount'   => $params['discount_amount'],
            'member_amount'     => $params['member_amount'],
            'user_remark'       => $params['user_remark'],
            'address'           => [
                'contact'       => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? $params['contact'] : ($params['address']['contact'] ?? ''),
                'province'      => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '' : ($params['address']['province_id'] ?? ''),
                'city'          => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '' : ($params['address']['city_id'] ?? ''),
                'district'      => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '' : ($params['address']['district_id'] ?? ''),
                'address'       => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '' : ($params['address']['address'] ?? ''),
                'mobile'        => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? $params['mobile'] : ($params['address']['mobile'] ?? ''),

            ],
            'delivery_type'     => OrderEnum::VIRTUAL_ORDER == $params['order_type'] ? DeliveryEnum::DELIVERY_VIRTUAL : $params['delivery_type'],
            'pickup_code'       => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? create_number_sn((new Order()), 'pickup_code',6) : null,
            'selffetch_shop_id' => ($params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? $params['selffetch_shop_id'] : 0,
        ]);

        $goodsData = [];
        foreach ($params['goods'] as $goods) {
            //商品实付价格
            $totalPayPrice = $goods['sub_price'] - ($goods['discount_price'] ?? 0) + ($goods['express_price'] ?? 0);
            $totalPayPrice = $totalPayPrice <= 0 ? 0 : $totalPayPrice;

            $goodsData[] = [
                'order_id'          => $order['id'],
                'goods_id'          => $goods['goods_id'],
                'item_id'           => $goods['item_id'],
                'goods_name'        => $goods['goods_name'],
                'goods_num'         => $goods['goods_num'],
                'goods_price'       => $goods['sell_price'],//商品价格单价(未扣减优惠和积分价格)
                'total_price'       => $goods['sub_price'],
                'total_pay_price'   => $totalPayPrice,//实际支付商品金额(扣除优惠金额、加上运费)
                'spec_value_ids'    => $goods['spec_value_ids'],
                'discount_price'    => $goods['discount_price'] ?? 0,//优惠券优惠金额
                'original_price'    => $goods['original_price'] ?? 0,//商品原始价格
                'member_price'      => $goods['member_price'] ?? 0,//商品会员价格
                'goods_snap'        => $goods,
                'express_price'     => $goods['express_price'] ?? 0,//运费
            ];
        }
        (new OrderGoods())->duokaiSaveAll($goodsData);

        return $order;
    }


    /**
     * @notes 商品信息
     * @param $params
     * @return array
     * @author 段誉
     * @date 2021/7/23 15:54
     */
    public static function getOrderGoodsData($params)
    {
        // 购买车下单
        if ($params['source'] == 'cart') {
            $params['goods'] = Cart::where([
                ['id', 'in', $params['cart_id']],
                ['user_id', '=', $params['user_id']],
            ])->field('item_id,goods_id,goods_num')->select()->toArray();
        }

        // 砍价商品信息提取
        if ($params['order_type'] == OrderEnum::BARGAIN_ORDER) {
            $params['goods'] = self::initiateGoods($params);
        }

        $itemIds = array_column($params['goods'], 'item_id');

        $field = [
            'gi.id' => 'item_id', 'gi.image' => 'item_image',
            'gi.spec_value_str', 'spec_value_ids', 'gi.sell_price',
            'gi.volume', 'gi.stock', 'gi.weight', 'g.id' => 'goods_id',
            'g.name' => 'goods_name','g.type', 'g.status', 'g.delete_time', 'g.image',
            'g.express_type', 'g.express_money', 'g.express_template_id',
            'g.is_express','g.is_selffetch','g.is_express','g.is_virtualdelivery',
            'g.after_pay','g.after_delivery','g.delivery_content', 'g.code', 'g.is_address', 'g.limit_type', 'g.limit_value'
        ];


        $goodsData = (new Goods())->duokaiWithoutGlobalScope()
            ->aliasSid('g')
            ->alias('g')
            ->join('goods_item gi', 'gi.goods_id = g.id')
            ->where('gi.id', 'in', $itemIds)
            ->column($field, 'gi.id');

        //处理图片路径
        foreach ($goodsData as &$val) {
            $val['image'] = trim($val['image']) ? FileService::getFileUrl($val['image']) : '';
        }

        $goodsLists = self::getOrderGoodsLists($params, $goodsData);

        return $goodsLists;
    }


    /**
     * @notes 结算商品列表
     * @param $goods
     * @param $goodsData
     * @return array
     * @author 段誉
     * @date 2021/7/23 15:55
     */
    public static function getOrderGoodsLists($params, $goodsData)
    {
        $goods = $params['goods'];
        $goodsIds = array_column($goodsData, 'goods_id');
        $levelGoodsItem = DiscountLogic::getGoodsDiscount(self::$user['id'], $goodsIds);
        $goodsLists = [];

        foreach ($goods as $k => $item) {
            //删除没找到商品信息的商品
            if (!isset($goodsData[$item['item_id']])) {
                unset($goods[$k]);
                continue;
            }

            //组装商品数据
            $goodsInfo = $goodsData[$item['item_id']];

            //当前商品是否被删除或下架
            if ($goodsInfo['delete_time'] > 0 || $goodsInfo['status'] != 1) {
                throw new \Exception($goodsInfo['goods_name'] . '已下架,请重新选择购买');
            }

            //当前库存是否足够
            if ($item['goods_num'] > $goodsInfo['stock']) {
                throw new \Exception($goodsInfo['goods_name'] . '(' . $goodsInfo['spec_value_str'] . ')' . '库存不足' . $item['goods_num'] . '件，请重新选择购买');
            }
            $goodsInfo['member_price'] = 0;
            if(in_array(self::$OrderType,[OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER])){
                //会员折扣
                $goodsInfo['member_price'] = $levelGoodsItem[$goodsInfo['goods_id']][$goodsInfo['item_id']]['discount_price'] ?? '';
            }
            //记录原价
            $goodsInfo['original_price'] = $goodsInfo['sell_price'];
            // 获取不同订单类型的规格单价
            $goodsInfo['sell_price'] = self::getSellPrice($params,$goodsInfo);

            $goodsInfo['sub_price'] = round($goodsInfo['sell_price'] * $item['goods_num'], 2);
            $goodsInfo['goods_num'] = intval($item['goods_num']);
            $goodsInfo['total_original_price'] = round($goodsInfo['original_price'] * $goodsInfo['goods_num'], 2);//商品原价合计
            $goodsLists[] = $goodsInfo;

            self::$totalNum += $item['goods_num'];
            self::$orderPrice['total_goods_price'] += $goodsInfo['sub_price'];


            //普通商品，计算折扣金额
            if(in_array(self::$OrderType,[OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER])){
                $memberAmount = round(($goodsInfo['original_price'] - $goodsInfo['sell_price']) * $item['goods_num'],2);
                self::$orderPrice['member_amount'] += $memberAmount;
            }
            //订单商品原价总价
            self::$orderPrice['total_goods_original_price'] += $goodsInfo['total_original_price'];
        }
        //订单金额
        self::$orderPrice['total_amount'] = self::$orderPrice['total_goods_price'];
        //订单应付金额
        self::$orderPrice['order_amount'] = self::$orderPrice['total_goods_price'];
        return $goodsLists;
    }


    /**
     * @notes 订单详情
     * @param $params
     * @return array
     * @author 段誉
     * @date 2021/8/2 20:59
     */
    public static function getDetail($params)
    {
        $result = (new Order())->with(['order_goods' => function($query) {
            $query->field([
                'id', 'order_id', 'goods_id', 'item_id', 'goods_snap', 'original_price',
                'goods_name', 'goods_price', 'goods_num', 'total_price', 'total_pay_price', 'express_price', 'change_price', 'discount_price'=>'coupon_discount', 'member_price', 'integral_price'
            ])->append(['goods_image', 'spec_value_str'])->hidden(['goods_snap']);
        }])
            ->where(['id' => $params['id'], 'user_id' => $params['user_id']])
            ->append(['btn', 'delivery_address', 'cancel_unpaid_orders_time','show_pickup_code'])
            ->hidden(['user_id', 'order_terminal', 'transaction_id', 'delete_time', 'update_time'])
            ->findOrEmpty()->toArray();

        //订单类型
        $result['order_type_desc'] = ($result['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '自提订单' : OrderEnum::getOrderTypeDesc($result['order_type']);

        //订单状态描述
        $result['order_status_desc'] = ($result['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY && $result['delivery_type'] == DeliveryEnum::SELF_DELIVERY) ? '待取货' : OrderEnum::getOrderStatusDesc($result['order_status']);
        if ($result['order_type'] == OrderEnum::TEAM_ORDER && $result['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS){
            $result['order_status_desc'] = ($result['order_status'] == OrderEnum::STATUS_WAIT_DELIVERY) ? TeamEnum::getStatusDesc($result['is_team_success']) : OrderEnum::getOrderStatusDesc($result['order_status']);
        }

        //自提门店
        $result['selffetch_shop'] = SelffetchShop::where('id',$result['selffetch_shop_id'])
            ->field('id,name,province,city,district,address,business_start_time,business_end_time')
            ->append(['detailed_address'])
            ->hidden(['province','city','district','address'])
            ->find();

        //地址  省市区分隔开
        $result['address']->province = Region::where('id',$result['address']->province)->value('name');
        $result['address']->city = Region::where('id',$result['address']->city)->value('name');
        $result['address']->district = Region::where('id',$result['address']->district)->value('name');

        //订单商品原价总价
        $result['total_original_price'] = 0;

        //订单商品售后按钮处理
        foreach ($result['order_goods'] as &$goods) {
            $goods['after_sale_btn'] = 0;//售后按钮关闭
            $after_sale = AfterSale::where(['order_goods_id'=>$goods['id'],'order_id'=>$params['id']])->findOrEmpty();
            $after_sale_goods = AfterSaleGoods::where(['order_goods_id'=>$goods['id'],'after_sale_id'=>$after_sale['id']])->findOrEmpty();
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


            //批量下单后，过了取消订单的时间，可对其中一件商品进行退款操作
            if (in_array($result['order_status'],[OrderEnum::STATUS_WAIT_DELIVERY,OrderEnum::STATUS_WAIT_RECEIVE])) {
                $ableCancelOrder = ConfigService::get('transaction', 'cancel_unshipped_orders');
                if ($ableCancelOrder == YesNoEnum::NO) {
                    $goods['after_sale_btn'] = 1;//售后按钮开启
                }
                if ($ableCancelOrder == YesNoEnum::YES) {
                    $configTime = ConfigService::get('transaction', 'cancel_unshipped_orders_times');
                    $ableCancelTime = strtotime($result['pay_time']) + ($configTime * 60);
                    if (time() > $ableCancelTime) {
                        $goods['after_sale_btn'] = 1;//售后按钮开启
                    }
                }
                if (isset($after_sale['status']) && $after_sale['status'] == AfterSaleEnum::STATUS_ING) {
                    $goods['after_sale_btn'] = 2;//售后中
                }
                if (isset($after_sale['status']) && $after_sale['status'] == AfterSaleEnum::STATUS_SUCCESS) {
                    $goods['after_sale_btn'] = 3;//售后成功
                }
                if (isset($after_sale['status']) && $after_sale['status'] == AfterSaleEnum::STATUS_FAIL) {
                    $goods['after_sale_btn'] = 4;//售后失败
                }
            }

            //商品原价总价
            $goods['total_original_price'] = $goods['original_price'] * $goods['goods_num'];
            $result['total_original_price'] += $goods['original_price'] * $goods['goods_num'];

            //会员价优惠
            $goods['member_discount'] = 0;
            if($goods['member_price'] > 0){
                $goods['member_discount'] = round(($goods['original_price'] - $goods['member_price']) * $goods['goods_num'],2);
            }
            unset($goods['member_price']);
            //积分优惠
            $goods['integral_discount'] = round($goods['integral_price'] * $goods['goods_num'],2);
            unset($goods['integral_price']);

            //售后状态
            $goods['after_sale_status_desc'] = '无售后';
            if (!$after_sale->isEmpty()) {
                $goods['after_sale_status_desc'] = AfterSaleEnum::getStatusDesc($after_sale->status);
            }

            //订单商品总的优惠
            $goods['total_discount'] = round($goods['member_discount']+ $goods['coupon_discount'] +$goods['integral_discount'],2);
        }
        //订单商品原价总价
        $result['total_original_price'] = round($result['total_original_price'],2);
        //订单总优惠金额
        $result['total_discount'] = $result['discount_amount'] + $result['member_amount'] + $result['integral_amount'];
//        unset($result['discount_amount']);unset($result['member_amount']);unset($result['integral_amount']);
        //商品详情屏蔽查看内容按钮
        $result['btn']['content_btn'] = OrderEnum::BTN_HIDE;

        return $result;
    }


    /**
     * @notes 取消订单
     * @param $params
     * @return bool
     * @author 段誉
     * @date 2021/8/2 15:08
     */
    public static function cancelOrder($params)
    {
        Db::startTrans();
        try {
            $order = (new Order())->getUserOrderById($params['id'], $params['user_id']);

            // 如果是拼团订单特别处理
            if ($order['order_type'] == OrderEnum::TEAM_ORDER) {

                TeamLogic::signFailTeam($order['id']);

            } else {

                //处于已支付状态的发起整单售后
                if ($order['pay_status'] == PayEnum::ISPAID) {
                    AfterSaleService::orderRefund([
                        'order_id' => $params['id'],
                        'scene' => AfterSaleLogEnum::BUYER_CANCEL_ORDER
                    ]);
                }

                //更新订单为已关闭
                Order::duokaiUpdate([
                    'order_status' => OrderEnum::STATUS_CLOSE,
                    'cancel_time' => time()
                ], ['id' => $order['id']]);

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
                    'type' => OrderLogEnum::TYPE_USER,
                    'channel' => OrderLogEnum::USER_CANCEL_ORDER,
                    'order_id' => $params['id'],
                    'operator_id' => $params['user_id'],
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
     * @notes 确认订单
     * @param $params
     * @author 段誉
     * @date 2021/8/2 14:59
     */
    public static function confirmOrder($params)
    {
        //更新订单状态
        Order::duokaiUpdate([
            'order_status' => OrderEnum::STATUS_FINISH,
            'confirm_take_time' => time(),
            'after_sale_deadline' => self::getAfterSaleDeadline(), //售后截止时间
        ], ['id' => $params['id'], 'user_id' => $params['user_id']]);

        //订单日志
        (new OrderLog())->record([
            'type' => OrderLogEnum::TYPE_USER,
            'channel' => OrderLogEnum::USER_CONFIRM_ORDER,
            'order_id' => $params['id'],
            'operator_id' => $params['user_id']
        ]);
    }


    /**
     * @notes 获取当前售后
     * @return float|int
     * @author 段誉
     * @date 2021/8/2 17:02
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
     * @notes 查看物流
     * @param $params
     * @return array[]
     * @author ljj
     * @date 2021/8/13 6:07 下午
     */
    public static function orderTraces($params)
    {
        // 获取订单信息,物流信息
        $order = Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('order_goods og', 'o.id = og.order_id')
            ->join('goods g', 'og.goods_id = g.id')
            ->join('delivery d', 'd.order_id = o.id')
            ->field('g.image,o.order_status,d.express_name,d.invoice_no,o.total_num,d.contact,d.mobile,o.address,o.confirm_take_time,d.send_type,d.express_id,o.express_time,o.pay_time,o.create_time')
            ->append(['delivery_address'])
            ->where(['o.id'=>$params['id'],'o.user_id'=>$params['user_id']])
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
                if($express_type === 'express_bird') {
                    $expressage = (new Kdniao($express_bird['ebussiness_id'], $express_bird['app_key']));
                    $express_field = 'codebird';
                } elseif($express_type === 'express_hundred') {
                    $expressage = (new Kd100($express_hundred['customer'], $express_hundred['app_key']));
                    $express_field = 'code100';
                }

                //快递编码
                $express_code = Express::where('id',$order['express_id'])->value($express_field);

                //获取物流轨迹
                if (in_array(strtolower($express_code), [ 'sf', 'shunfeng' ])) {
                    if ($express_type === 'express_bird') {
                        $expressage->logistics($express_code, $order['invoice_no'], substr($order['address']->mobile, -4));
                    } else {
                        $expressage->logistics($express_code, $order['invoice_no'], $order['address']->mobile);
                    }
                }else {
                    $expressage->logistics($express_code, $order['invoice_no']);
                }
//                if ($express_code === 'SF' && $express_type === 'express_bird') {
//                    $expressage->logistics($express_code, $order['invoice_no'], substr($order['address']->mobile,-4));
//                }else {
//                    $expressage->logistics($express_code, $order['invoice_no']);
//                }

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
            'order'=>[
                'goods_image' => FileService::getFileUrl($order['image']),
                'goods_count' => $order['total_num'],
                'express_name' => $order['express_name'],
                'invoice_no' => $order['invoice_no'],
                'order_status' => $order['order_status'],
                'send_type' => $order['send_type'],
            ],
            'take'=>[
                'contact' => $order['contact'],
                'mobile' => $order['mobile'],
                'address' => $order['delivery_address'],
            ],
            'finish'=>[
                'title' => '交易完成',
                'tips' => ($order['order_status'] == OrderEnum::STATUS_FINISH) ? '订单交易完成' : '',
                'time' => ($order['order_status'] == OrderEnum::STATUS_FINISH) ? $order['confirm_take_time'] : '',
            ],
            'delivery' => [
                'title' => '运输中',
                'traces' => $traces
            ],
            'shipment'=>$shipment,
            'pay'=>[
                'title' => '已支付',
                'tips' => '订单支付成功，等待商家发货',
                'time' => $order['pay_time']
            ],
            'buy'=>[
                'title' => '已下单',
                'tips' => '订单提交成功',
                'time' => $order['create_time']
            ],
        ];
    }

    /**
     * @notes 获取配送方式
     * @return array
     * @author ljj
     * @date 2021/8/27 2:32 下午
     */
    public function getDeliveryType()
    {
        return [
            'express' => [
                'is_express' => ConfigService::get('delivery_type', 'is_express', 1),
                'express_name' => ConfigService::get('delivery_type', 'express_name', '快递发货'),
            ],
            'selffetch' => [
                'is_selffetch' => ConfigService::get('delivery_type', 'is_selffetch', 0),
                'selffetch_name' => ConfigService::get('delivery_type', 'selffetch_name', '上门自提'),
            ],
        ];
    }

    /**
     * @notes 删除订单
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/31 2:38 下午
     */
    public function del($params)
    {
        return Order::duokaiDestroy($params['id']);
    }

    /**
     * @notes 提取砍价商品信息
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/10/9 11:13
     */
    public static function initiateGoods($params)
    {
        $bargainInitiate = BargainInitiate::findOrEmpty($params['initiate_id'])->toArray();
        // 返回二维数组
        return [
            [
                'item_id' => $bargainInitiate['goods_snapshot']['item_id'],
                'goods_num' => $bargainInitiate['goods_snapshot']['goods_num'],
            ]
        ];
    }

    /**
     * @notes 获取不同类型订单的规格单价
     * @param $params
     * @author Tab
     * @date 2021/10/9 11:23
     */
    public static function getSellPrice($params, $goodsInfo)
    {
        switch ($params['order_type']) {
            // 普通订单
            case OrderEnum::NORMAL_ORDER:
                return self::getGoodsSellPrice($goodsInfo);
            // 拼团订单
            case OrderEnum::TEAM_ORDER:
                return self::getTeamActivityPrice($params);
            // 秒杀订单
            case OrderEnum::SECKILL_ORDER:
                return self::getSeckillActivityPrice($params);
            // 砍价订单
            case OrderEnum::BARGAIN_ORDER:
                return self::getBargainActivityPrice($params);
            //虚拟订单
            case OrderEnum::VIRTUAL_ORDER:
                return self::getGoodsSellPrice($goodsInfo);
        }
    }
    /**
     * @notes 获取商品售价
     * @param $params
     * @return mixed
     * @author cjhao
     * @date 2022/5/7 18:40
     */
    public static function getGoodsSellPrice($params)
    {
        if($params['member_price'] >= 0 && $params['member_price'] <= $params['sell_price']){
            return $params['member_price'];
        }
        return $params['sell_price'];
    }

    /**
     * @notes 获取砍价活动价格
     * @param $params
     * @return mixed|void
     * @author Tab
     * @date 2021/10/9 11:30
     */
    public static function getBargainActivityPrice($params)
    {
        $bargainInitiate = BargainInitiate::findOrEmpty($params['initiate_id'])->toArray();
        // 任意金额可购买
        if ($params['buy_condition'] == 'random') {
            return $bargainInitiate['current_price'];
        }
        // 底价购买
        if ($params['buy_condition'] == 'floor') {
            return $bargainInitiate['floor_price'];
        }
    }

    /**
     * @notes 获取秒杀活动价格
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/10/9 16:00
     */
    public static function getSeckillActivityPrice($params)
    {
        return SeckillGoodsItem::where([
            'seckill_id' => $params['seckill_id'],
            'item_id' => $params['goods'][0]['item_id'],
        ])->value('seckill_price');
    }

    /**
     * @notes 获取拼团活动价格
     * @param $params
     * @author Tab
     * @date 2021/10/9 16:31
     */
    public static function getTeamActivityPrice($params)
    {
        return TeamGoodsItem::where([
            'team_id' => $params['team_id'],
            'item_id' => $params['goods'][0]['item_id'],
        ])->value('team_price');
    }

    /**
     * @notes 砍价下单后的操作
     * @param $params
     * @param $order
     * @author Tab
     * @date 2021/10/9 11:52
     */
    public static function bargainAfter($params, $order)
    {
        // 更新砍价记录
        $bargainInitiate = BargainInitiate::findOrEmpty($params['initiate_id']);
        $bargainInitiate->order_id = $order['id'];
        // 标识砍价成功避免任意金额可下单的情况用户继续发起帮助砍价
        $bargainInitiate->status = BargainEnum::STATUS_SUCCESS;
        $bargainInitiate->duokaiSave();
    }

    /**
     * @notes 拼团下单后的操作
     * @param $params
     * @param $order
     * @author Tab
     * @date 2021/10/9 16:35
     */
    public static function teamAfter($params, $order)
    {
        $time = time();
        $news_found_id = null;
        $teamActivity = TeamActivity::findOrEmpty($params['team_id'])->toArray();
        $teamGoods = TeamGoods::where([
            'team_id' => $params['team_id'],
            'goods_id' => $params['goods'][0]['goods_id'],
        ])->findOrEmpty()->toArray();
        $teamGoodsItem = TeamGoodsItem::where([
            'team_id' => $params['team_id'],
            'item_id' => $params['goods'][0]['item_id'],
        ])->findOrEmpty()->toArray();
        $goodsSnap = $teamGoods['goods_snap'];
        $itemSnap  = $teamGoodsItem['item_snap'];
        $goodsInfo = [
            'id'             => intval($params['goods'][0]['goods_id']),
            'item_id'        => intval($params['goods'][0]['item_id']),
            'spec_value_ids' => $params['goods'][0]['spec_value_ids'],
            'name'           => $goodsSnap['name'],
            'image'          => $itemSnap['image'] ? FileService::getFileUrl($itemSnap['image']) : FileService::getFileUrl($goodsSnap['image']),
            'spec_value_str' => $itemSnap['spec_value_str'],
            'cost_price'     => $teamGoodsItem['sell_price'],
            'sell_price'     => $teamGoodsItem['team_price'],
            'total_price'    => round($teamGoodsItem['team_price'] * $params['goods'][0]['goods_num'], 2),
            'count'          => intval($params['goods'][0]['goods_num']),
            'goods_snap'     => $goodsSnap,
        ];

        // 开团
        if (!isset($params['found_id']) || empty($params['found_id'])) {
            $found = TeamFound::duokaiCreate([
                'found_sn' => generate_sn((new TeamFound()), 'found_sn'),
                'team_id'  => $params['team_id'],
                'user_id'  => $params['user_id'],
                'order_id' => $order['id'],
                'people'   => $teamActivity['people_num'],
                'join'     => 0,
                'status'   => 0,
                'goods_snap' => json_encode([
                    'id'     => $params['goods'][0]['goods_id'],
                    'name'   => $params['goods'][0]['goods_name'],
                    'image'  => $params['goods'][0]['image']
                ], JSON_UNESCAPED_UNICODE),
                'kaituan_time' => $time,
                'invalid_time' => ($teamActivity['effective_time'] * 60) + time()
            ]);
            $news_found_id = $found['id'];
        }

        // 参团
        TeamJoin::duokaiCreate([
            'join_sn'      => generate_sn((new TeamJoin()), 'join_sn'),
            'team_id'      => $params['team_id'],
            'found_id'     => $news_found_id ?: $params['found_id'],
            'identity'     => $news_found_id ? 1 : 2,
            'user_id'      => $params['user_id'],
            'order_id'     => $order['id'],
            'status'       => 0,
            'team_snap'    => json_encode($teamActivity, JSON_UNESCAPED_UNICODE),
            'goods_snap'   => json_encode($goodsInfo, JSON_UNESCAPED_UNICODE),
            'invalid_time' => ($teamActivity['effective_time'] * 60) + time(),
            'create_time'  => $time,
            'update_time'  => $time
        ]);

        // 更新数据
        TeamFound::duokaiUpdate(['join'=>['inc', 1]], ['id'=>$news_found_id ?: $params['found_id']]);
        TeamActivity::duokaiUpdate(['partake_number'=>['inc', 1]], ['id'=>$params['team_id']]);
        Order::duokaiUpdate(['team_found_id'=>$news_found_id ?: $params['found_id']], ['id'=>$order['id']]);
    }

    /**
     * @notes 秒杀下单后操作
     * @param $params
     * @param $order
     * @author Tab
     * @date 2021/10/13 14:49
     */
    public static function seckillAfter($params, $order)
    {
        Order::duokaiUpdate([
            'id' => $order['id'],
            'seckill_id' => $params['seckill_id']
        ], [], [], '', false);
    }
}
