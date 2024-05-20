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
 * 砍价枚举
 * Class BargainEnum
 * @package app\common\enum
 */
class BargainEnum
{
    /**
     * 砍价活动状态
     */
    const ACTIVITY_STATUS_WAIT = 1;
    const ACTIVITY_STATUS_ING = 2;
    const ACTIVITY_STATUS_END = 3;

    /**
     * 发起砍价状态
     */
    const STATUS_ING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;

    /**
     * 每刀价格类型
     */
    const KNIFE_TYPE_FIXED = 1;
    const KNIFE_TYPE_RAND = 2;

    /**
     * 购买条件
     */
    const BUY_CONDITION_RAND = 1;
    const BUY_CONDITION_FLOOR = 2;

    /**
     * @notes 获取砍价活动状态
     * @param null $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/27 14:21
     */
    public static function getActivityStatusDesc($value = null)
    {
        $desc = [
            self::ACTIVITY_STATUS_WAIT => '未开始',
            self::ACTIVITY_STATUS_ING => '进行中',
            self::ACTIVITY_STATUS_END => '已结束',
        ];

        if(is_null($value)) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }

    /**
     * @notes 发起砍价状态描述
     * @param null $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/28 15:19
     */
    public static function getStatusDesc($value = null)
    {
        $desc = [
            self::STATUS_ING => '砍价中',
            self::STATUS_SUCCESS => '砍价成功',
            self::STATUS_FAIL => '砍价失败',
        ];

        if(is_null($value)) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }

    /**
     * @notes 每刀金额类型描述
     * @param null $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/28 18:21
     */
    public static function getKnifeTypeDesc($value = null)
    {
        $desc = [
            self::KNIFE_TYPE_FIXED => '固定金额',
            self::KNIFE_TYPE_RAND => '任意金额',
        ];

        if(is_null($value)) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }
}