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


use app\common\enum\BargainEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\SeckillEnum;
use app\common\enum\TeamEnum;
use app\common\model\BargainInitiate;
use app\common\model\SeckillGoodsItem;
use app\common\model\TeamFound;
use app\common\model\TeamGoodsItem;
use app\common\model\TeamJoin;
use app\common\model\User;
use app\common\validate\BaseValidate;

/**
 * 订单提交验证
 * Class OrderValidate
 * @package app\shopapi\validate
 */
class PlaceOrderValidate extends BaseValidate
{

    protected $rule = [
        // 下单用户
        'user_id' => 'checkUser',
        // 下单来源(立即购买/购物车购买)
        'source'    => 'require',
        // 下单动作(结算/下单)
        'action'    => 'require',
        // 订单类型
        'order_type' => 'require|checkGoods|checkOrderType',
        // 配送方式
        'delivery_type' => 'require|checkDeliveryType'
    ];


    protected $message = [
        'source.require'    => '下单来源缺失',
        'action.require'    => '下单动作缺失',
        'order_type.require'     => '订单类型缺失',
        'delivery_type.require'     => '配送参数缺失',
    ];

    /**
     * @notes 检测用户是否登录
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/10/14 11:59
     */
    public static function checkUser($value, $rule, $data)
    {
        $user = User::findOrEmpty($value);
        if ($user->isEmpty()) {
            return '请先登录';
        }
        return true;
    }

    /**
     * @notes 检查配送方式
     * @param $value
     * @param $rule
     * @param $data
     * @author Tab
     * @date 2021/10/9 10:59
     */
    public function checkDeliveryType($value, $rule, $data)
    {
        if (!in_array($data['delivery_type'],DeliveryEnum::DELIVERY_TYPE)) {
            return '无效的配送方式';
        }
        return true;
    }

    /**
     * @notes 验证商品参数
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/7/26 17:22
     */
    public function checkGoods($value, $rule, $data)
    {
        // 购买车下单 或 砍价订单 无需商品信息
        if ($data['source'] == 'cart' || $data['order_type'] == OrderEnum::BARGAIN_ORDER) {
            return true;
        }

        if (!isset($data['goods'])) {
            return '商品信息参数缺失';
        }

        if (!is_array($data['goods'])) {
            return '商品信息格式不正确';
        }

        foreach ($data['goods'] as $item) {
            if (!isset($item['item_id']) || !isset($item['goods_num'])) {
                return '商品信息参数不完整';
            }
        }
        return true;
    }

    /**
     * @notes 校验订单类型
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/10/8 18:59
     */
    public function checkOrderType($value, $rule, $data)
    {
        if (!in_array($value, OrderEnum::ORDER_TYPE)) {
            return '无效的订单类型';
        }

        // 营销活动验证
        switch($value)
        {
            // 拼团验证
            case OrderEnum::TEAM_ORDER:
                return $this->checkTeam($data);
            // 秒杀验证
            case OrderEnum::SECKILL_ORDER:
                return $this->checkSeckill($data);
            // 砍价验证
            case OrderEnum::BARGAIN_ORDER:
                return $this->checkBargain($data);
        }

        return true;
    }

    /**
     * @notes 拼团验证
     * @param $data
     * @author Tab
     * @date 2021/10/8 19:06
     */
    public function checkTeam($data)
    {
        if (!isset($data['team_id'])) {
            return '拼团活动ID缺失';
        }

        // 拼团商品信息
        $teamGoodsItem = (new TeamGoodsItem())->duokaiWithoutGlobalScope()
            ->aliasSid('TGI')
            ->alias('TGI')
            ->field('TGI.*,TG.goods_snap,TA.name,TA.people_num,
                    TA.min_buy,TA.max_buy,TA.is_coupon,TA.is_distribution,TA.effective_time')
            ->join('team_activity TA', 'TA.id = TGI.team_id')
            ->join('team_goods TG', 'TG.id = TGI.team_gid')
            ->where([
                ['TGI.team_id', '=', (int)$data['team_id']],
                ['TGI.item_id', '=', (int)$data['goods'][0]['item_id']],
                ['TA.status', '=', TeamEnum::TEAM_STATUS_CONDUCT],
                ['TA.start_time', '<=', time()],
                ['TA.end_time', '>=', time()],
            ])->findOrEmpty()->toArray();

        if (empty($teamGoodsItem)) {
           return '拼团活动已结束';
        }

        if ($teamGoodsItem['max_buy'] > 0 && $data['goods'][0]['goods_num'] > $teamGoodsItem['max_buy']) {
            return '下单数量不能大于'.$teamGoodsItem['max_buy'].'件';
        }

        // 参团
        if (isset($data['found_id']) && !empty($data['found_id'])) {
            $teamFound = (new TeamFound())->where(['id'=>$data['found_id']])->findOrEmpty()->toArray();
            if (empty($teamFound)) {
                return '选择的团不存在';
            }
            if ($teamFound['status'] != 0) {
                return '当前拼团已结束，请重新选择拼团';
            }
            if ($teamFound['invalid_time'] <= time()) {
                return '当前拼团已结束，请重新选择拼团';
            }
            if ($teamFound['user_id'] == $data['user_id']) {
                return '您是该团发起人,不能参团哦';
            }
            if ($teamFound['people'] == $teamFound['join']) {
                return '当前拼团已满员，请重新选择拼团';
            }

            // 获取已参团记录
            $people = (new TeamJoin())->where(['found_id'=>$data['found_id'], 'user_id'=>$data['user_id']])->findOrEmpty()->toArray();
            if (!empty($people)) {
                return '您已是该团成员了,不能重复参团哦！';
            }
        }

        return true;
    }

    /**
     * @notes 秒杀验证
     * @author Tab
     * @date 2021/10/8 19:07
     */
    public function checkSeckill($data)
    {
        if (!isset($data['seckill_id'])) {
            return '秒杀活动ID缺失';
        }

        // 秒杀商品信息
        $seckillGoodsItem = (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
            ->aliasSid('SGI')
            ->alias('SGI')
            ->field('SGI.*,SG.goods_snap,SA.name,SA.min_buy,SA.max_buy,SA.is_coupon,SA.is_distribution')
            ->join('seckill_activity SA', 'SA.id = SGI.seckill_id')
            ->join('seckill_goods SG', 'SG.id = SGI.seckill_gid')
            ->where([
                ['SGI.seckill_id', '=', (int)$data['seckill_id']],
                ['SGI.item_id', '=', (int)$data['goods'][0]['item_id']],
                ['SA.status', '=', SeckillEnum::SECKILL_STATUS_CONDUCT],
                ['SA.start_time', '<=', time()],
                ['SA.end_time', '>=', time()],
            ])->findOrEmpty()->toArray();

        if (empty($seckillGoodsItem)) {
            return '秒杀活动已结束';
        }

        if ($seckillGoodsItem['max_buy'] > 0 && $data['goods'][0]['goods_num'] > $seckillGoodsItem['max_buy']) {
            return '下单数量不能大于' . $seckillGoodsItem['max_buy'] . '件';
        }

        return true;
    }

    /**
     * @notes 砍价验证
     * @author Tab
     * @date 2021/10/8 19:07
     */
    public function checkBargain($data)
    {
        if (!isset($data['initiate_id'])) {
            return '发起砍价ID缺失';
        }
        if (!isset($data['buy_condition'])) {
            return '购买条件缺失';
        }

        $bargainInitiate = BargainInitiate::findOrEmpty($data['initiate_id'])->toArray();
        if (empty($bargainInitiate)) {
            return '砍价信息不存在';
        }
        if ($bargainInitiate['status'] != BargainEnum::STATUS_SUCCESS && $bargainInitiate['bargain_snapshot']['buy_condition'] != BargainEnum::BUY_CONDITION_RAND) {
            return '未砍价成功 或 不支持任意金额购买';
        }
        if (!empty($bargainInitiate['order_id'])) {
            return '已下过单，不能重复购买';
        }

        if ($bargainInitiate['bargain_snapshot']['order_limit'] > 0 && $bargainInitiate['goods_snapshot']['goods_num'] > $bargainInitiate['bargain_snapshot']['order_limit']) {
            return '超出每单购买限制';
        }

        return true;
    }
}