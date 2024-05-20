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
 * 幸运抽奖枚举
 */
class LuckyDrawEnum
{
    const NOT_WIN = 0;
    const INTEGRAL = 1;
    const COUPON = 2;
    const BALANCE = 3;

    const WAIT = 0;
    const ING = 1;
    const END = 2;

    /**
     * 所有奖品类型
     */
    const PRIZE_TYPE = [
        self::NOT_WIN,
        self::INTEGRAL,
        self::COUPON,
        self::BALANCE,
    ];

    /**
     * 中奖奖品类型
     */
    const WIN_PRIZE_TYPE = [
        self::INTEGRAL,
        self::COUPON,
        self::BALANCE,
    ];

    /**
     * @notes 获取奖品类型描述
     * @param null $prizeType
     * @return string|string[]
     * @author Tab
     * @date 2021/11/24 9:44
     */
    public static function getPrizeTypeDesc($prizeType = null)
    {
        $desc = [
            self::NOT_WIN => '未中奖',
            self::INTEGRAL => '积分',
            self::COUPON => '优惠券',
            self::BALANCE => '余额',
        ];
        if (is_null($prizeType)) {
            return $desc;
        }

        return $desc[$prizeType] ?? '';
    }

    /**
     * @notes 获取状态描述
     * @param null $status
     * @return string|string[]
     * @author Tab
     * @date 2021/11/24 14:02
     */
    public static function getStatusDesc($status = null)
    {
        $desc = [
            self::WAIT => '未开始',
            self::ING => '进行中',
            self::END => '已结束',
        ];
        if (is_null($status)) {
            return $desc;
        }

        return $desc[$status] ?? '';
    }
}