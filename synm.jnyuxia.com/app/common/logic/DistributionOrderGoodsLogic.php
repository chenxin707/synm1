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

namespace app\common\logic;

use app\adminapi\logic\distribution\DistributionConfigLogic;
use app\common\enum\DistributionConfigEnum;
use app\common\enum\DistributionGoodsEnum;
use app\common\enum\DistributionOrderGoodsEnum;
use app\common\enum\OrderEnum;
use app\common\model\DistributionConfig;
use app\common\model\DistributionGoods;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\SeckillActivity;
use app\common\model\TeamActivity;
use app\common\model\TeamFound;
use app\common\model\User;

/**
 * 分销订单逻辑层
 * Class DistributionOrderGoodsLogic
 * @package app\common\logic
 */
class DistributionOrderGoodsLogic extends BaseLogic
{
    /**
     * @notes 添加分销订单
     * @param $orderId
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/4 20:47
     */
    public static function add($orderId)
    {
        // 获取分销配置
        $distributionConfig = DistributionConfigLogic::getConfig();
        if(!$distributionConfig['switch']) {
            return false;
        }

        // 校验订单类型
        $allowDistribution = self::checkOrderType($orderId);
        if(!$allowDistribution) {
            return false;
        }

        // 用户信息
        $userId = Order::where('id', $orderId)->value('user_id');
        $userInfo = self::userInfo($userId);

        // 订单信息
        $orderInfo = self::orderInfo($orderId);

        foreach($orderInfo as $item) {
            // 判断商品是否参与分销
            $goodsDistribution = self::checkGoodsDistribution($item['goods_id']);
            if(empty($goodsDistribution) || !$goodsDistribution['is_distribution']) {
                // 商品未参与分销
                continue;
            }

            // 分销层级
            switch($distributionConfig['level'])
            {
                case DistributionConfigEnum::LEVEL_ONE: // 一级分销
                    self::firstLevelCommission($userInfo, $item, $goodsDistribution);
                    break;
                case DistributionConfigEnum::LEVEL_TWO: // 一级、二级分销
                    self::firstLevelCommission($userInfo, $item, $goodsDistribution);
                    self::secondLevelCommission($userInfo, $item, $goodsDistribution);
                    break;
            }

            // 自购返佣
            if($distributionConfig['self'])  {
                self::selfPurchaseCommission($userInfo, $item, $goodsDistribution);
            }
        }

    }

    /**
     * @notes 校验订单类型
     * @param $orderId
     * @return bool
     * @author Tab
     * @date 2021/7/26 17:21
     */
    public static function checkOrderType($orderId)
    {
        $order = Order::field('order_type')->where('id', $orderId)->findOrEmpty()->toArray();

        // 普通订单
        if(in_array($order['order_type'], [OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER])) {
            return true;
        }

        // 非普通订单不参与分销
        return false;
    }

    /**
     * @notes 订单信息
     * @param $orderId
     * @return mixed
     * @author Tab
     * @date 2021/7/26 17:21
     */
    public static function orderInfo($orderId)
    {
        $orderInfo = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->field('og.id as order_goods_id,og.order_id,og.goods_id,og.item_id,og.total_pay_price,o.user_id')
            ->where('og.order_id', $orderId)
            ->select()
            ->toArray();
        return $orderInfo;
    }

    /**
     * @notes 用户信息
     * @param $userId
     * @return mixed
     * @author Tab
     * @date 2021/7/26 17:21
     */
    public static function userInfo($userId)
    {
        $userInfo = User::withoutGlobalScope()
            ->aliasSid('u')
            ->alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->field('u.id,u.first_leader,u.second_leader,d.level_id,d.is_distribution,d.is_freeze')
            ->where('u.id', $userId)
            ->findOrEmpty()
            ->toArray();

        return $userInfo;
    }

    /**
     * @notes 校验商品是否参与分销
     * @param $goodsId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/26 17:21
     */
    public static function checkGoodsDistribution($goodsId)
    {
        $distributionGoods = DistributionGoods::field('goods_id,item_id,level_id,self_ratio,first_ratio,second_ratio,is_distribution,rule')
            ->where('goods_id', $goodsId)
            ->select()
            ->toArray();

        if(empty($distributionGoods)) {
            return [];
        }

        return [
            'goods_id' => $distributionGoods[0]['goods_id'],
            'is_distribution' => $distributionGoods[0]['is_distribution'],
            'rule' => $distributionGoods[0]['rule']
        ];
    }

    /**
     * @notes 一级分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     * @date 2021/7/26 17:22
     */
    public static function firstLevelCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['first_leader']) {
            // 没有上级，无需分佣
            return false;
        }
        $firstLeaderInfo = self::userInfo($userInfo['first_leader']);
        if(!$firstLeaderInfo['is_distribution'] || $firstLeaderInfo['is_freeze']) {
            // 上级不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $firstLeaderInfo);
        $firstLeaderInfo['ratio'] = $ratioArr['first_ratio'];
        $firstLeaderInfo['level'] = DistributionConfigEnum::LEVEL_ONE;
        self::addDistributionOrderGoods($item, $firstLeaderInfo);
    }

    /**
     * @notes 二级分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     * @date 2021/7/26 17:22
     */
    public static function secondLevelCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['second_leader']) {
            // 没有上上级，无需分佣
            return false;
        }
        $secondLeaderInfo = self::userInfo($userInfo['second_leader']);
        if(!$secondLeaderInfo['is_distribution'] || $secondLeaderInfo['is_freeze']) {
            // 上上级不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $secondLeaderInfo);
        $secondLeaderInfo['ratio'] = $ratioArr['second_ratio'];
        $secondLeaderInfo['level'] = DistributionConfigEnum::LEVEL_TWO;
        self::addDistributionOrderGoods($item, $secondLeaderInfo);
    }

    /**
     * @notes 自购分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     * @date 2021/7/26 17:22
     */
    public static function selfPurchaseCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['is_distribution'] || $userInfo['is_freeze']) {
            // 自己不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $userInfo);
        $userInfo['ratio'] = $ratioArr['self_ratio'];
        $userInfo['level'] = DistributionConfigEnum::LEVEL_SELF;
        self::addDistributionOrderGoods($item, $userInfo);
    }

    /**
     * @notes 获取佣金比例
     * @param $goodsDistribution
     * @param $item
     * @param $userInfo
     * @return array
     * @author Tab
     * @date 2021/7/26 17:22
     */
    public static function getRatio($goodsDistribution, $item, $userInfo)
    {
        // 按分销会员等级对应的比例
        if($goodsDistribution['rule'] == DistributionGoodsEnum::RULE_LEVEL) {
            $ratioArr = DistributionLevel::field('first_ratio,second_ratio,self_ratio')
                ->where('id', $userInfo['level_id'])
                ->findOrEmpty()
                ->toArray();
            return $ratioArr;
        }

        // 单独设置的比例
        if($goodsDistribution['rule'] == DistributionGoodsEnum::RULE_CUSTOMIZE) {
            $ratioArr = DistributionGoods::field('first_ratio,second_ratio,self_ratio')
                ->where([
                    'goods_id' => $item['goods_id'],
                    'item_id' => $item['item_id'],
                    'level_id' => $userInfo['level_id']
                ])
                ->findOrEmpty()
                ->toArray();
            return $ratioArr;
        }
    }

    /**
     * @notes 生成分销订单
     * @param $item
     * @param $userInfo
     * @return false
     * @author Tab
     * @date 2021/7/26 17:22
     */
    public static function addDistributionOrderGoods($item, $userInfo)
    {
        $earnings = 0;
        $distributionConfig = DistributionConfigLogic::getConfig();
        if($distributionConfig['cal_method'] == DistributionConfigEnum::CAL_BY_PAYMENT_AMOUNT)
        {
            $earnings = self::calByPaymentAmount($item, $userInfo);
        }
        if($earnings < 0.01) {
            return false;
        }
        $data = [
            'user_id' => $userInfo['id'],
            'level_id' => $userInfo['level_id'],
            'level' => $userInfo['level'],
            'ratio' => $userInfo['ratio'],
            'order_goods_id' => $item['order_goods_id'],
            'goods_id' => $item['goods_id'],
            'item_id' => $item['item_id'],
            'earnings' => $earnings,
            'status' => DistributionOrderGoodsEnum::UN_RETURNED,
        ];

        DistributionOrderGoods::duokaiCreate($data);
    }

    /**
     * @notes 根据商品实际支付金额计算佣金
     * @param $item
     * @param $userInfo
     * @return float
     * @author Tab
     * @date 2021/7/27 11:54
     */
    public static function calByPaymentAmount($item, $userInfo)
    {
        $earnings = round(($item['total_pay_price'] * $userInfo['ratio'] / 100), 2);
        return $earnings;
    }
}