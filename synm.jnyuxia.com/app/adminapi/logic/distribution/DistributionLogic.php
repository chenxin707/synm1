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

use app\common\logic\BaseLogic;
use app\common\model\Distribution;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\model\User;

/**
 * 分销基础信息逻辑层
 * Class DistributionLogic
 * @package app\adminapi\logic\distribution
 */
class DistributionLogic extends BaseLogic
{
    /**
     * @notes 冻结/解冻分销会员
     * @param $params
     * @return false|string
     * @author Tab
     * @date 2021/8/5 14:43
     */
    public static function freeze($params)
    {
        try {
            $distribution = Distribution::where('user_id', $params['id'])->findOrEmpty();
            if ($distribution->isEmpty()) {
                throw new \think\Exception('分销商不存在');
            }
            $distribution->is_freeze = !$distribution->is_freeze;
            $distribution->duokaiSave();

            return $distribution->is_freeze ? '冻结成功' : '解冻成功';
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 分销信息(用于用户信息面板)
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/8/5 15:12
     */
    public static function info($params)
    {
        $field = 'd.level_id,u.first_leader,distribution_time';
        $distribution = Distribution::withoutGlobalScope()
            ->aliasSid('d')
            ->alias('d')
            ->leftJoin('user u', 'u.id = d.user_id')
            ->field($field)
            ->where('user_id', $params['id'])
            ->findOrEmpty()
            ->toArray();
        if(empty($distribution)) {
            return [];
        }
        $distribution['level_name'] = DistributionLevel::getLevelName($distribution['level_id']);
        $distribution['return'] = DistributionOrderGoods::getEarnings($params['id']);
        $distribution['un_return'] = DistributionOrderGoods::getUnReturnedCommission($params['id']);
        $distribution['fans'] = User::getFans($params['id']);
        $distribution['level_one_fans'] = User::getLevelOneFans($params['id']);
        $distribution['level_two_fans'] = User::getLevelTwoFans($params['id']);
        $distribution['first_leader_name'] = User::getNickname($distribution['first_leader']);

        return $distribution;
    }
}