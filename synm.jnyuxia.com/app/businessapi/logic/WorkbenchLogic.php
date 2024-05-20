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

use app\common\enum\AfterSaleEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\model\AfterSale;
use app\common\model\Goods;
use app\common\model\GoodsComment;
use app\common\model\IndexVisit;
use app\common\model\Order;
use app\common\model\User;
use app\common\service\ConfigService;

/**
 *
 * Class WorkbenchLogic
 * @package app\businessapi\logic
 */
class WorkbenchLogic
{

    public function index()
    {

        // 今日数据
        $today = self::today();
        // 待办事项
        $pending = self::pending();
        // 近5日营业额
        $business5 = self::business7();
        // 近5日访客数
        $visitor5 = self::visitor7();

        return [
            'shop_name'     => ConfigService::get('shop', 'name'),
            'today'         => $today,
            'pending'       => $pending,
            'business5'    => $business5,
            'visitor5'     => $visitor5,
        ];

    }


    /**
     * @notes 待办事项
     * @return array
     * @author Tab
     * @date 2021/9/10 17:57
     */
    public static function pending()
    {
        // 待发货订单数
        $waitShipped = Order::where('order_status', OrderEnum::STATUS_WAIT_DELIVERY)->count();
        // 待审核售后申请
        $waitAudit = AfterSale::where('sub_status', AfterSaleEnum::SUB_STATUS_WAIT_SELLER_AGREE)->count();
        // 待审核评价
        $waitProcess = GoodsComment::where(['status'=>YesNoEnum::NO])->count();
        // 售罄商品
        $noStockGoods = Goods::where('total_stock', 0)->count();

        return [
            'wait_shipped' => $waitShipped,
            'wait_audit' => $waitAudit,
            'wait_process' => $waitProcess,
            'no_stock_goods' => $noStockGoods
        ];
    }

    /**
     * @notes 今日数据
     * @return array
     * @author Tab
     * @date 2021/9/10 17:41
     */
    public static function today()
    {
        // 营业额
        $todayOrderAmount = Order::where('pay_status', YesNoEnum::YES)
            ->whereDay('create_time')
            ->sum('order_amount');
        // 成交订单数
        $todayOrderNum = Order::where('pay_status', YesNoEnum::YES)
            ->whereDay('create_time')
            ->count();
        // 访客数
        $visitor = IndexVisit::whereDay('create_time')->column('ip');
        $todayVisitor = count(array_unique($visitor));
        // 新增用户
        $todayNewUser = User::whereDay('create_time')->count();

        return [
            'today_order_amount' => $todayOrderAmount,
            'today_order_num' => $todayOrderNum,
            'today_visitor' => $todayVisitor,
            'today_new_user' => $todayNewUser,
        ];
    }


    /**
     * @notes 近15天营业额
     * @return array
     * @author Tab
     * @date 2021/9/10 18:06
     */
    public static function business7()
    {
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d') . ' 23:59:59';
        $todayDec15 = $today->add(\DateInterval::createFromDateString('-4day'));
        $todayDec15Str = $todayDec15->format('Y-m-d');

        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "sum(order_amount) as today_amount"
        ];
        $lists = Order::field($field)
            ->whereTime('create_time', 'between', [$todayDec15Str,$todayStr])
            ->where('pay_status', YesNoEnum::YES)
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_amount', 'date');
        $amountData = [];
        $date = [];
        for($i = 6; $i >= 0; $i --) {

            $today = new \DateTime();
            $targetDay = $today->add(\DateInterval::createFromDateString('-'. $i . 'day'));
            $targetDayTime = $targetDay->format('Ymd');
            $targetDay = $targetDay->format('d');
            $date[] = $targetDay;
            $amountData[] = $lists[$targetDayTime] ?? 0;
        }
        return [
            'date' => $date,
            'list' => [
                ['name' => '营业额', 'data' => $amountData]
            ]
        ];
    }

    /**
     * @notes 近15天访客数
     * @return mixed
     * @author Tab
     * @date 2021/9/10 18:51
     */
    public static function visitor7()
    {
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d') . ' 23:59:59';
        $todayDec15 = $today->add(\DateInterval::createFromDateString('-5day'));
        $todayDec15Str = $todayDec15->format('Y-m-d');

        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "ip"
        ];
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
            'date' => $date,
            'list' => [
                ['name' => '访客数', 'data' => $userData]
            ]
        ];
    }

}