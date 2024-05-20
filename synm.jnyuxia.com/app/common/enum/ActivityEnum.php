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
 * 营销活动
 */
class ActivityEnum
{
    const COUPON = 0;   //优惠券
    const TEAM = 1;     //拼团活动
    const SECKILL = 2;  //秒杀活动
    const BARGAIN = 3;  //砍价活动

    const TYPE = [
        self::TEAM,
        self::SECKILL,
        self::BARGAIN,
    ];

    //可用虚拟商品的活动
    const USABLE_VIRTUAL = [
        self::COUPON,
    ];

    /**
     * @notes 获取营销活动描述
     * @param null $value
     * @return string|string[]
     * @author Tab
     * @date 2021/10/11 15:29
     */
    public static function getActivityDesc($value = null)
    {
        $desc = [
            self::TEAM => '拼团活动',
            self::SECKILL => '秒杀活动',
            self::BARGAIN => '砍价活动',
        ];
        if (is_null($value)) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }
}