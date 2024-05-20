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

use app\common\enum\BargainEnum;
use app\common\enum\CouponEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\logic\BaseLogic;
use app\common\model\BargainActivity;
use app\common\model\BargainGoods;
use app\common\model\BargainHelp;
use app\common\model\BargainInitiate;
use app\common\model\Coupon;
use app\common\model\CouponList;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\GoodsSpec;
use app\common\model\GoodsSpecValue;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderLog;
use app\common\model\SelffetchShop;
use app\common\model\User;
use app\common\model\UserAddress;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\shopapi\logic\Order\FreightLogic;
use app\shopapi\logic\Order\OrderLogic;
use think\Exception;
use think\facade\Db;
use think\facade\Validate;

/**
 * 砍价逻辑层
 * Class BargainLogic
 * @package app\shopapi\logic
 */
class BargainLogic extends BaseLogic
{
    /**
     * @notes 查看砍价商品详情
     * @param $params
     * @author Tab
     * @date 2021/8/28 16:13
     */
    public static function detail($params)
    {
        // 商品信息
        $field = [
            'id' => 'goods_id',
            'name' => 'goods_name',
            'image',
            'total_stock',
            'max_price' => 'goods_max_price',
            'unit_id',

        ];
        $goods = Goods::field($field)->findOrEmpty($params['goods_id']);

        // 获取规格项-规格值-组合
        $goods['spec_value_list'] = self::specValueList($params, $goods);

        // 获取规格项-规格值
        $goods['spec_value'] = self::specValue($params);

        // 商品图片重命名
        $goods['goods_image'] = $goods['image'];

        // 增加活动商品浏览量
        $where = [
            'activity_id' => $params['activity_id'],
            'goods_id' => $params['goods_id'],
        ];
        $bargainGoods =  BargainGoods::where($where)->findOrEmpty();
        BargainGoods::where($where)->update([
            'visited' => $bargainGoods['visited'] + 1
        ]);
        // 增加活动浏览量
        $bargainActivity = BargainActivity::findOrEmpty($params['activity_id']);
        $bargainActivity->visited = $bargainActivity->visited + 1;
        $bargainActivity->duokaiSave();

        $goods['unit_name'] = '';
        $hidden[] = 'unit_id';
        if($goods['unit_id']){
            $goods['unit_name'] = $goods->unit->name;
            $hidden[] ='unit';
        }
        return $goods->hidden($hidden)->toArray();

    }

    /**
     * @notes 规格项-规格值
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/30 10:01
     */
    public static function specValue($params)
    {
        $specList = GoodsSpec::where('goods_id', $params['goods_id'])->select()->toArray();
        $specValueLists = GoodsSpecValue::where('goods_id', $params['goods_id'])
            ->select()
            ->toArray();
        $specValue = [];
        foreach ($specList as $spec) {
            foreach ($specValueLists as $item) {
                if ($item['spec_id'] == $spec['id']) {
                    $spec['spec_list'][] = $item;
                }
            }
            $specValue[] = $spec;
        }

        return $specValue;
    }

    /**
     * @notes 规格项-规格值-组合
     * @param $params
     * @author Tab
     * @date 2021/9/30 10:13
     */
    public static function specValueList($params, &$goods)
    {
        $field = [
            'gi.*',
            'bg.activity_id',
            'bg.first_knife',
            'bg.floor_price',
        ];
        $lists = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods_item gi', 'gi.id = bg.item_id')
            ->field($field)
            ->where([
                'bg.activity_id' => $params['activity_id'],
                'bg.goods_id' => $params['goods_id'],
            ])
            ->select()
            ->toArray();

        foreach ($lists  as &$item) {
            if (!isset($goods['min_floor_price'])) {
                $goods['min_price'] = $item['floor_price'];
            } else {
                $goods['min_price'] = min($goods['min_floor_price'], $item['floor_price']);
            }
            $item['image'] = empty($item['image']) ? $goods['image'] : FileService::getFileUrl($item['image']);
        }

        return $lists;
    }

    /**
     * @notes 发起砍价
     * @param $params
     * @return false
     * @author Tab
     * @date 2021/8/28 16:56
     */
    public static function initiate($params)
    {
        Db::startTrans();
        try {
            // 校验是否允许发起砍价活动
            self::checkInitiate($params);
            // 发起砍价
            $result = self::addInitiate($params);

            Db::commit();
            return $result;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 校验是否允许发起砍价
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/8/28 16:53
     */
    public static function checkInitiate($params)
    {
        $bargainActivity = BargainActivity::findOrEmpty($params['activity_id'])->toArray();

        if (empty($bargainActivity)) {
            throw new \Exception('砍价活动不存在');
        }
        if (strtotime($bargainActivity['start_time']) > time()) {
            throw new \Exception('砍价活动未开始');
        }
        if (strtotime($bargainActivity['end_time']) <= time()) {
            throw new \Exception('砍价活动已结束');
        }
        $count = BargainInitiate::where([
            'activity_id' => $params['activity_id'],
            'user_id' => $params['user_id'],
        ])->count();
        if ($count >= $bargainActivity['count']) {
            throw new \Exception('发起砍价次数超出限制');
        }
//        if ($bargainActivity['buy_limit'] && $params['goods_num'] < $bargainActivity['buy_limit']) {
//            throw new \Exception('购买数量低于起购数量');
//        }
        if ($bargainActivity['order_limit'] && $params['goods_num'] > $bargainActivity['order_limit']) {
            throw new \Exception('购买数量超出每单限制');
        }
    }

    /**
     * @notes 添加砍价记录
     * @param $params
     * @author Tab
     * @date 2021/8/28 17:27
     */
    public static function addInitiate($params)
    {
        // 砍价活动信息
        $bargainActivity = BargainActivity::findOrEmpty($params['activity_id'])->toArray();
        // 砍价商品信息
        $field = [
            'bg.goods_id',
            'bg.item_id',
            'bg.first_knife',
            'bg.floor_price',
            'g.name' => 'goods_name',
            'g.image' => 'goods_image',
            'gi.image' => 'item_image',
            'gi.spec_value_str' => 'item_spec_value_str',
            'gi.sell_price' => 'item_sell_price',
        ];
        $bargainGoods = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods g', 'g.id = bg.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = bg.item_id')
            ->field($field)
            ->where([
                'bg.activity_id' => $params['activity_id'],
                'bg.item_id' => $params['item_id'],
            ])
            ->findOrEmpty()
            ->toArray();

        $bargainGoods['goods_num'] = $params['goods_num'];

        $data = [
            'sn' => generate_sn(new BargainInitiate(), 'sn'),
            'activity_id' => $params['activity_id'],
            'user_id' => $params['user_id'],
            'bargain_snapshot' => $bargainActivity,
            'goods_snapshot' => $bargainGoods,
            'help_num' => 0,
            'current_price' => $bargainGoods['item_sell_price'],
            'floor_price' => $bargainGoods['floor_price'],
            'first_knife' => $bargainGoods['first_knife'],
            'start_time' => time(),
            'end_time' => time() + 60 * $bargainActivity['valid_period'],
            'status' => BargainEnum::STATUS_ING,
        ];
        // 添加砍价记录
        $bargainInitiate = BargainInitiate::duokaiCreate($data);

        // 判断是否允许自己参与砍价
        if ($bargainActivity['self']) {
            // 校验是否允许发起帮砍
            self::checkHelp($bargainInitiate->id, $params['user_id']);
            // 生成帮助砍价记录
            $result = self::addHelp($bargainInitiate->id, $params['user_id']);
            // 返回帮砍信息
            return $result;
        }
        // 不能自己帮自己砍价
        return ['initiate_id' => $bargainInitiate->id];
    }

    /**
     * @notes 帮助砍价
     * @param $initiateId
     * @param $userId
     * @throws \Exception
     * @author Tab
     * @date 2021/8/28 17:41
     */
    public static function help($params)
    {
        Db::startTrans();
        try {
            // 校验是否允许发起帮砍
            self::checkHelp($params['initiate_id'], $params['user_id']);
            // 生成帮助砍价记录
            $result = self::addHelp($params['initiate_id'], $params['user_id']);

            Db::commit();
            return $result;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 校验是否允许发起帮砍
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/8/28 17:34
     */
    public static function checkHelp($initiateId, $userId)
    {
        $bargainInitiate = BargainInitiate::findOrEmpty($initiateId)->toArray();
        if (empty($bargainInitiate)) {
            throw new \Exception('砍价发起记录不存在');
        }

        $bargainActivity = BargainActivity::findOrEmpty($bargainInitiate['activity_id'])->toArray();
        if (empty($bargainActivity)) {
            throw new \Exception('砍价活动不存在');
        }
        if (strtotime($bargainActivity['start_time']) > time()) {
            throw new \Exception('砍价活动未开始');
        }
        if (strtotime($bargainActivity['end_time']) <= time()) {
            throw new \Exception('砍价活动已结束');
        }
        if (!$bargainActivity['self'] && $userId == $bargainInitiate['user_id']) {
            throw new \Exception('不能自己给自己砍价');
        }
        if ($bargainInitiate['status'] != BargainEnum::STATUS_ING) {
            throw new \Exception('非砍价中状态不允许助力');
        }
        if (($bargainInitiate['start_time'] + $bargainActivity['valid_period'] * 60) <= time()) {
            throw new \Exception('已过砍价有效期');
        }
        $count = BargainHelp::where([
            'initiate_id' => $initiateId,
            'user_id' => $userId
        ])->count();
        if ($count > 0) {
            throw new \Exception('不能重复帮助砍价哦');
        }
    }

    /**
     * @notes 添加帮助砍价记录
     * @param $initiateId
     * @param $userId
     * @author Tab
     * @date 2021/8/28 18:07
     */
    public static function addHelp($initiateId, $userId)
    {
        // 计算帮砍信息
        $calcHelp = self::calcHelp($initiateId);
        $data = [
           'initiate_id' => $initiateId,
           'user_id' => $userId,
           'reduce_amount' => $calcHelp['reduce_amount'],
        ];
        // 添加帮砍记录
        BargainHelp::duokaiCreate($data);

        // 更新砍价记录
        $bargainInitiate = BargainInitiate::findOrEmpty($initiateId);
        $bargainInitiate->help_num = $bargainInitiate->help_num + 1;
        $bargainInitiate->current_price = $calcHelp['current_price'];

        if ($calcHelp['current_price'] <= $bargainInitiate['floor_price']) {
            // 砍价成功
            $bargainInitiate->status = BargainEnum::STATUS_SUCCESS;
            $bargainInitiate->end_time = time();
        }

        $bargainInitiate->duokaiSave();

        // 返回帮砍信息
        return $calcHelp;
    }

    /**
     * @notes 计算帮砍信息
     * @param $initiateId
     * @return int[]
     * @author Tab
     * @date 2021/8/28 18:09
     */
    public static function calcHelp($initiateId)
    {
        $bargainInitiate = BargainInitiate::findOrEmpty($initiateId)->toArray();
        $bargainActivity = BargainActivity::findOrEmpty($bargainInitiate['activity_id'])->toArray();
        // 判断是否为首刀
        $count = BargainHelp::where('initiate_id', $bargainInitiate['id'])->count();
        if ($count == 0) {
            // 首刀
            $reduceAmount = $bargainInitiate['first_knife'];
        } else {
            // 非首刀
            $reduceAmount = self::calcReduceAmount($bargainInitiate, $bargainActivity);
        }

        // 计算帮砍后价格
        $currentPrice = round($bargainInitiate['current_price'] - $reduceAmount, 2);
        // 距离底价还差多少金额
        $diffPrice = round($currentPrice - $bargainInitiate['floor_price'], 2);
        // 一共需要砍的价格
        $needHelp = round($bargainInitiate['goods_snapshot']['item_sell_price'] - $bargainInitiate['floor_price'], 2);
        // 特殊处理: 一刀砍到低于底价的情况(例如：首刀金额设置过高的情况)
        if ($currentPrice < $bargainInitiate['floor_price']) {
            // 将帮砍后的价格设置成与底价一样
            $currentPrice = $bargainInitiate['floor_price'];
            // 将帮砍价格设置为当前价格 - 底价价格
            $reduceAmount = round($bargainInitiate['current_price'] - $bargainInitiate['floor_price'], 2);
            $diffPrice = 0;
        }
        return [
            'reduce_amount' => doubleval($reduceAmount),
            'current_price' => $currentPrice,
            'diff_price' => $diffPrice,
            'floor_price' => $bargainInitiate['floor_price'],
            'need_help' => $needHelp,
            'initiate_id' => intval($initiateId)
        ];
    }

    /**
     * @notes 计算当前帮砍金额
     * @param $bargainInitiate
     * @param $bargainActivity
     * @return float
     * @author Tab
     * @date 2021/8/28 19:01
     */
    public static function calcReduceAmount($bargainInitiate, $bargainActivity)
    {
        // 剩余刀数 = 帮砍次数 - 已砍次数
        $knifeCount = $bargainActivity['help_num'] - $bargainInitiate['help_num'];
        // 距离底价还差多少金额 = 当前价格 - 底价
        $leftAmount = $bargainInitiate['current_price'] - $bargainInitiate['floor_price'];

        // 每刀固定金额 = 距离底价还差多少金额 / 剩余刀数
        if ($bargainActivity['knife_amount_type'] == BargainEnum::KNIFE_TYPE_FIXED) {
            $reduceAmount = round($leftAmount / $knifeCount, 2);
            return $reduceAmount;
        }

        // 随机金额 && 非最后一刀
        if ($bargainActivity['knife_amount_type'] == BargainEnum::KNIFE_TYPE_RAND && $knifeCount != 1) {
            $leftAmount = round($leftAmount / 2, 2);
            $rand = mt_rand(0, $leftAmount * 100);
            $reduceAmount = round($rand / 100, 2);
            return $reduceAmount;
        }

        // 随机金额 && 最后一刀
        if ($bargainActivity['knife_amount_type'] == BargainEnum::KNIFE_TYPE_RAND && $knifeCount == 1) {
            $reduceAmount = $leftAmount;
            return $reduceAmount;
        }
    }

    /**
     * @notes 查看砍价进度
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/18 16:52
     */
    public static function bargainProgress($params)
    {
        $bargainInitiate = BargainInitiate::field('*,status as status_desc')->findOrEmpty($params['initiate_id'])->toArray();
        $bargainInitiate['goods_image'] = get_image([
            $bargainInitiate['goods_snapshot']['item_image'], $bargainInitiate['goods_snapshot']['goods_image']
        ]);
        $bargainInitiate['goods_id'] = $bargainInitiate['goods_snapshot']['goods_id'];
        $bargainInitiate['goods_name'] = $bargainInitiate['goods_snapshot']['goods_name'];
        $bargainInitiate['item_spec_value_str'] = $bargainInitiate['goods_snapshot']['item_spec_value_str'];
        $bargainInitiate['item_sell_price'] = $bargainInitiate['goods_snapshot']['item_sell_price'];
        $bargainInitiate['help_record'] = self::helpRecord($bargainInitiate);
        $bargainInitiate['help_total'] = round($bargainInitiate['item_sell_price'] - $bargainInitiate['current_price'], 2);
        $bargainInitiate['help_diff'] = round($bargainInitiate['current_price'] - $bargainInitiate['floor_price'], 2);
        $bargainInitiate['btns'] = BargainInitiate::getBtns($bargainInitiate);
        unset($bargainInitiate['goods_snapshot']);
        unset($bargainInitiate['bargain_snapshot']);
        unset($bargainInitiate['create_time']);
        unset($bargainInitiate['update_time']);
        unset($bargainInitiate['delete_time']);

        return $bargainInitiate;
    }

    /**
     * @notes 帮砍信息
     * @param $bargainInitiate
     * @return array
     * @author Tab
     * @date 2021/9/18 16:25
     */
    public static function helpRecord($bargainInitiate)
    {
        $field = [
            'u.nickname',
            'u.avatar',
            'bh.reduce_amount',
        ];
        $lists = BargainHelp::withoutGlobalScope()
            ->aliasSid('bh')
            ->alias('bh')
            ->leftJoin('user u', 'u.id = bh.user_id')
            ->field($field)
            ->withAttr('avatar', function ($value) {
                return empty($value) ? '' : FileService::getFileUrl(trim($value, '/'));
            })
            ->where('bh.initiate_id', $bargainInitiate['id'])
            ->order('bh.id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 分享帮砍详情
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/18 17:47
     */
    public static function shareDetail($params)
    {
        $field = [
            'id',
            'activity_id',
            'user_id',
            'goods_snapshot',
            'end_time',
        ];
        $bargainInitiate = BargainInitiate::field($field)->findOrEmpty($params['initiate_id'])->toArray();
        $bargainInitiate['initiate_user'] = User::field('avatar,nickname')->findOrEmpty($bargainInitiate['user_id'])->toArray();
        $bargainInitiate['goods_image'] = get_image([
            $bargainInitiate['goods_snapshot']['item_image'], $bargainInitiate['goods_snapshot']['goods_image']
        ]);
        $bargainInitiate['goods_id'] = $bargainInitiate['goods_snapshot']['goods_id'];
        $bargainInitiate['goods_name'] = $bargainInitiate['goods_snapshot']['goods_name'];
        $bargainInitiate['item_spec_value_str'] = $bargainInitiate['goods_snapshot']['item_spec_value_str'];
        $bargainInitiate['item_sell_price'] = $bargainInitiate['goods_snapshot']['item_sell_price'];
        $bargainInitiate['help_record'] = self::helpRecord($bargainInitiate);
        $bargainInitiate['btns'] = BargainInitiate::getBtns2($bargainInitiate, $params['user_id']);
        $bargainInitiate['tips'] = $bargainInitiate['end_time'] <= time() ? '砍价已结束，去看看其他商品吧~' : '';
        unset($bargainInitiate['goods_snapshot']);

        return $bargainInitiate;
    }

}