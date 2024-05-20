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
 * 会员账户流水变动表枚举
 * Class AccountLogEnum
 * @package app\common\enum
 */
class AccountLogEnum
{
    /**
     * 变动类型命名规则：对象_动作_简洁描述
     * 动作 DEC-减少 INC-增加
     * 对象 BNW-不可提现余额(充值等) BW-可提现余额(分销收入、股东分红等) GROWTH-成长值 INTEGRAL-积分
     * BNW - BALANCE_NOT_WITHDRAWABLE 不可提现余额
     * BW - BALANCE_WITHDRAWABLE 可提现余额
     * 可提现余额类型 100-199(减少) 200-299(增加)
     * 不可提现余额类型 300-399(减少) 400-499(增加)
     * 成长值类型 500-599(减少) 600-699(增加)
     * 积分类型 700-799(减少) 800-899(增加)
     * 例：
     *  const BNW_DEC_ADMIN = 100; // 管理员减少余额
     *  const BNW_INC_ADMIN = 200; // 管理员增加余额
     */

    /**
     * 变动对象
     * BW 可提现余额
     * BNW 不可提现余额
     * GROWTH 成长值
     * INTEGRAL 积分
     */
    const BW = 1;
    const BNW = 2;
    const GROWTH = 3;
    const INTEGRAL = 4;

    /**
     * 动作
     * DEC 减少
     * INC 增加
     */
    const DEC = 1;
    const INC = 2;

    /**
     * 不可提现余额减少类型
     */
    const BNW_DEC_ADMIN = 100;
    const BNW_DEC_ORDER = 101;
    const BNW_DEC_TRANSFER = 102;

    /**
     * 不可提现余额增加类型
     */
    const BNW_INC_ADMIN = 200;
    const BNW_INC_RECHARGE = 201;
    const BNW_INC_CANCEL_ORDER = 202;
    const BNW_INC_AFTER_SALE = 203;
    const BNW_INC_WITHDRAW = 204;
    const BNW_INC_RECHARGE_GIVE = 205;
    const BNW_INC_TRANSFER = 206;
    const BNW_INC_LOTTERY = 207;
    const BNW_INC_INTEGRAL_ORDER = 208;


    /**
     * 可提现余额减少类型
     */
    const BW_DEC_WITHDRAWAL = 300;
    const BW_DEC_ADMIN      = 301;

    /**
     * 可提现余额增加类型
     */
    const BW_INC_REFUSE_WITHDRAWAL      = 400;
    const BW_INC_DISTRIBUTION_SETTLE    = 401;
    const BW_INC_PAYMENT_FAIL           = 402;
    const BW_INC_TRANSFER_FAIL          = 403;
    const BW_INC_ADMIN                  = 404;
    /**
     * 成长值减少类型
     */
    const GROWTH_DEC_ADMIN = 500;

    /**
     * 成长值增加类型
     */
    const GROWTH_INC_ADMIN = 600;
    const GROWTH_INC_SIGN = 601;
    const GROWTH_INC_RECHARGE = 602;
    const GROWTH_INC_ORDER = 603;

    /**
     * 积分减少类型
     */
    const INTEGRAL_DEC_ADMIN = 700;
    const INTEGRAL_DEC_ORDER = 701;
    const INTEGRAL_DEC_LOTTERY = 702;
    const INTEGRAL_DEC_INTEGRAL_ORDER = 703;


    /**
     * 积分增加类型
     */
    const INTEGRAL_INC_ADMIN = 800;
    const INTEGRAL_INC_SIGN = 801;
    const INTEGRAL_INC_RECHARGE = 802;
    const INTEGRAL_INC_ORDER = 803;
    const INTEGRAL_INC_REGISTER = 804;
    const INTEGRAL_INC_INVITE = 805;
    const INTEGRAL_INC_CANCEL_ORDER = 806;
    const INTEGRAL_INC_LOTTERY = 807;
    const INTEGRAL_INC_AWARD = 808;
    const INTEGRAL_INC_CANCEL_INTEGRAL = 809;



    /**
     * 不可提现余额（减少类型汇总）
     */
    const BNW_DEC = [
        self::BNW_DEC_ADMIN,
        self::BNW_DEC_ORDER,
        self::BNW_DEC_TRANSFER,
    ];

    /**
     * 不可提现余额（增加类型汇总）
     */
    const BNW_INC = [
        self::BNW_INC_ADMIN,
        self::BNW_INC_RECHARGE,
        self::BNW_INC_CANCEL_ORDER,
        self::BNW_INC_AFTER_SALE,
        self::BNW_INC_WITHDRAW,
        self::BNW_INC_RECHARGE_GIVE,
        self::BNW_INC_TRANSFER,
        self::BNW_INC_LOTTERY,
        self::BNW_INC_INTEGRAL_ORDER

    ];

    /**
     * 可提现余额（减少类型汇总）
     */
    const BW_DEC = [
        self::BW_DEC_ADMIN,
        self::BW_DEC_WITHDRAWAL,
    ];

    /**
     * 可提现余额(增加类型汇总）
     */
    const BW_INC = [
        self::BW_INC_REFUSE_WITHDRAWAL,
        self::BW_INC_DISTRIBUTION_SETTLE,
        self::BW_INC_PAYMENT_FAIL,
        self::BW_INC_TRANSFER_FAIL,
        self::BW_INC_ADMIN
    ];

    /**
     * 成长值(减少类型汇总)
     */
    const GROWTH_DEC = [
        self::GROWTH_DEC_ADMIN
    ];

    /**
     * 成长值(增加类型汇总)
     */
    const GROWTH_INC = [
        self::GROWTH_INC_ADMIN,
        self::GROWTH_INC_SIGN,
        self::GROWTH_INC_RECHARGE,
        self::GROWTH_INC_ORDER,
    ];

    /**
     * 积分(减少类型汇总)
     */
    const INTEGRAL_DEC = [
        self::INTEGRAL_DEC_ADMIN,
        self::INTEGRAL_DEC_ORDER,
        self::INTEGRAL_DEC_LOTTERY,
        self::INTEGRAL_DEC_INTEGRAL_ORDER
    ];

    /**
     * 积分(增加类型汇总)
     */
    const INTEGRAL_INC = [
        self::INTEGRAL_INC_ADMIN,
        self::INTEGRAL_INC_SIGN,
        self::INTEGRAL_INC_RECHARGE,
        self::INTEGRAL_INC_ORDER,
        self::INTEGRAL_INC_REGISTER,
        self::INTEGRAL_INC_INVITE,
        self::INTEGRAL_INC_CANCEL_ORDER,
        self::INTEGRAL_INC_LOTTERY,
        self::INTEGRAL_INC_AWARD,
        self::INTEGRAL_INC_CANCEL_INTEGRAL,

    ];

    /**
     * @notes 动作描述
     * @param $action
     * @param false $flag
     * @return string|string[]
     * @author Tab
     * @date 2021/8/9 17:34
     */
    public static function getActionDesc($action, $flag = false)
    {
        $desc = [
            self::DEC => '减少',
            self::INC => '增加',
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$action] ?? '';
    }

    /**
     * @notes 获取变动类型描述
     * @param $changeType
     * @param false $flag
     * @return string|string[]
     * @author Tab
     * @date 2021/8/3 18:48
     */
    public static function getChangeTypeDesc($changeType, $flag = false)
    {
        $desc = [
            self::BNW_DEC_ADMIN => '管理员减少余额',
            self::BNW_DEC_ORDER => '下单扣减余额',
            self::BNW_DEC_TRANSFER => '转账扣减余额',
            self::BNW_INC_ADMIN => '管理员增加余额',
            self::BNW_INC_RECHARGE => '充值增加余额',
            self::BNW_INC_CANCEL_ORDER => '取消订单退还余额',
            self::BNW_INC_AFTER_SALE => '售后退还余额',
            self::BNW_INC_WITHDRAW => '提现至余额',
            self::BNW_INC_RECHARGE_GIVE => '充值赠送余额',
            self::BNW_INC_TRANSFER => '转账增加余额',
            self::BNW_INC_LOTTERY => '幸运抽奖增加余额',
            self::BW_DEC_WITHDRAWAL => '佣金提现',
            self::BW_DEC_ADMIN      => '管理员减少余额',
            self::BW_INC_REFUSE_WITHDRAWAL => '拒绝提现回退金额',
            self::BW_INC_DISTRIBUTION_SETTLE => '分销结算增加收入',
            self::BW_INC_PAYMENT_FAIL => '付款失败回退金额',
            self::BW_INC_TRANSFER_FAIL => '转账失败回退金额',
            self::BW_INC_ADMIN          => '管理员增加余额',
            self::GROWTH_DEC_ADMIN => '管理员减少成长值',
            self::GROWTH_INC_ADMIN => '管理员增加成长值',
            self::GROWTH_INC_SIGN => '签到赠送成长值',
            self::GROWTH_INC_RECHARGE => '充值赠送成长值',
            self::GROWTH_INC_ORDER => '下单赠送成长值',
            self::INTEGRAL_DEC_ADMIN => '管理员减少积分',
            self::INTEGRAL_DEC_ORDER => '下单扣减积分',
            self::INTEGRAL_DEC_LOTTERY => '幸运抽奖扣减积分',
            self::INTEGRAL_INC_ADMIN => '管理员增加积分',
            self::INTEGRAL_INC_SIGN => '签到赠送积分',
            self::INTEGRAL_INC_RECHARGE => '充值赠送积分',
            self::INTEGRAL_INC_ORDER => '下单赠送积分',
            self::INTEGRAL_INC_REGISTER => '注册赠送积分',
            self::INTEGRAL_INC_INVITE => '邀请赠送积分',
            self::INTEGRAL_INC_CANCEL_ORDER => '取消订单退还积分',
            self::INTEGRAL_INC_LOTTERY => '幸运抽奖增加积分',
            self::INTEGRAL_INC_AWARD => '消费赠送积分',
            self::INTEGRAL_INC_CANCEL_INTEGRAL => '取消积分订单返还积分',
            self::INTEGRAL_DEC_INTEGRAL_ORDER => '积分商城下单扣减积分',
            self::BNW_INC_INTEGRAL_ORDER => '下单积分商城红包增加余额',
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$changeType] ?? '';
    }

    /**
     * @notes 获取不可提现余额类型描述
     * @return string|string[]
     * @author Tab
     * @date 2021/8/25 20:28
     */
    public static function getBnwChangeTypeDesc()
    {
        $bnwChangeType = self::getBnwChangeType();
        $changeTypeDesc = self::getChangeTypeDesc('',true);
        $bnwChangeTypeDesc = array_filter($changeTypeDesc, function($key)  use ($bnwChangeType) {
            return in_array($key, $bnwChangeType);
        }, ARRAY_FILTER_USE_KEY);
        return $bnwChangeTypeDesc;
    }

    /**
     * @notes 获取可提现余额类型描述
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/28 11:18
     */
    public static function getBwChangeTypeDesc()
    {
        $bwChangeType = self::getBwChangeType();
        $changeTypeDesc = self::getChangeTypeDesc('',true);
        $bwChangeTypeDesc = array_filter($changeTypeDesc, function($key)  use ($bwChangeType) {
            return in_array($key, $bwChangeType);
        }, ARRAY_FILTER_USE_KEY);
        return $bwChangeTypeDesc;
    }

    /**
     * @notes 获取积分类型描述
     * @return string|string[]
     * @author Tab
     * @date 2021/8/25 20:46
     */
    public static function getIntegralChangeTypeDesc()
    {
        $integralChangeType = self::getIntegralChangeType();
        $changeTypeDesc = self::getChangeTypeDesc('',true);
        $integralChangeTypeDesc = array_filter($changeTypeDesc, function($key)  use ($integralChangeType) {
            return in_array($key, $integralChangeType);
        }, ARRAY_FILTER_USE_KEY);
        return $integralChangeTypeDesc;
    }

    /**
     * @notes 获取可提现余额变动类型
     * @return int[]
     * @author Tab
     * @date 2021/8/3 19:02
     */
    public static function getBwChangeType()
    {
        $bw = array_merge(self::BW_DEC, self::BW_INC);
        return $bw;
    }

    /**
     * @notes 获取不可提现余额变动类型
     * @return int[]
     * @author Tab
     * @date 2021/8/3 19:03
     */
    public static function getBnwChangeType()
    {
        $bnw = array_merge(self::BNW_DEC, self::BNW_INC);
        return $bnw;
    }

    /**
     * @notes 获取余额变动类型
     * @return int[]
     * @author Tab
     * @date 2021/8/3 19:05
     */
    public static function getBalanceChangeType()
    {
        $bw = self::getBwChangeType();
        $bnw = self::getBnwChangeType();
        $balance = array_merge($bw, $bnw);
        return $balance;
    }

    /**
     * @notes 获取成长值变动类型
     * @return int[]
     * @author Tab
     * @date 2021/8/3 19:00
     */
    public static function getGrowthChangeType()
    {
        $growth = array_merge(self::GROWTH_DEC, self::GROWTH_INC);
        return $growth;
    }

    /**
     * @notes 获取积分变动类型
     * @return int[]
     * @author Tab
     * @date 2021/8/3 19:06
     */
    public static function getIntegralChangeType()
    {
        $integral = array_merge(self::INTEGRAL_DEC, self::INTEGRAL_INC);
        return $integral;
    }

    /**
     * @notes 获取变动对象
     * @param $changeType
     * @return false|string
     * @author Tab
     * @date 2021/8/4 9:36
     */
    public static function getChangeObject($changeType)
    {
        // 可提现余额
        $bw = self::getBwChangeType();
        if(in_array($changeType, $bw)) {
            return self::BW;
        }

        // 不可提现余额
        $bnw = self::getBnwChangeType();
        if(in_array($changeType, $bnw)) {
            return self::BNW;
        }

        // 成长值
        $growth = self::getGrowthChangeType();
        if(in_array($changeType, $growth)) {
            return self::GROWTH;
        }

        // 积分
        $integral = self::getIntegralChangeType();
        if(in_array($changeType, $integral)) {
            return self::INTEGRAL;
        }

        return false;
    }
}