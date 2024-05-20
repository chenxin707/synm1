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

use app\common\enum\DistributionApplyEnum;
use app\common\enum\DistributionOrderGoodsEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Distribution;
use app\common\model\DistributionApply;
use app\common\model\DistributionOrderGoods;
use app\common\model\User;
use app\common\service\FileService;

class DistributionDataLogic extends BaseLogic
{
    /**
     * @notes 分销数据中心
     * @return array
     * @author Tab
     * @date 2021/7/28 10:04
     */
    public static function dataCenter()
    {
        return [
            'earnings_data'     => self::earningsData(),
            'distribution_data' => self::distributionData()
        ];
    }

    /**
     * @notes 佣金概览
     * @return array
     * @author Tab
     * @date 2021/7/28 10:12
     */
    public static function earningsData()
    {
        $cumulativeRebatedCommission = DistributionOrderGoods::where(['status' => DistributionOrderGoodsEnum::RETURNED])->sum('earnings');
        $todayRebatedCommission = DistributionOrderGoods::where(['status' => DistributionOrderGoodsEnum::RETURNED])->whereDay('settlement_time')->sum('earnings');
        $cumulativeUnrefundedCommission = DistributionOrderGoods::where(['status' => DistributionOrderGoodsEnum::UN_RETURNED])->sum('earnings');
        $todayUnrefundedCommission = DistributionOrderGoods::where(['status' => DistributionOrderGoodsEnum::UN_RETURNED])->whereDay('create_time')->sum('earnings');

        return [
            'cumulative_rebated_commission' => $cumulativeRebatedCommission,
            'today_rebated_commission' => $todayRebatedCommission,
            'cumulative_unrefunded_commission' => $cumulativeUnrefundedCommission,
            'today_unrefunded_commission' => $todayUnrefundedCommission,
        ];
    }

    /**
     * @notes 分销概览
     * @return array
     * @author Tab
     * @date 2021/7/28 10:18
     */
    public static function distributionData()
    {
        $members = Distribution::count();
        $distributionMembers = Distribution::where(['is_distribution' => YesNoEnum::YES])->count();
        $auditWaitMembers = DistributionApply::where(['status' => DistributionApplyEnum::AUDIT_WAIT])->count();
        $distributionMemberPercentage = $members ? (round(($distributionMembers / $members), 2) * 100) : 0;

        return [
            'distribution_members' => $distributionMembers,
            'distribution_member_percentage' => $distributionMemberPercentage,
        ];
    }

    /**
     * @notes 分销商收入排行榜
     * @return mixed
     * @author Tab
     * @date 2021/7/28 10:28
     */
    public static function topMemberEarnings($params)
    {
       $lists =  DistributionOrderGoods::withoutGlobalScope()
            ->aliasSid('dog')
            ->alias('dog')
            ->leftJoin('distribution d', 'd.user_id = dog.user_id')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->field('sum(dog.earnings) as sum_earnings, dog.user_id, u.avatar, u.nickname')
            ->where('dog.status', DistributionOrderGoodsEnum::RETURNED)
            ->group('dog.user_id')
            ->order('sum_earnings', 'desc')
           ->limit(50)
           ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count =  DistributionOrderGoods::withoutGlobalScope()
            ->aliasSid('dog')
            ->alias('dog')
            ->leftJoin('distribution d', 'd.user_id = dog.user_id')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->field('sum(dog.earnings) as sum_earnings, dog.user_id, u.avatar, u.nickname')
            ->where('dog.status', DistributionOrderGoodsEnum::RETURNED)
            ->group('dog.user_id')
            ->order('sum_earnings', 'desc')
            ->limit(50)
            ->count();

        foreach($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
        }

        return [
            'lists' => $lists,
            'count' => $count,
            'page_no' => $params['page_no'],
            'page_size' => $params['page_size'],
        ];
    }

    /**
     * @notes 分销商下级人数排行榜
     * @return array
     * @author Tab
     * @date 2021/9/14 15:10
     */
    public static function topMemberFans($params)
    {
        $field = [
            'u.id',
            'u.sn',
            'u.nickname',
            'u.avatar',
            'u.id' => 'fans'
        ];
        $lists = User::withoutGlobalScope()
            ->aliasSid('u')
            ->alias('u')
            ->Join('distribution d', 'u.id = d.user_id')
            ->field($field)
            ->where('d.is_distribution', YesNoEnum::YES)
            ->order('fans', 'desc')
            ->select()
            ->toArray();
        // 获取排序列
        $sortColumn = array_column($lists, 'fans');
        // 根据下级人数排序
        array_multisort($sortColumn, SORT_DESC, SORT_NUMERIC, $lists);
        // 截取前50个
        $index = ($params['page_no'] - 1) * $params['page_size'];
        $newLists = array_slice($lists, $index, $params['page_size']);
        $count = count(array_slice($lists, 0, 50));

        return [
            'lists' => $newLists,
            'count' => $count,
            'page_no' => $params['page_no'],
            'page_size' => $params['page_size'],
        ];
    }
}