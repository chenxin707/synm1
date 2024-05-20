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
namespace app\businessapi\logic;
use app\adminapi\logic\distribution\DistributionDataLogic;
use app\common\enum\AfterSaleEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\model\AfterSale;
use app\common\model\Goods;
use app\common\model\IndexVisit;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\User;

/**
 * 财务控制器类
 * Class FinanceController
 * @package app\businessapi\controller
 */
class FinanceLogic
{

    /**
     * @notes 财务概况
     * @return array
     * @author cjhao
     * @date 2023/2/16 14:35
     */
    public function dataCenter()
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


    public function dealCenter()
    {
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d') . ' 23:59:59';
        $todayDec15 = $today->add(\DateInterval::createFromDateString('-4day'));
        $todayDec15Str = $todayDec15->format('Y-m-d');
        // 订单总支付金额
        $orderSum = Order::where('pay_status', PayEnum::ISPAID)
            ->whereTime('create_time', 'between', [$todayDec15Str,$todayStr])
            ->sum('order_amount');
        // 订单总支付笔数
        $orderNum = Order::where('pay_status', PayEnum::ISPAID)
            ->whereTime('create_time', 'between', [$todayDec15Str,$todayStr])
            ->count('id');


        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "sum(order_amount) as today_amount,count(id) as today_num"
        ];
        $lists = Order::field($field)
            ->whereTime('create_time', 'between', [$todayDec15Str,$todayStr])
            ->where('pay_status', YesNoEnum::YES)
            ->group('date')
            ->select()
            ->toArray();
        $lists = array_column($lists, null, 'date');

        $amountData = [];
        $date = [];
        for($i = 6; $i >= 0; $i --) {
            $today = new \DateTime();
            $targetDay = $today->add(\DateInterval::createFromDateString('-'. $i . 'day'));
            $targetDayTime = $targetDay->format('Ymd');
            $targetDay = $targetDay->format('d');
            $date[] = $targetDay;

            $amountData[] = $lists[$targetDayTime]['today_amount'] ?? 0;
            $numData[] = $lists[$targetDayTime]['today_num'] ?? 0;
        }

        return [
            'order_sum'     => $orderSum,
            'order_num'     => $orderNum,
            'date' => $date,
            'list' => [
                ['name' => '成交数量', 'data' => $numData],
                ['name' => '营业额', 'data' => $amountData],
            ]
        ];
    }


    /**
     * @notes  商品中心
     * @return Goods[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2023/2/17 16:58
     */
    public function goodsCenter()
    {
        $field = [
            'g.id',
            'g.name',
            'g.image',
            'g.min_price',
            'g.max_price',
            'sum(og.goods_num)' => 'total_num',
            'sum(og.total_pay_price)' => 'total_pay_price',
        ];
        $lists = (new OrderGoods)->duokaiWithoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'g.id = og.goods_id')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('g.id,g.name,g.image')
            ->order('total_pay_price', 'desc')
            ->limit(20)
            ->select()
            ->toArray();
        foreach ($lists as $key => $goods){
            //商品价格
            $list[$key]['price'] = '¥' . $goods['min_price'];
            if ($goods['min_price'] != $goods['max_price']) {
                $lists[$key]['price'] = '¥' . $goods['min_price'] . '~' . '¥' . $goods['max_price'];
            }
        }
        return $lists;
    }

    /**
     * @notes 访客统计
     * @return array
     * @throws \Exception
     * @author cjhao
     * @date 2023/2/17 17:01
     */
    public function visitCenter()
    {
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d') . ' 23:59:59';
        $todayDec15 = $today->add(\DateInterval::createFromDateString('-5day'));
        $todayDec15Str = $todayDec15->format('Y-m-d');

        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "ip"
        ];
        $count = IndexVisit::distinct(true)->count();

        $lists = IndexVisit::field($field)
            ->distinct(true)
            ->whereTime('create_time', 'between', [$todayDec15Str,$todayStr])
            ->select()
            ->toArray();

        // 集合一天的IP
        $temp1 =  [];
        foreach ($lists as $item) {
            $temp1[$item['date']][] = $item['ip'];
        }
        // 统计数量
        $temp2 = [];
        foreach ($temp1 as $k => $v) {
            $temp2[$k] = count($v);
        }

        $userData = [];
        $date = [];
        for($i = 6; $i >= 0; $i --) {
            $today = new \DateTime();
            $targetDay = $today->add(\DateInterval::createFromDateString('-'. $i . 'day'));
            $targetDayTime = $targetDay->format('Ymd');
            $targetDay = $targetDay->format('d');
            $date[] = $targetDay;
            $userData[] = $temp2[$targetDayTime] ?? 0;
        }
        return [
            'count' => $count,
            'date'  => $date,
            'list'  => [
                ['name' => '访客数', 'data' => $userData]
            ]
        ];

    }
}