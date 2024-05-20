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

namespace app\adminapi\logic;

use app\common\enum\AfterSaleEnum;
use app\common\enum\OrderEnum;
use app\common\enum\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\Config;
use app\common\model\Goods;
use app\common\model\GoodsComment;
use app\common\model\IndexVisit;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\PlatformShop;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\service\FileService;

/**
 * 工作台
 * Class WorkbenchLogic
 * @package app\adminapi\logic
 */
class WorkbenchLogic extends BaseLogic
{
    /**
     * @notes 工作台
     * @return int[]
     * @author Tab
     * @date 2021/9/10 14:12
     */
    public static function index($adminInfo)
    {
        // 基本信息
        $shopInfo = self::shopInfo($adminInfo);
        // 今日数据
        $today = self::today();
        // 待办事项
        $pending = self::pending();
        // 近15日营业额
        $business15 = self::business15();
        // 近15日访客数
        $visitor15 = self::visitor15();

        return [
            'shop_info' => $shopInfo,
            'today' => $today,
            'pending' => $pending,
            'business15' => $business15,
            'visitor15' => $visitor15,
        ];
    }

    /**
     * @notes 商城信息
     * @param $adminInfo
     * @return array
     * @author Tab
     * @date 2021/9/10 15:02
     */
    public static function shopInfo($adminInfo)
    {
        // 版本号
        $version = config('project.version');
        // 商城名称
        $name = ConfigService::get('shop', 'name');
        // 商城logo
        $logo = ConfigService::get('shop', 'logo');
        $logo = FileService::getFileUrl($logo);
        // 管理员名称
        $adminName = $adminInfo['name'];
        //商城套餐
        $platShop = PlatformShop::alias('PS')
            ->join('set_meal SM','PS.set_meal_id = SM.id')
            ->where(['PS.id'=>$adminInfo['sid']])
            ->field('PS.expires_time,SM.name')
            ->findOrEmpty()->toArray();
        $expiresTime = $platShop['expires_time'] ?? '';
        $expiresStatus = -1;

        if($expiresTime){
            //即将到期
            $time = strtotime($expiresTime) - time();
            if($time > 0 && $time < 7*24*60*60){
                $expiresStatus = 0;
            }
            //正常状态
            if($time > 7*24*60*60){
                $expiresStatus = 1;
            }
        }

        return [
            'version'          => $version,
            'name'          => $name,
            'logo'          => $logo,
            'admin_name'    => $adminName,
            'meal_name'     => $platShop['name'] ?? '',
            'expires_time'  => $expiresTime,
            'expires_status'=> $expiresStatus,
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
        // 待回复商品评价
        $waitReply = GoodsComment::whereNull('reply')->count();
        // 待审核评价
        $waitProcess = GoodsComment::where(['status'=>YesNoEnum::NO])->count();
        // 售罄商品
        $noStockGoods = Goods::where('total_stock', 0)->count();

        return [
            'wait_shipped' => $waitShipped,
            'wait_audit' => $waitAudit,
            'wait_reply' => $waitReply,
            'wait_process' => $waitProcess,
            'no_stock_goods' => $noStockGoods
        ];
    }

    /**
     * @notes 近15天营业额
     * @return array
     * @author Tab
     * @date 2021/9/10 18:06
     */
    public static function business15()
    {
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d') . ' 23:59:59';
        $todayDec15 = $today->add(\DateInterval::createFromDateString('-14day'));
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
        for($i = 0; $i < 15; $i ++) {
            $today = new \DateTime();
            $targetDay = $today->add(\DateInterval::createFromDateString('-'. $i . 'day'));
            $targetDay = $targetDay->format('Ymd');
            $date[] = $targetDay;
            $amountData[] = $lists[$targetDay] ?? 0;
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
    public static function visitor15()
    {
        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d') . ' 23:59:59';
        $todayDec15 = $today->add(\DateInterval::createFromDateString('-14day'));
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
        for($i = 0; $i < 15; $i ++) {
            $today = new \DateTime();
            $targetDay = $today->add(\DateInterval::createFromDateString('-'. $i . 'day'));
            $targetDay = $targetDay->format('Ymd');
            $date[] = $targetDay;
            $userData[] = $temp2[$targetDay] ?? 0;
        }
        return [
            'date' => $date,
            'list' => [
                ['name' => '访客数', 'data' => $userData]
            ]
        ];
    }

    /**
     * @notes 商品排行榜 - 按销售额排序
     * @return mixed
     * @author Tab
     * @date 2021/9/10 19:40
     */
    public static function topGoods50($params)
    {
        $field = [
            'g.id',
            'g.name',
            'g.image',
            'sum(og.goods_num)' => 'total_num',
            'sum(og.total_pay_price)' => 'total_pay_price',
        ];
        $lists = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'g.id = og.goods_id')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('g.id,g.name,g.image')
            ->order('total_pay_price', 'desc')
            ->limit(50)
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'g.id = og.goods_id')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('g.id,g.name,g.image')
            ->order('total_pay_price', 'desc')
            ->limit(50)
            ->count();

        return [
            'lists' => $lists,
            'count' => $count,
            'page_no' => $params['page_no'],
            'page_size' => $params['page_size'],
        ];
    }

    /**
     * @notes 用户排行榜 - 按用户累计购买金额
     * @return mixed
     * @author Tab
     * @date 2021/9/10 19:50
     */
    public static function topUser50($params)
    {
        $field = [
            'u.id',
            'u.nickname',
            'u.avatar',
            'count(o.id)' => 'total_num',
            'sum(o.order_amount)' => 'total_order_amount',
        ];
        $lists = Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->leftJoin('user u', 'u.id = o.user_id')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('u.id,u.nickname,u.avatar')
            ->order('total_order_amount', 'desc')
            ->limit(50)
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count = Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->leftJoin('user u', 'u.id = o.user_id')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('u.id,u.nickname,u.avatar')
            ->order('total_order_amount', 'desc')
            ->limit(50)
            ->count();

        return [
            'lists' => $lists,
            'count' => $count,
            'page_no' => $params['page_no'],
            'page_size' => $params['page_size'],
        ];
    }

    /**
     * @notes 获取平台客服
     * @return array
     * @author cjhao
     * @date 2022/3/9 10:31
     */
    public static function getPlatformService(){

        $serviceQrcode = Config::withoutGlobalScope()->where(['type' => 'platformapi', 'name' => 'service_qrcode'])->value('value');
        if($serviceQrcode){
            $serviceQrcode = FileService::getFileUrl($serviceQrcode);
        }
        $config = [
            'service_name'          => Config::withoutGlobalScope()->where(['type' => 'platformapi', 'name' => 'service_name'])->value('value'),
            'service_mobile'        => Config::withoutGlobalScope()->where(['type' => 'platformapi', 'name' => 'service_mobile'])->value('value'),
            'service_qrcode'        => $serviceQrcode,
        ];
        return $config;
    }
}
