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

/**
 * 分销会员等级模型
 * Class DistributionLevel
 * @package app\common\model
 */
class DistributionLevel extends BaseModel
{
    /**
     * @notes 级别获取器
     * @param $value
     * @param $rule
     * @param $data
     * @return string
     * @author Tab
     * @date 2021/7/23 9:44
     */
    public function getWeightsDescAttr($value, $data)
    {
        $defaultStr = $data['is_default'] ? '级(默认等级)' : '级';
        return $value . $defaultStr;
    }

    /**
     * @notes 等级名称获取器
     * @param $value
     * @param $data
     * @return string
     * @author Tab
     * @date 2021/7/26 9:45
     */
    public function getNameDescAttr($value, $data)
    {
        $defaultStr = $data['is_default'] ? '(默认等级)' : '';
        return $value . $defaultStr;
    }

    /**
     * @notes 当前等级下的分销会员数
     * @param $value
     * @return int
     * @author Tab
     * @date 2021/7/23 9:48
     */
    public function getMemberNumAttr($value)
    {
        return Distribution::where('level_id', $value)->count();
    }

    /**
     * @notes 获取等级名称
     * @param $level_id
     * @return mixed|string
     * @author Tab
     * @date 2021/7/27 17:07
     */
    public static function getLevelName($level_id)
    {
        $level = self::findOrEmpty($level_id)->toArray();
        if($level) {
            return $level['name'] . '(' . $level['weights'] . '级)';
        }
        return '';
    }
}