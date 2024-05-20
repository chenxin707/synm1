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

namespace app\adminapi\logic\finance;

use app\adminapi\logic\distribution\DistributionDataLogic;
use app\common\enum\AfterSaleEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\Order;
use app\common\model\User;

/**
 * 财务逻辑层
 * Class FinanceLogic
 * @package app\adminapi\logic\finance
 */
class FinanceLogic extends BaseLogic
{
    /**
     * @notes 数据中心
     * @return array
     * @author Tab
     * @date 2021/8/25 19:55
     */
    public static function dataCenter()
    {
        // 订单总支付金额
        $orderSum = Order::where('pay_status', PayEnum::ISPAID)->sum('order_amount');
        // 订单总支付笔数
        $orderNum = Order::where('pay_status', PayEnum::ISPAID)->count('id');
        // 售后退款成功总金额
        $afterSaleSum = AfterSale::where('sub_status', AfterSaleEnum::SUB_STATUS_SELLER_REFUND_SUCCESS)->sum('refund_total_amount');
        // 用户总余额(不可提现)
        $userMoneySum = User::sum('user_money');
        // 用户总收入(可提现)
        $userEarningsSum = User::sum('user_earnings');
        // 用户总资产
        $userTotalAssets = round($userMoneySum + $userEarningsSum, 2);
        //分销订单收益
        $distributionData = DistributionDataLogic::earningsData();

        $data = [
            'order_sum' => $orderSum,
            'order_num' => $orderNum,
            'after_sale_sum' => $afterSaleSum,
            'user_money_sum' => $userMoneySum,
            'user_earnings_sum' => $userEarningsSum,
            'user_total_assets' => $userTotalAssets,
            'distribution_data' => $distributionData
        ];
        return $data;
    }
}