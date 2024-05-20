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
 * 分销配置枚举
 * Class DistributionConfigEnum
 * @package app\common\enum
 */
class DistributionConfigEnum
{
    /**
     * 分销开关
     * 关闭后不产生新的分销佣金，商城分销入口会关闭
     * 0-关闭  1-开启
     */
    const DEFAULT_SWITCH = 0;

    /**
     * 分销层级
     * 控制哪些层级的分销才能获得分销佣金
     * 1-一级分销 2-二级分销
     */
    const DEFAULT_LEVEL = 2;
    const LEVEL_SELF = 0;
    const LEVEL_ONE = 1;
    const LEVEL_TWO = 2;

    /**
     * 自购返佣
     * 0-关闭 1-开启
     */
    const DEFAULT_SELF = 0;

    /**
     * 开通分销会员条件
     * OPEN_ALL 无条件(全员分销)
     * OPEN_APPLY 申请分销
     * OPEN_ASSIGN 指定分销
     */
    const DEFAULT_OPEN = 2;
    const OPEN_ALL = 1;
    const OPEN_APPLY = 2;
    const OPEN_ASSIGN = 3;

    /**
     * 申请首页宣传图
     */
    const DEFAULT_APPLY_IMAGE = 'resource/image/adminapi/default/apply_image.png';

    /**
     * 申请协议是否显示
     * 0-隐藏 / 1-显示
     */
    const DEFAULT_PROTOCOL_SHOW = 1;

    /**
     * 申请协议内容
     */

    const DEFAULT_PROTOCOL_CONTENT = '';

    /**
     * 分享海报
     */
    const DEFAULT_POSTER = 'resource/image/adminapi/default/distribution_poster.png';

    /**
     * 佣金计算方式
     * 1-按商品实际金额
     */
    const DEFAULT_CAL_METHOD = 1;
    const CAL_BY_PAYMENT_AMOUNT = 1;

    /**
     * 结算时机
     * 1-订单完成后
     */
    const DEFAULT_SETTLEMENT_TIMING = 1;

    /**
     * 结算时机多少天后
     */
    const DEFAULT_SETTLEMENT_TIME = 7;
}