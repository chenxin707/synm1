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

namespace app\common\model;

use app\common\enum\BargainEnum;

class BargainInitiate extends BaseModel
{
    protected $autoWriteTimestamp = true;

    // 设置json类型字段
    protected $json = ['goods_snapshot', 'bargain_snapshot'];
    // 设置json数据返回数组
    protected $jsonAssoc = true;
    // 设置JSON字段的类型
    protected $jsonType = [
        'goods_snapshot->goods_id'	=>	'int',
        'goods_snapshot->item_id'	=>	'int',
    ];

    /**
     * @notes 状态描述获取器
     * @param $value
     * @return string|string[]
     * @author Tab
     * @date 2021/9/18 11:33
     */
    public function getStatusDescAttr($value)
    {
        return BargainEnum::getStatusDesc($value);
    }

    /**
     * @notes 砍价进度 - 按钮
     * @param $bargainInitiate
     * @return array
     * @author Tab
     * @date 2021/9/18 16:43
     */
    public static function getBtns($bargainInitiate)
    {
        $helpBtn = $buyNowBtn = $buyAllowBtn = $viewOrderBtn = false;

        // 砍价成功已下单
        if ($bargainInitiate['status'] == BargainEnum::STATUS_SUCCESS && empty($bargainInitiate['order_id'])) {
            $viewOrderBtn = true;
        }

        // 砍价成功未下单
        if ($bargainInitiate['status'] == BargainEnum::STATUS_SUCCESS && empty($bargainInitiate['order_id'])) {
            $buyAllowBtn = true;
        }

        // 砍价中 且 任意金额可购买
        if ($bargainInitiate['status'] == BargainEnum::STATUS_ING && $bargainInitiate['bargain_snapshot']['buy_condition'] == BargainEnum::BUY_CONDITION_RAND) {
            $buyNowBtn = true;
        }

        // 砍价中 且 砍价未结束
        if ($bargainInitiate['status'] == BargainEnum::STATUS_ING && $bargainInitiate['end_time'] > time()) {
            $helpBtn = true;
        }

        return [
            // 邀请好友帮砍按钮
            'help_btn' => $helpBtn,
            // 直接购买按钮
            'buy_now_btn' => $buyNowBtn,
            //  立即购买按钮
            'buy_allow_btn' => $buyAllowBtn,
            // 查看订单按钮
            'view_order_btn' => $viewOrderBtn
        ];
    }

    /**
     * @notes 分享砍价详情 - 按钮
     * @param $bargainInitiate
     * @param $userId
     * @return array
     * @author Tab
     * @date 2021/9/18 17:42
     */
    public static function getBtns2($bargainInitiate, $userId)
    {
        $initiateBtn = $helpBtn = false;

        // 砍价活动列表
        $where = [
            // 进行中状态
            ['ba.status', '=', BargainEnum::ACTIVITY_STATUS_ING],
            // 已到活动开始时间
            ['ba.start_time', '<=', time()],
            // 未到活动结束时间
            ['ba.end_time', '>', time()],
        ];

        $field = [
            'g.image' => 'goods_image',
            'g.name' => 'goods_name',
            'g.max_price' => 'goods_max_price',
            'ba.end_time',
            'bg.activity_id' => 'bargain_min_price',
            'bg.activity_id',
            'bg.goods_id',
        ];
        $count = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods g', 'g.id = bg.goods_id')
            ->leftJoin('bargain_activity ba', 'ba.id = bg.activity_id')
            ->distinct(true)
            ->field($field)
            ->where($where)
            ->select()
            ->toArray();
        if ($count > 0) {
            $initiateBtn = true;
        }

        // 查询是否帮砍过
        $count = BargainHelp::where([
            'initiate_id' => $bargainInitiate['id'],
            'user_id' => $userId,
        ])->count();
        // 砍价活动未结束 且 未帮砍过
        if ($bargainInitiate['end_time'] > time() && $count == 0) {
            $helpBtn = true;
        }

        return [
            // 我也要发起砍价按钮
            'initiate_btn' => $initiateBtn,
            // 帮助砍价按钮
            'help_btn' => $helpBtn,
        ];
    }

    /**
     * @notes 商品信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/9/24 16:43
     */
    public function searchGoodsInfoAttr($query, $value, $data)
    {
        if (isset($data['goods_info']) && !empty($data['goods_info'])) {
            $goodsIds = Goods::where('name|code', 'like', '%'. $data['goods_info'] .'%')->column('id');
            $initiateIds = BargainInitiate::where('goods_snapshot->goods_id', 'in', $goodsIds)->column('id');
            $query->where('bi.id', 'in', $initiateIds);
        }
    }

    /**
     * @notes 用户信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/9/24 16:52
     */
    public function searchUserInfoAttr($query, $value, $data)
    {
        if (isset($data['user_info']) && !empty($data['user_info'])) {
            $userIds = User::where('sn|nickname', 'like', '%'. $data['user_info'] .'%')->column('id');
            $initiateIds = BargainInitiate::where('user_id', 'in', $userIds)->column('id');
            $query->where('bi.id', 'in', $initiateIds);
        }
    }

    /**
     * @notes 活动信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/9/24 16:52
     */
    public function searchActivityInfoAttr($query, $value, $data)
    {
        if (isset($data['activity_info']) && !empty($data['activity_info'])) {
            $activityIds = BargainActivity::where('name', 'like', '%'. $data['activity_info'] .'%')->column('id');
            $query->where('bi.activity_id', 'in', $activityIds);
        }
    }
}