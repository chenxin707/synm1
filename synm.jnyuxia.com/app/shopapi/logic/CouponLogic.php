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


use app\common\enum\CouponEnum;
use app\common\enum\FootprintEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\DiscountLogic;
use app\common\model\Cart;
use app\common\model\Coupon;
use app\common\model\CouponList;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\User;
use app\common\service\TimeService;
use Exception;

class CouponLogic
{
    /**
     * @notes 领取优惠券
     * @param $params
     * @param $user_id
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/22 15:28
     */
    public static function receive($params, $user_id)
    {
        try {
            $user = User::findOrEmpty($user_id);
            if ($user->isEmpty()) {
                throw new Exception('请先登录');
            }
            // 汽泡足迹
            event('Footprint', ['type' => FootprintEnum::GET_COUPONS, 'user_id' => $user_id]);
            // 查询优惠券信息
            $coupon = (new Coupon())->findOrEmpty(intval($params['id']))->toArray();

            if (!$coupon or $coupon['status'] == CouponEnum::COUPON_STATUS_NOT) {
                throw new Exception('优惠券尚未开放领取，敬请期待');
            }

            if (!$coupon or $coupon['status'] == CouponEnum::COUPON_STATUS_END) {
                throw new Exception('领取失败，优惠券活动已结束');
            }

            if ($coupon['send_total_type'] == CouponEnum::SEND_TOTAL_TYPE_FIXED) {
                if ($coupon['send_total'] <= 0) {
                    throw new Exception('领取失败，优惠券已抢光了');
                }
                $receiveTotal = (new CouponList())->where(['coupon_id'=>intval($params['id'])])->count();
                if ($receiveTotal >= $coupon['send_total']) {
                    throw new Exception('领取失败，优惠券已抢光了');
                }
            }

            switch ($coupon['get_num_type']) {
                case CouponEnum::GET_NUM_TYPE_NOT:
                    break;
                case CouponEnum::GET_NUM_TYPE_LIMIT:
                    $total = (new CouponList())
                        ->where('user_id', '=', $user_id)
                        ->where('coupon_id', '=', $coupon['id'])
                        ->count();
                    if ($total >= $coupon['get_num']) {
                        throw new Exception('领取失败，超过领取限制');
                    }
                    break;
                case CouponEnum::GET_NUM_TYPE_DAY:
                    $total = (new CouponList())
                        ->where('coupon_id', '=', $coupon['id'])
                        ->where('user_id', '=', $user_id)
                        ->where('create_time', '>=', TimeService::today()[0])
                        ->where('create_time', '<=', TimeService::today()[1])
                        ->count();
                    if ($total >= $coupon['get_num']) {
                        throw new Exception('领取失败，超过今天领取限制');
                    }
                    break;
            }

            // 计算出券最后可用时间
            $invalid_time = 0;
            switch ($coupon['use_time_type']) {
                case CouponEnum::USE_TIME_TYPE_FIXED:
                    $invalid_time = $coupon['use_time_end'];
                    break;
                case CouponEnum::USE_TIME_TYPE_TODAY:
                    $invalid_time = time() + ($coupon['use_time'] * 86400);
                    break;
                case CouponEnum::USE_TIME_TYPE_TOMORROW:
                    $time = strtotime(date('Y-m-d', strtotime("+1 day")));
                    $invalid_time = $time + ($coupon['use_time'] * 86400);
            }

            // 发放优惠券
            CouponList::duokaiCreate([
                'channel'      => 1,
                'coupon_code'  => create_code(),
                'user_id'      => $user_id,
                'coupon_id'    => $coupon['id'],
                'order_id'     => 0,
                'status'       => 0,
                'use_time'     => 0,
                'invalid_time' => $invalid_time,
                'create_time'  => time()
            ]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 获取商品优惠券
     * @param int $goodsId 商品id
     * @param int $userId 用户id
     * @return array
     * @author cjhao
     * @date 2021/8/27 15:12
     */
    public static function goodsCoupon(int $goodsId,int $userId = 0):array
    {
        $couponList = Coupon::where(['get_type'=>CouponEnum::GET_TYPE_USER, 'status' => CouponEnum::COUPON_STATUS_CONDUCT])
                ->select();
        $goodsCoupon = [];
        $myCouponIds = [];
        if($userId){
            $myCouponIds = CouponList::where(['user_id'=>$userId,'status'=>CouponEnum::USE_STATUS_NOT])
                        ->column('coupon_id');

        }

        foreach ($couponList as $coupon){
            $couponId = false;
            //优惠券是否适合当前商品
            switch ($coupon->use_goods_type){
                case CouponEnum::USE_GOODS_TYPE_NOT:
                    $couponId = true;
                    break;
                case CouponEnum::USE_GOODS_TYPE_ALLOW:
                    $useGoodsIds = explode(',',$coupon->use_goods_ids);
                    if(in_array($goodsId,$useGoodsIds)){
                        $couponId = true;
                    }
                    break;
                case CouponEnum::USE_GOODS_TYPE_BAN:
                    $useGoodsIds = explode(',',$coupon->use_goods_ids);
                    if(!in_array($goodsId,$useGoodsIds)){
                        $couponId = true;
                    }
                    break;
            }
            if(CouponEnum::USE_TIME_TYPE_FIXED == $coupon->use_time_type && $coupon->use_time_end < time()){
                $couponId = false;
            }
            if($couponId){
                //使用条件
                $condition = 1 == $coupon->condition_type ? '无金额门槛' : '满' . $coupon->condition_money . '使用';
                if(CouponEnum::USE_GOODS_TYPE_NOT == $coupon->use_goods_type){
                    $useScene = '全场通用';
                }else{
                    $useScene = '指定商品可用';
                }
                //使用时间
                $effectiveTime = '';
                switch ($coupon->use_time_type) {
                    case CouponEnum::USE_TIME_TYPE_TODAY:
                        $effectiveTime = '领取' . $coupon->use_time . '天内可用';
                        break;
                    case CouponEnum::USE_TIME_TYPE_TOMORROW:
                        $effectiveTime = '领取次日' . $coupon->use_time . '天内可用';
                        break;
                }
                //优惠券是否已领取
                $isReceive = 0;
                if(in_array($coupon->id,$myCouponIds)){
                    $isReceive = 1;
                }
                $isAvailable = 0;
                //是否可领取
                switch ($coupon->get_num_type) {
                    case CouponEnum::GET_NUM_TYPE_LIMIT:
                        $total = CouponList::where(['coupon_id' => $coupon->id])
                            ->where(['user_id' => $userId])
                            ->count();
                        $isAvailable = $total >= $coupon->get_num ? 1 : 0;
                        break;
                    case CouponEnum::GET_NUM_TYPE_DAY:
                        $total = CouponList::where(['coupon_id' => $coupon->id])
                            ->where(['user_id' =>$userId])
                            ->where('create_time', '>=', TimeService::today()[0])
                            ->where('create_time', '<=', TimeService::today()[1])
                            ->count();
                        $isAvailable = $total >= $coupon->get_num ? 1 : 0;
                        break;
                }
                $goodsCoupon[] = [
                    'id'            => $coupon->id,
                    'name'          => $coupon->name,
                    'money'         => $coupon->money,
                    'condition'     => $condition,
                    'use_scene'     => $useScene,
                    'is_receive'    => $isReceive,
                    'is_available'  => $isAvailable,
                    'effective_time'=> $effectiveTime,
                ];
            }
        }

        return $goodsCoupon;
    }

    /**
     * @notes 结算页优惠券
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/9 18:14
     */
    public static function orderCoupon($params)
    {
        // 处理购物车的情况
        if ($params['source'] == 2) {
            $params['goods'] = self::handleCart($params['cart_ids']);
        }

        // 计算订单金额
        $orderAmount = self::calcOrderAmount($params);

        // 查找用户未使用的券
        $where = [
            ['cl.user_id', '=', $params['user_id']],
            ['cl.status', '=', CouponEnum::USE_STATUS_NOT],
        ];
        $field = [
            // 领取时间
            'cl.id',
            // 领取时间
            'cl.create_time',
            // 领取时间
            'cl.create_time',
            // 券名称
            'c.name',
            // 面额
            'c.money',
            // 使用门槛
            'c.condition_type',
            // 订单满多少金额可用
            'c.condition_money',
            // 使用时间类型
            'c.use_time_type',
            // 固定时间范围
            'c.use_time_start',
            'c.use_time_end',
            // 领取多少天后可用
            'c.use_time',
            // 适用商品类型
            'c.use_goods_type',
            'c.use_goods_ids',
        ];
        $lists = CouponList::withoutGlobalScope()
            ->aliasSid('cl')
            ->alias('cl')
            ->leftJoin('coupon c', 'c.id = cl.coupon_id')
            ->field($field)
            ->where($where)
            ->select()
            ->toArray();

        // 可用优惠券
        $canUse = [];
        // 不可用优惠券
        $notCanUse = [];
        foreach ($lists as &$item) {
            // 不可用原因
            $item['fail_use_tips'] = '';
            // 不可用原因详细信息
            $item['fail_use_detail'] = [];
            // 检查是否已到可使用时间
            $flagUserTime = self::checkUseTime($item);

            // 检查是否达到使用门槛
            $flagCondition = self::checkCondition($item, $orderAmount);

            // 检查当前商品是否可用券
            $flagGoods = self::checkGoods($item, $params);

            $item['money'] = clearZero($item['money']);

            if ($flagUserTime && $flagCondition && $flagGoods) {
                $canUse[] = $item;
            } else {
                $notCanUse[] = $item;
            }

        }

        return [
            'can_use' => $canUse,
            'can_use_count' => count($canUse),
            'not_can_use' => $notCanUse,
            'not_can_use_count' => count($notCanUse),
        ];
    }

    /**
     * @notes 获取购物车商品
     * @param $cartIds
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/15 19:04
     */
    public static function handleCart($cartIds)
    {
        $data = [];
        $cartData = Cart::where([
            ['id', 'in', $cartIds],
            ['selected', '=', YesNoEnum::YES]
        ])->select()->toArray();
        foreach ($cartData as $item) {
            $data[] = ['item_id' => $item['item_id'], 'num' => $item['goods_num']];
        }
        return $data;
    }

    /**
     * @notes 计算订单价格
     * @param $params
     * @return float|int
     * @author Tab
     * @date 2021/9/8 15:30
     */
    public static function calcOrderAmount($params)
    {
        // 提取item_id
        $item_ids = array_column($params['goods'], 'item_id');
        // 获取规格价格
        $items = GoodsItem::where('id', 'in', $item_ids)->column('goods_id,id,sell_price', 'id');
        $goodsIds = array_column($items,'goods_id');

        //获取会员价
        $levelGoodsItem = DiscountLogic::getGoodsDiscount($params['user_id'], $goodsIds);

        // 计算订单总价
        $sum = 0;
        foreach($params['goods'] as $item) {
            $goodsItem = $items[$item['item_id']];

            $price = $levelGoodsItem[$goodsItem['goods_id']][$goodsItem['id']]['discount_price'] ?? $goodsItem['sell_price'];
            $sum += round($item['num'] * $price,2);
        }
        return $sum;
    }

    /**
     * @notes 检查使用时间
     * @param $item
     * @return bool
     * @author Tab
     * @date 2021/9/9 18:40
     */
    public static function checkUseTime(&$item)
    {
        switch($item['use_time_type']) {
            // 固定时间
            case CouponEnum::USE_TIME_TYPE_FIXED:
                $item['use_time_tips'] = date('Y.m.d H:i:s',  $item['use_time_start']) . ' - ' . date('Y.m.d H:i:s',  $item['use_time_end']);
                if(time() >= $item['use_time_start'] && time() <= $item['use_time_end']) {
                    return true;
                }
                $item['fail_use_tips'] = '不在使用时间范围内';
                $item['fail_use_detail'] = ['不在使用时间范围内'];
                return false;
            // 当天起多少天内
            case CouponEnum::USE_TIME_TYPE_TODAY:
                $today = date('Y-m-d', strtotime($item['create_time']));
                $today .= ' 00:00:00';
                $effectiveTime = strtotime($today) + 24 * 60 * 60 * $item['use_time'];
                $item['use_time_tips'] = '有效期至' . date('Y.m.d H:i:s', $effectiveTime);
                if (time() < $effectiveTime) {
                    return true;
                }
                $item['fail_use_tips'] = '不在使用时间范围内';
                $item['fail_use_detail'] = ['不在使用时间范围内'];
                return false;
            // 次日起多少天内
            case CouponEnum::USE_TIME_TYPE_TOMORROW:
                $today = date('Y-m-d', strtotime($item['create_time']));
                $today .= ' 00:00:00';
                $effectiveStartTime = strtotime($today) + 24 * 60 * 60;
                $effectiveEndTime = strtotime($today) + 24 * 60 * 60 * ($item['use_time'] + 1);
                $item['use_time_tips'] = '有效期至' . date('Y.m.d H:i:s', $effectiveEndTime);
                if (time() >= $effectiveStartTime && time() <= $effectiveEndTime) {
                    return true;
                }
                $item['fail_use_tips'] = '不在使用时间范围内';
                $item['fail_use_detail'] = ['不在使用时间范围内'];
                return false;
        }
        $item['fail_use_tips'] = '不在使用时间范围内';
        $item['fail_use_detail'] = ['不在使用时间范围内'];
        return false;
    }

    /**
     * @notes 使用门槛校验
     * @param $item
     * @param $orderAmount
     * @return bool
     * @author Tab
     * @date 2021/9/9 18:51
     */
    public static function checkCondition(&$item, $orderAmount)
    {
        // 无门槛
        if($item['condition_type'] == CouponEnum::CONDITION_TYPE_NOT) {
            $item['condition_tips'] = '无门槛';
            return true;
        }
        $item['condition_tips'] = '满' . $item['condition_money'] . '可用';
        // 订单满金额
        if($item['condition_type'] == CouponEnum::CONDITION_TYPE_FULL && $orderAmount >= $item['condition_money']) {
            return true;
        }
        $item['fail_use_tips'] = '未满足规定的订单金额';
        $item['fail_use_detail'] = ['未满足规定的订单金额'];
        return false;
    }

    /**
     * @notes 校验当前商品是否可用优惠券
     * @param $item
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/9 19:03
     */
    public static function checkGoods(&$item, $params)
    {
        // 全部商品可用
        if($item['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_NOT) {
            $item['goods_tips'] = '全店通用';
            return true;
        }
        $itemIds = array_column($params['goods'], 'item_id');
        $goodsIds = GoodsItem::where('id', 'in', $itemIds)->select()->column('goods_id');
        // 指定商品可用
        if($item['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_ALLOW) {
            $item['goods_tips'] = '指定商品可用';
            $allow = explode(',', $item['use_goods_ids']);
            $item['fail_use_detail'] = self::goodsData($allow, '可用商品:');
            foreach ($goodsIds as $id) {
                if (!in_array($id, $allow)) {
                    $item['fail_use_tips'] = '部分商品不适用';
                    return false;
                }
            }
            return true;
        }
        // 指定商品不可用
        if($item['use_goods_type'] == CouponEnum::USE_GOODS_TYPE_BAN) {
            $item['goods_tips'] = '指定商品不可用';
            $ban = explode(',', $item['use_goods_ids']);
            $item['fail_use_detail'] = self::goodsData($ban, '不可用商品:');
            foreach ($goodsIds as $id) {
                if (in_array($id, $ban)) {
                    $item['fail_use_tips'] = '部分商品不适用';
                    return false;
                }
            }
            return true;
        }
        $item['fail_use_tips'] = '部分商品不适用';
        return false;
    }

    /**
     * @notes 商品名称
     * @param $goodsIds
     * @author Tab
     * @date 2021/9/10 16:36
     */
    public static function goodsData($goodsIds, $tips)
    {
        $data[] = $tips;
        $goods = Goods::field('name')->where('id', 'in', $goodsIds)->select()->toArray();
        foreach($goods as $item) {
            $data[] = $item['name'];
        }
        return $data;
    }
}
