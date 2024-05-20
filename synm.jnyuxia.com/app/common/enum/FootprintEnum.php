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
 * 足迹气泡枚举
 * Class FootprintEnum
 * @package app\common\enum
 */
class FootprintEnum
{
    // 足迹汽泡时长：默认60分钟
    const DEFAULT_DURATION = 60;

    // 足迹汽泡状态：默认关闭
    const DEFAULT_STATUS = 0;

    // 访问商城
    const VISIT_MALL = 1;

    // 浏览商品
    const BROWSE_PRODUCT = 2;

    // 加入购物车
    const ADD_CART = 3;

    // 领取优惠券
    const GET_COUPONS = 4;

    // 下单结算
    const ORDER_SETTLEMENT = 5;
}