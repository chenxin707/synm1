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
namespace app\adminapi\logic\data;

use app\common\enum\AfterSaleEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\Goods;
use app\common\model\GoodsVisit;
use app\common\model\IndexVisit;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\User;

/**
 * 数据中心
 * Class CenterLogic
 * @package app\adminapi\logic
 */
class CenterLogic extends BaseLogic
{
    /**
     * @notes 流量分析
     * @return array
     * @author Tab
     * @date 2021/9/27 10:31
     */
    public static function trafficAnalysis($params)
    {
        if (!isset($params['month'])) {
            // 未传月份，默认获取当前月份数据
            $params['month'] = date('Y-m');
        }
        return [
            // 首页数据汇总
            'summary' => self::taSummary($params),
            // 首页访问量
            'visit' => self::taVisit($params),
            // 首页访客数
            'user' => self::taUser($params),
        ];
    }

    /**
     * @notes 流量分板 - 首页数据汇总
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 10:47
     */
    public static function taSummary($params)
    {
        // 访问量
        $visit = IndexVisit::whereMonth('create_time', $params['month'])->sum('visit');
        // 访客数(去重)
        $ips = IndexVisit::distinct(true)->whereMonth('create_time', $params['month'])->column('ip');

        return [
            'visit' => intval($visit),
            'visitor' => count($ips)
        ];
    }

    /**
     * @notes 流量分析 - 首页访问量图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 11:04
     */
    public static function taVisit($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "sum(visit) as today_data"
        ];
        $lists = IndexVisit::field($field)
            ->whereMonth('create_time', $params['month'])
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_data', 'date');

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $lists[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '浏览量', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 流量分析 - 首页访客数图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 11:09
     */
    public static function taUser($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "ip"
        ];
        // 去重
        $lists = IndexVisit::distinct(true)
            ->field($field)
            ->whereMonth('create_time', $params['month'])
            ->select()
            ->toArray();

        // 集合一天的IP
        $temp1 =  [];
        foreach ($lists as $item) {
            $temp1[$item['date']][] = $item['ip'];
        }
        // 统计ip数量
        $temp2 = [];
        foreach ($temp1 as $k => $v) {
            $temp2[$k] = count($v);
        }

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $temp2[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '访客数', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 用户分析
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 11:21
     */
    public static function userAnalysis($params)
    {
        if (!isset($params['month'])) {
            // 未传月份，默认获取当前月份数据
            $params['month'] = date('Y-m');
        }
        return [
            // 数据汇总
            'summary' => self::uaSummary($params),
            // 用户总人数
            'user' => self::uaUser($params),
            // 新增用户数
            'new_user' => self::uaNewUser($params),
        ];
    }

    /**
     * @notes 用户分析 - 数据汇总
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 11:27
     */
    public static function uaSummary($params)
    {
        // 用户总人数
        $user = User::count();
        // 新增用户数
        $newUser = User::whereMonth('create_time', $params['month'])->count();

        return [
            'user' => $user,
            'new_user' => $newUser
        ];
    }

    /**
     * @notes 用户分析 - 用户总数图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 14:52
     */
    public static function uaUser($params)
    {
        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];

        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $dayTimestamp = strtotime($day. ' 23:59:59');
            $date[] = $day;
            $data[] = User::where('create_time', '<=', $dayTimestamp)->count();
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '用户总数', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 用户分析 - 新增用户图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 14:55
     */
    public static function uaNewUser($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "count(id) as today_data"
        ];
        $lists = User::field($field)
            ->whereMonth('create_time', $params['month'])
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_data', 'date');

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $lists[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '新增用户', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 交易分析
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:01
     */
    public static function transactionAnalysis($params)
    {
        if (!isset($params['month'])) {
            // 未传月份，默认获取当前月份数据
            $params['month'] = date('Y-m');
        }
        return [
            // 数据汇总
            'summary' => self::tsaSummary($params),
            // 成交订单
            'order' => self::tsaOrder($params),
            // 订单金额
            'order_amount' => self::tsaOrderAmount($params),
        ];
    }

    /**
     * @notes 交易分析 - 数据汇总
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:25
     */
    public static function tsaSummary($params)
    {
        // 成交订单
        $order = Order::whereMonth('create_time', $params['month'])->where('pay_status', YesNoEnum::YES)->count();
        // 订单金额
        $orderAmount = Order::whereMonth('create_time', $params['month'])->where('pay_status', YesNoEnum::YES)->sum('order_amount');

        return [
            'order' => $order,
            'order_amount' => $orderAmount
        ];
    }

    /**
     * @notes 交易分析 - 成交订单图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:26
     */
    public static function tsaOrder($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "count(id) as today_data"
        ];
        $lists = Order::field($field)
            ->whereMonth('create_time', $params['month'])
            ->where('pay_status', YesNoEnum::YES)
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_data', 'date');

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $lists[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '成交订单', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 交易分析 - 订单金额图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:29
     */
    public static function tsaOrderAmount($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "sum(order_amount) as today_data"
        ];
        $lists = Order::field($field)
            ->whereMonth('create_time', $params['month'])
            ->where('pay_status', YesNoEnum::YES)
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_data', 'date');

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $lists[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '订单金额', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 商品分析
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:34
     */
    public static function goodsAnalysis($params)
    {
        if (!isset($params['month'])) {
            // 未传月份，默认获取当前月份数据
            $params['month'] = date('Y-m');
        }
        return [
            // 数据汇总
            'summary' => self::gaSummary($params),
            // 浏览量
            'visit' => self::gaVisit($params),
            // 销售量
            'num' => self::gaNum($params),
        ];
    }

    /**
     * @notes 商品分析 - 数据汇总
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:36
     */
    public static function gaSummary($params)
    {
        // 浏览量
        $visit = GoodsVisit::whereMonth('create_time', $params['month'])->sum('visit');
        // 销售量
        $num = Order::whereMonth('create_time', $params['month'])->where('pay_status', YesNoEnum::YES)->sum('total_num');

        return [
            'visit' => $visit,
            'num' => $num
        ];
    }

    /**
     * @notes 商品分析 - 浏览量图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:41
     */
    public static function gaVisit($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "sum(visit) as today_data"
        ];
        $lists = GoodsVisit::field($field)
            ->whereMonth('create_time', $params['month'])
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_data', 'date');

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $lists[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '浏览量', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 商品分析 - 销量图表数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/27 15:43
     */
    public static function gaNum($params)
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "sum(total_num) as today_data"
        ];
        $lists = Order::field($field)
            ->whereMonth('create_time', $params['month'])
            ->where('pay_status', YesNoEnum::YES)
            ->group('date')
            ->select()
            ->toArray();

        $lists = array_column($lists, 'today_data', 'date');

        // 转时间戳
        $timestamp = strtotime($params['month']);
        // 转格式
        $month = date('Ym', $timestamp);
        // 获取指定月份共有几天
        $days = date('t', $timestamp);
        // 数据存储器
        $data = [];
        // 日期存储器
        $date = [];
        for($i = $days; $i >= 1; $i--) {
            $day  = $month . day_format($i);
            $date[] = $day;
            $data[] = $lists[$day] ?? 0;
        }

        return [
            'date' => $date,
            'list' => [
                ['name' => '销量', 'data' => $data]
            ]
        ];
    }

    /**
     * @notes 商品排行榜
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/27 16:37
     */
    public static function goodsTop($params)
    {
        // 排序字段
        if (!isset($params['sort_field']) || empty($params['sort_field'])) {
            $params['sort_field'] = 'visit';
        }

        $lists = Goods::field('id,image,name')
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['visit'] = self::goodsVisit($item);
            $item['num'] = self::goodsNum($item);
            $item['amount'] = self::goodsAmount($item);
        }

        $idSort = array_column($lists, 'id');
        $visitSort = array_column($lists, 'visit');
        $numSort = array_column($lists, 'num');
        $amountSort = array_column($lists, 'amount');

        // 排序
        switch ($params['sort_field'])
        {
            // 浏览量排序
            case 'visit':
                array_multisort($visitSort,  SORT_DESC, SORT_NUMERIC, $lists);
                break;
            // 销量排序
            case 'num':
                array_multisort($numSort,  SORT_DESC, SORT_NUMERIC, $lists);
                break;
            // 销售额排序
            case 'amount':
                array_multisort($amountSort,  SORT_DESC, SORT_NUMERIC, $lists);
                break;
            // id排序
            default:
                array_multisort($idSort,  SORT_DESC, SORT_NUMERIC, $lists);
                break;
        }

        return $lists;
    }

    /**
     * @notes 商品浏览量
     * @param $item
     * @return float
     * @author Tab
     * @date 2021/9/27 16:14
     */
    public static function goodsVisit($item)
    {
        $visit =  GoodsVisit::where('goods_id', $item['id'])->sum('visit');
        return intval($visit);
    }

    /**
     * @notes 商品销量
     * @param $item
     * @return mixed
     * @author Tab
     * @date 2021/9/27 16:18
     */
    public static function goodsNum($item)
    {
        $num = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->where([
                ['og.goods_id', '=', $item['id']],
                ['o.pay_status', '=', YesNoEnum::YES],
            ])
            ->sum('og.goods_num');

        return intval($num);
    }

    /**
     * @notes 商品销量额
     * @param $item
     * @return mixed
     * @author Tab
     * @date 2021/9/27 16:19
     */
    public static function goodsAmount($item)
    {
        $amount = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->where([
                ['og.goods_id', '=', $item['id']],
                ['o.pay_status', '=', YesNoEnum::YES],
            ])
            ->sum('og.total_pay_price');

        return $amount;
    }
}