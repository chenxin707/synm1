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

use app\common\model\Admin;
use app\common\model\User;

/**
 * 售后日志枚举
 * Class AfterSaleLogEnum
 * @package app\common\enum
 */
class AfterSaleLogEnum
{
    /**
     * 整单退款场景
     */
    const BUYER_CANCEL_ORDER = 1;
    const SELLER_CANCEL_ORDER = 2;
    const ORDER_CLOSE = 3;

    /**
     * 操作人角色
     * ROLE_SYS 系统
     * ROLE_BUYER 买家
     * ROLE_SELLER 卖家
     */
    const ROLE_SYS = 1;
    const ROLE_BUYER = 2;
    const ROLE_SELLER = 3;

    /**
     * @notes 获取场景描述
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/2 10:17
     */
    public static function getSenceDesc($value)
    {
        $desc = [
            self::BUYER_CANCEL_ORDER => '买家取消订单',
            self::SELLER_CANCEL_ORDER => '卖家取消订单',
            self::ORDER_CLOSE => '支付回调时订单已关闭',
        ];

        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取操作者名称
     * @param $operatorID
     * @param $operatorRole
     * @return mixed|string
     * @author Tab
     * @date 2021/8/9 20:16
     */
    public static function getOpertorName($operatorID, $operatorRole)
    {
        switch ($operatorRole) {
            case self::ROLE_SYS:
                return '系统';
            case self::ROLE_BUYER:
                return User::where('id', $operatorID)->value('nickname');
            case self::ROLE_SELLER:
                return Admin::where('id', $operatorID)->value('name');
        }
        return '';
    }
}