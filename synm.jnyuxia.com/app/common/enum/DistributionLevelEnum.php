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

namespace app\common\enum;

/**
 * 分销会员等级枚举
 * Class DistributionLevelEnum
 * @package app\common\enum
 */
class DistributionLevelEnum
{
    /**
     * 升级条件允许的字段
     * singleConsumptionAmount 单笔消费金额
     * cumulativeConsumptionAmount 累计消费金额
     * cumulativeConsumptionTimes 累计消费次数
     * returnedCommission 已结算佣金收入
     */
    const UPDATE_CONDITION_FIELDS = ['singleConsumptionAmount', 'cumulativeConsumptionAmount', 'cumulativeConsumptionTimes', 'returnedCommission'];

    /**
     * OR升级关系
     */
    const UPDATE_RELATION_OR = 1;

    /**
     * AND 升级关系
     */
    const UPDATE_RELATION_AND = 2;

    /**
     * 默认等级状态
     * 0-自定义等级
     * 1-系统默认等级
     */
    const IS_DEFAULT_YES = 1;

    /**
     * @notes 获取键对应值的字段名
     * @param $key
     * @return string
     * @author Tab
     * @date 2021/7/22 14:03
     */
    public static function getValueFiled($key)
    {
        switch($key) {
            case 'singleConsumptionAmount':
            case 'cumulativeConsumptionAmount':
            case 'returnedCommission':
                return 'value_decimal';
            case 'cumulativeConsumptionTimes':
                return 'value_int';
            default:
                return 'value_text';
        }
    }
}