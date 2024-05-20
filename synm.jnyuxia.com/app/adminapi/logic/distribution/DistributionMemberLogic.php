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

namespace app\adminapi\logic\distribution;

use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Distribution;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\model\OrderGoods;
use app\common\model\User;

/**
 * 分销会员逻辑层
 * Class DistributionMemberLogic
 * @package app\adminapi\logic\distribution
 */
class DistributionMemberLogic extends BaseLogic
{
    /**
     * @notes 开通分销
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/7/27 17:49
     */
    public static function open($params)
    {
        try {
            $countIds = count($params['ids']);
            $countDistribution = Distribution::where('user_id', 'in', $params['ids'])->count();
            if ($countIds != $countDistribution) {
                throw new \Exception('存在不合法的ID');
            }
            $level = DistributionLevel::findOrEmpty($params['level_id']);
            if($level->isEmpty()) {
                throw new \Exception('无效的分销等级');
            }
            // 获取用户对应的distribution表id
            $distributionIds = Distribution::where('user_id', 'in', $params['ids'])->column('id', 'user_id');
            $updateData = [];
            $time = time();
            foreach($params['ids'] as $userId) {
                $updateData[] = [
                    'id' => $distributionIds[$userId],
                    'is_distribution' => YesNoEnum::YES,
                    'level_id' => $params['level_id'],
                    'distribution_time' => $time,
                ];
            }

            (new Distribution())->duokaiSaveAll($updateData, true, false, false);

            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    public static function detail($params)
    {
        $field = [
            'id' => 'distribution_id',
            'user_id',
            'level_id',
            'is_distribution',
            'is_freeze',
            'distribution_time',
        ];
        $distribution = Distribution::field($field)
            ->where('user_id', $params['user_id'])
            ->findOrEmpty()
            ->toArray();
        // 分销等级
        $distribution['level_name'] = DistributionLevel::getLevelName($distribution['level_id']);
        // 已入账佣金
        $distribution['earningns'] = DistributionOrderGoods::getEarnings($distribution['user_id']);
        // 待结算佣金
        $distribution['unreturned_commission'] = DistributionOrderGoods::getUnReturnedCommission($distribution['user_id']);
        // 分销订单数量
        $orderGoodsIds = DistributionOrderGoods::where('user_id', $distribution['user_id'])->column('order_goods_id');
        $orderIds = OrderGoods::distinct(true)->where('id', 'in', $orderGoodsIds)->column('order_id');
        $distribution['distribution_order_num'] = count($orderIds);
        // 用户信息
        $distribution['user_info'] = User::field('id,sn,nickname,first_leader')->findOrEmpty($distribution['user_id'])->toArray();
        // 上级分销商信息
        $distribution['first_leader_info'] = User::getFirstLeader($distribution['user_info']['first_leader'])['name'];
        // 下级人数
        $distribution['fans'] = User::getFans($distribution['user_id']);
        // 下级中有多少是分销商
        $distribution['fans_distribution'] = User::getFansDistribution($distribution['user_id']);
        // 下一级人数
        $distribution['fans_one'] = User::getLevelOneFans($distribution['user_id']);
        // 下一级中分销商人数
        $distribution['fans_one_distribution'] = User::getLevelOneFansDistribution($distribution['user_id']);
        // 下二级人数
        $distribution['fans_two'] = User::getLevelTwoFans($distribution['user_id']);
        // 下二级中分销商人数
        $distribution['fans_two_distribution'] = User::getLevelTwoFansDistribution($distribution['user_id']);

        return $distribution;
    }

    /**
     * @notes 调整分销等级界面信息
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/14 18:50
     */
    public static function adjustLevelInfo($params)
    {
        $field = [
            'u.sn',
            'u.nickname',
            'd.user_id',
            'd.level_id'
        ];
        $user = Distribution::withoutGlobalScope()
            ->aliasSid('d')
            ->alias('d')
            ->leftJoin('user u', 'u.id = d.user_id')
            ->field($field)
            ->where('d.user_id', $params['user_id'])
            ->findOrEmpty()
            ->toArray();
        $user['level_name'] = DistributionLevel::getLevelName($user['level_id']);
        $levels = DistributionLevel::order('weights', 'asc')->column('id,name,weights');

        return [
            'user' => $user,
            'levels' => $levels
        ];
    }

    /**
     * @notes 调整分销商等级
     * @param $params
     * @author Tab
     * @date 2021/9/14 18:58
     */
    public static function adjustLevel($params)
    {
        try {
            $distribution = Distribution::where('user_id', $params['user_id'])->findOrEmpty();
            if ($distribution->isEmpty()) {
                throw new \Exception('分销商不存在');
            }
            $level = DistributionLevel::findOrEmpty($params['level_id']);
            if ($level->isEmpty()) {
                throw new \Exception('分销等级不存在');
            }
            $distribution->level_id = $params['level_id'];
            $distribution->duokaiSave();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 冻结/解冻资格
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/9/14 19:11
     */
    public static function freeze($params)
    {
        try {
            $distribution = Distribution::where('user_id', $params['user_id'])->findOrEmpty();
            if ($distribution->isEmpty()) {
                throw new \Exception('分销商不存在');
            }
            $distribution->is_freeze = !$distribution->is_freeze;
            $distribution->duokaiSave();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 查看下级
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/14 19:25
     */
    public static function getFans($params)
    {
        $distribution['user_id'] = $params['user_id'];
        // 用户信息
        $distribution['user_info'] = User::field('id,sn,nickname,first_leader')->findOrEmpty($distribution['user_id'])->toArray();
        // 下级人数
        $distribution['fans'] = User::getFans($distribution['user_id']);
        // 下级中有多少是分销商
        $distribution['fans_distribution'] = User::getFansDistribution($distribution['user_id']);
        // 下一级人数
        $distribution['fans_one'] = User::getLevelOneFans($distribution['user_id']);
        // 下一级中分销商人数
        $distribution['fans_one_distribution'] = User::getLevelOneFansDistribution($distribution['user_id']);
        // 下二级人数
        $distribution['fans_two'] = User::getLevelTwoFans($distribution['user_id']);
        // 下二级中分销商人数
        $distribution['fans_two_distribution'] = User::getLevelTwoFansDistribution($distribution['user_id']);

        return $distribution;
    }
}