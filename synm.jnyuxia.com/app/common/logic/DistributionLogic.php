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

use app\common\enum\DistributionConfigEnum;
use app\common\enum\YesNoEnum;
use app\common\model\Distribution;
use app\common\model\DistributionConfig;
use app\common\model\DistributionLevel;
use app\common\model\User;

/**
 * 分销基础信息逻辑层
 * Class DistributionLogic
 * @package app\common\logic
 */
class DistributionLogic extends BaseLogic
{
    /**
     * @notes 添加分销基础信息记录
     * @param $userId
     * @author Tab
     * @date 2021/8/4 11:45
     */
    public static function add($userId)
    {
        $defaultLevel = DistributionLevel::where('is_default', 1)->findOrEmpty()->toArray();
        if (empty($defaultLevel)) {
            // 没有默认等级，初始化
            DistributionLevel::duokaiCreate([
                'name' => '默认等级',
                'weights' => '1',
                'is_default' => '1',
                'remark' => '默认等级',
                'update_relation' => '1'
            ]);
        }
        // 默认分销会员等级
        $defaultLevelId = DistributionLevel::where('is_default', YesNoEnum::YES)->value('id');
        // 分销会员开通方式
        $open = DistributionConfig::where('key', 'open')->value('value');
        $open = $open ?: DistributionConfigEnum::DEFAULT_OPEN;
        $isDistribution = $open == DistributionConfigEnum::OPEN_ALL ? YesNoEnum::YES : YesNoEnum::NO;

        $data = [
            'user_id' => $userId,
            'level_id' => $defaultLevelId,
            'is_distribution' => $isDistribution,
            'is_freeze' => YesNoEnum::NO,
            'remark' => '',
        ];

        if($isDistribution) {
            // 成为分销会员时间
            $data['distribution_time'] = time();
        }

        Distribution::duokaiCreate($data);
    }
}