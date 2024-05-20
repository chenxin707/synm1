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

namespace app\shopapi\logic;


use app\common\enum\OrderEnum;
use app\common\enum\SeckillEnum;
use app\common\model\Goods;
use app\common\model\GoodsCollect;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\SeckillGoods;
use app\common\model\SeckillGoodsItem;
use app\common\model\User;
use app\common\model\UserAddress;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\shopapi\service\CouponService;
use Exception;
use think\facade\Db;

class SeckillLogic
{
    /**
     * @notes 秒杀活动详细
     * @return array|string
     * @author 张无忌
     * @date 2021/7/27 18:27
     */
    public static function detail($params)
    {
        try {
            // 查询校验商品获取活动
            $seckillGoods = (new SeckillGoods())->duokaiWithoutGlobalScope()
                ->aliasSid('SG')
                ->alias('SG')
                ->field(['SG.*,SA.name,SA.min_buy,SA.max_buy',
                    'SA.is_coupon,SA.is_distribution,SA.start_time,SA.end_time,SG.browse_volume,SG.virtual_sales_num,SG.virtual_click_num'])
                ->join('seckill_activity SA', 'SA.id = SG.seckill_id')
                ->where('SA.start_time', '<', time())
                ->where('SA.end_time', '>=', time())
                ->where('SA.status', '>=', SeckillEnum::SECKILL_STATUS_CONDUCT)
                ->findOrEmpty($params['id'])->toArray();

            if (!$seckillGoods) {
                throw new Exception('当前秒杀活动已结束');
            }

            $seckillGoodsItem = (new SeckillGoodsItem())
                ->where(['seckill_gid' => $seckillGoods['id']])
                ->select()->toArray();

            $goods = (new Goods())
                ->field([
                    'id,name,code,image,video,video_cover,min_price',
                    'min_lineation_price,total_stock,sales_num,spec_type,content,unit_id,express_type'
                ])->with(['spec_value.spec_list', 'spec_value_list'])
                ->append(['goods_image'])
                ->findOrEmpty($seckillGoods['goods_id']);

            $goods['is_collect'] = 0;
            if($params['user_id']){
                $isCollect = GoodsCollect::where(['goods_id'=>$goods['id'],'user_id'=>$params['user_id']])->value('id');
                $goods['is_collect'] = $isCollect ? 1 : 0;
            }

            $goods['stock_show'] = ConfigService::get('goods_set', 'is_show', 0);

            foreach ($goods['spec_value_list'] as &$item) {
                $item['image'] = $item['image'] ? $item['image'] : $goods['image'];
                foreach ($seckillGoodsItem as $value) {
                    if ($item['goods_id'] == $value['goods_id'] and $item['id'] == $value['item_id']) {
                        $item['seckill_price'] = $value['seckill_price'];
                        $item['sales_volume'] = $value['sales_volume'];
                        unset($value);
                    }
                }
            }

            $goods['activity'] = [
                'id'              => $seckillGoods['seckill_id'],
                'name'            => $seckillGoods['name'],
                'min_buy'         => $seckillGoods['min_buy'],
                'max_buy'         => $seckillGoods['max_buy'],
                'min_seckill_price' => $seckillGoods['min_seckill_price'],
                'max_seckill_price' => $seckillGoods['max_seckill_price'],
                'browse_volume'     => $seckillGoods['browse_volume'] + $seckillGoods['virtual_click_num'],
                'is_coupon'       => $seckillGoods['is_coupon'],
                'is_distribution' => $seckillGoods['is_distribution'],
                'start_time'      => $seckillGoods['start_time'],
                'end_time'        => $seckillGoods['end_time'],
                'surplus_time'    => $seckillGoods['end_time'] - time(),
                'closing_order'   => (new SeckillGoodsItem())->where(['seckill_gid'=>$seckillGoods['id']])->sum('closing_order'),
                'sales_volume'    => (new SeckillGoodsItem())->where(['seckill_gid'=>$seckillGoods['id']])->sum('sales_volume') + $seckillGoods['virtual_sales_num']
            ];
            //商品评价
            $goods['goods_comment'] = GoodsLogic::getComment($goods['id']);
            // 增加浏览记录
            SeckillGoods::duokaiUpdate(['browse_volume'=> ['inc', 1]], ['id'=>$params['id']]);
            $hidden[] = 'unit_id';
            if($goods['unit_id']){
                $goods['unit_name'] = $goods->unit->name;
                $hidden[] ='unit';
            }

            // 包邮信息
            if ($goods['express_type'] == 1) {
                $goods['free_shipping_tips'] = '免运费';
            } else {
                $goods['free_shipping_tips'] = GoodsLogic::getFreeShippingTips($params['user_id']);
            }

            return $goods->hidden($hidden)->toArray();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 结算信息
     * @param $params
     * @param $user_id
     * @return array|string
     * @author 张无忌
     * @date 2021/8/5 16:43
     */
    public static function settlement($params, $user_id)
    {
        try {
            // 获取用户收货地址
            $address = UserAddress::getOneAddress($user_id, $params['address_id'] ?? 0);

            // 获取下单拼团商品信息
            $seckillGoodsItem = (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('SGI')
                ->alias('SGI')
                ->field('SGI.*,SG.goods_snap,SA.name,SA.min_buy,SA.max_buy,SA.is_coupon,SA.is_distribution')
                ->join('seckill_activity SA', 'SA.id = SGI.seckill_id')
                ->join('seckill_goods SG', 'SG.id = SGI.seckill_gid')
                ->where([
                    ['SGI.seckill_id', '=', (int)$params['seckill_id']],
                    ['SGI.goods_id', '=', (int)$params['goods']['goods_id']],
                    ['SGI.item_id', '=', (int)$params['goods']['item_id']],
                    ['SA.status', '=', SeckillEnum::SECKILL_STATUS_CONDUCT],
                    ['SA.start_time', '<=', time()],
                    ['SA.end_time', '>=', time()],
                ])->findOrEmpty()->toArray();

            if (!$seckillGoodsItem) {
                throw new Exception('该秒杀活动已结束了,下次再来吧');
            }

            if ($params['goods']['count'] < $seckillGoodsItem['min_buy']) {
                throw new Exception('下单数量不能少于' . $seckillGoodsItem['min_buy'] . '件');
            }

            if ($params['goods']['count'] > $seckillGoodsItem['min_buy']) {
                throw new Exception('下单数量不能大于' . $seckillGoodsItem['max_buy'] . '件');
            }

            // 获取用户信息
            $user = (new User())->findOrEmpty($user_id)->toArray();

            // 处理返回的数据
            $goodsSnap = json_decode($seckillGoodsItem['goods_snap'], true);
            $itemSnap  = json_decode($seckillGoodsItem['item_snap'], true);
            $orderStatus = [
                'found_id'       => $params['found_id'] ?? null,
                'order_type'     => OrderEnum::TEAM_ORDER,
                'pay_way'        => intval($params['pay_way'] ?? 1),
                'coupon_id'      => intval($params['coupon_list_id'] ?? 0),
                'remark'         => $params['remark'] ?? '',

                'express_price'     => 0,
                'discount_amount'   => 0,
                'total_count'       => intval($params['goods']['count']),
                'total_amount'      => round($seckillGoodsItem['seckill_price'] * $params['goods']['count'], 2),
                'order_amount'      => round($seckillGoodsItem['seckill_price'] * $params['goods']['count'], 2),
                'user_money'        => $user['user_money'],
                'user_integral'     => $user['user_integral'],

                'address'  => $address,
                'activity' => [
                    'id'         => $seckillGoodsItem['seckill_id'],
                    'name'       => $seckillGoodsItem['name'],
                    'min_buy'    => $seckillGoodsItem['min_buy'],
                    'max_buy'    => $seckillGoodsItem['max_buy'],
                    'sell_price' => $seckillGoodsItem['sell_price'],
                    'seckill_price'    => $seckillGoodsItem['seckill_price'],
                    'is_coupon'        => $seckillGoodsItem['is_coupon'],
                    'is_distribution'  => $seckillGoodsItem['is_distribution']
                ],

                'goods'    => [
                    'id'             => intval($params['goods']['goods_id']),
                    'item_id'        => intval($params['goods']['item_id']),
                    'spec_value_ids' => $itemSnap['spec_value_ids'],
                    'name'           => $goodsSnap['name'],
                    'image'          => $itemSnap['image'] ? FileService::getFileUrl($itemSnap['image']) : FileService::getFileUrl($goodsSnap['image']),
                    'spec_value_str' => $itemSnap['spec_value_str'],
                    'cost_price'     => $seckillGoodsItem['sell_price'],
                    'sell_price'     => $seckillGoodsItem['seckill_price'],
                    'total_price'    => round($seckillGoodsItem['seckill_price'] * $params['goods']['count'], 2),
                    'count'          => intval($params['goods']['count']),
                    'goods_snap'     => $goodsSnap,
                ]
            ];

            // 处理是否使用优惠券
            if ($orderStatus['coupon_id'] and $orderStatus['activity']['is_coupon']) {
                $couponStatus = CouponService::isUsable($user_id, $params['coupon_id'], $orderStatus);
                if ($couponStatus['isUsable']) {
                    $orderStatus['order_amount'] -= $couponStatus['money'];
                    $orderStatus['discount_amount'] += $couponStatus['money'];
                }
            }

            return $orderStatus;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 下单
     * @param $params
     * @param $user_id
     * @return array|string
     * @throws Exception
     * @author 张无忌
     * @date 2021/8/5 16:43
     */
    public static function buy($params, $user_id)
    {
        Db::startTrans();
        try {
            // 验证收货地址
            if (empty($params['address']) || !$params['address']) {
                throw new Exception('请选择收货地址');
            }

            // 验证并扣减库存
            $goodsItem = (new GoodsItem())
                ->where(['id' => $params['goods']['item_id']])
                ->where(['goods_id' => $params['goods']['id']])
                ->findOrEmpty()->toArray();

            if ($goodsItem['stock'] - $params['total_count'] < 0) {
                throw new Exception('抱歉,库存不足了');
            }

            (new GoodsItem())
                ->where(['id' => $params['goods']['item_id']])
                ->where(['goods_id' => $params['goods']['id']])
                ->update(['stock' => ['dec', $params['total_count']]]);

            // 订单逻辑: 创建订单
            $order = self::addOrder($params, $user_id);

            // 提交记录
            Db::commit();
            return [
                'type' => 'order',
                'order_id' => $order['id']
            ];
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 添加订单
     * @param $params
     * @param $user_id
     * @return Order|\think\Model
     * @author 张无忌
     * @date 2021/8/4 10:56
     */
    public static function addOrder($params, $user_id)
    {
        // 创建订单记录
        $order = Order::duokaiCreate([
            'sn'                => generate_sn((new Order()), 'sn'),
            'order_type'        => $params['order_type'],
            'user_id'           => $user_id,
            'order_terminal'    => 0,
            'pay_way'           => $params['pay_way'],
            'total_num'         => $params['total_count'],
            'total_amount'      => $params['total_amount'],
            'goods_price'       => $params['total_amount'],
            'order_amount'      => $params['order_amount'],
            'express_price'     => $params['express_price'],
            'discount_amount'   => $params['discount_amount'],
            'user_remark'       => $params['remark'],
            'address'           => [
                'contact'   => $params['address']['contact'],
                'province'  => $params['address']['province_id'],
                'city'      => $params['address']['city_id'],
                'district'  => $params['address']['district_id'],
                'address'   => $params['address']['address'],
                'mobile'    => $params['address']['mobile'],
            ],
        ]);

        // 创建订单商品记录
        OrderGoods::duokaiCreate([
            'order_id'        => $order['id'],
            'goods_id'        => $params['goods']['id'],
            'item_id'         => $params['goods']['item_id'],
            'goods_name'      => $params['goods']['name'],
            'goods_num'       => $params['goods']['count'],
            'goods_price'     => $params['goods']['sell_price'],
            'member_price'    => $params['goods']['sell_price'],
            'original_price'  => $params['goods']['sell_price'],
            'total_price'     => $params['goods']['total_price'],
            'total_pay_price' => $params['order_amount'],
            'discount_price'  => $params['discount_amount'],
            'integral_price'  => 0,
            'spec_value_ids'  => $params['goods']['spec_value_ids'],
            'goods_snap'      => $params['goods']['goods_snap']
        ]);

        return $order;
    }
}
