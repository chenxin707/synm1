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
 * 积分商品
 * Class IntegralGoodsEnum
 * @package app\common\enum
 */
class IntegralGoodsEnum
{
    // 兑换类型
    const TYPE_GOODS = 1;  //商品
    const TYPE_BALANCE = 2;  //红包（余额）

    // 商品状态
    const STATUS_SOLD_OUT   = 0;  //下架
    const STATUS_SHELVES    = 1;  //上架中


    // 兑换方式
    const EXCHANGE_WAY_INTEGRAL = 1; // 积分
    const EXCHANGE_WAY_HYBRID = 2; // 积分 + 金额


    // 物流类型
    const DELIVERY_NO_EXPRESS = 0; // 无需物流
    const DELIVERY_EXPRESS = 1; // 快递配送

    // 运费
    const EXPRESS_TYPE_FREE = 1; // 包邮
    const EXPRESS_TYPE_UNIFIED = 2; // 统一运费

    /**
     * @notes 兑换类型
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/2/25 17:48
     */
    public static function getTypeDesc($type = true)
    {
        $desc = [
            self::TYPE_GOODS => '商品',
            self::TYPE_BALANCE => '红包',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '';
    }
}