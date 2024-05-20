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

use app\common\enum\DistributionOrderGoodsEnum;

class DistributionOrderGoods extends BaseModel
{
    /**
     * @notes 返佣状态获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/17 18:15
     */
    public function getStatusDescAttr($value)
    {
        $desc = [1 => '待返佣', 2 => '已结算', 3 => '已失效'];
        return $desc[$value] ?? '';
    }

    /**
     * @notes 结算时间获取器
     * @param $value
     * @return string|null
     * @author Tab
     * @date 2021/7/28 9:21
     */
    public function getSettlementTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s') : '';
    }

    /**
     * @notes 分销层级描述获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/5 14:12
     */
    public function getLevelDescAttr($value)
    {
        $desc = ['自购佣金', '一级分佣', '二级分佣'];
        return $desc[$value] ?? '';
    }

    /**
     * @notes 累计已入账佣金
     * @param $userId
     * @return float
     * @author Tab
     * @date 2021/7/27 17:18
     */
    public static function getEarnings($userId)
    {
        return self::where([
            'user_id' => $userId,
            'status' => DistributionOrderGoodsEnum::RETURNED
        ])->sum('earnings');
    }

    /**
     * @notes 累计未返还佣金
     * @param $userId
     * @return float
     * @author Tab
     * @date 2021/8/5 15:03
     */
    public static function getUnReturnedCommission($userId)
    {
        return self::where([
            'user_id' => $userId,
            'status' => DistributionOrderGoodsEnum::UN_RETURNED
        ])->sum('earnings');
    }

    /**
     * @notes 买家信息
     * @param $userId
     * @return User|array|\think\Model
     * @author Tab
     * @date 2021/8/5 13:57
     */
    public static function getBueyer($userId)
    {
        return User::field('id,sn,nickname,avatar')->findOrEmpty($userId);
    }

    /**
     * @notes 订单用户信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/9/22 16:49
     */
    public function searchUserIdAttr($query, $value, $data)
    {
        if (!isset($data['user_info']) || empty($data['user_info'])) {
            return $query;
        }
        $ids = User::where('sn|nickname', 'like', '%' . $data['user_info'] . '%')->column('id');
        return $query->where('o.user_id', 'in', $ids);
    }
}