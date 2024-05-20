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

namespace app\platformapi\logic;

use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Order;
use app\common\model\PlatformShop;
use app\common\model\SetMealOrder;
use app\common\model\User;
use app\common\service\ConfigService;
use Requests;

/**
 * 平台工作台逻辑层
 */
class WorkbenchLogic extends BaseLogic
{
    /**
     * @notes 平台工作台
     * @param $platformAdminInfo
     * @return array
     * @author Tab
     * @date 2021/12/21 11:47
     */
    public static function index($platformAdminInfo)
    {
        // 平台信息
        $platformInfo = self::platformInfo($platformAdminInfo);
        // 累计数据
        $accumulatedData = self::accumulatedData();

        return [
            'platform_info' => $platformInfo,
            'accumulated_data' => $accumulatedData,
            'update_time' => date('Y-m-d H:i:s', time())
        ];
    }

    /**
     * @notes 平台信息
     * @param $platformAdminInfo
     * @return array
     * @author Tab
     * @date 2021/12/21 11:48
     */
    public static function platformInfo($platformAdminInfo)
    {
        return [
            // 平台名称
            'name' => ConfigService::get('platformapi', 'platform_name'),
            // 管理员名称
            'platform_admin_name' => $platformAdminInfo['name'],
            // 管理员头像
            'platform_admin_avatar' => $platformAdminInfo['avatar'],
            // 系统版本
            'system_version' => config('project.version')
        ];
    }

    /**
     * @notes 累计数据
     * @return array
     * @author Tab
     * @date 2021/12/21 11:58
     */
    public static function accumulatedData()
    {
        // 未删除的商城数量(含停止服务的商城)
        $shopCount =  PlatformShop::count();
        // 即将过期商城数量
        $sevenDay = strtotime('+7 days');
        $shopSoonCount = PlatformShop::where([
            ['expires_time', '>', time()],
            ['expires_time', '<=', $sevenDay],
        ])->count();
        // 已过期的商城数量
        $sevenDay = strtotime('+7 days');
        $shopOverdueCount = PlatformShop::where('expires_time', '<=', time())->count();
        // 剩余商城数量
        $shopOtherCount = $shopCount - $shopSoonCount - $shopOverdueCount;
        // 续费收入
        $renewAmount = 0;
        $setMealOrderList = SetMealOrder::withoutGlobalScope()->where('pay_status', PayEnum::ISPAID)->select()->toArray();
        foreach($setMealOrderList as $item) {
            $item['set_meal_price_snapshot'] = json_decode($item['set_meal_price_snapshot'], true);
            $renewAmount += $item['set_meal_price_snapshot']['price'];
        }
        // 所有商城已支付订单金额
        $allShopOrderAmount = Order::withoutGlobalScope()->where('pay_status', YesNoEnum::YES)->sum('order_amount');
        // 所有商城已支付订单数量
        $allShopOrderCount = Order::withoutGlobalScope()->where('pay_status', YesNoEnum::YES)->count();
        // 所有商城的用户数量
        $allShopUserCount = User::withoutGlobalScope()->count();
        return [
            'all_shop_order_amount' => $allShopOrderAmount,
            'shop_count' => $shopCount,
            'shop_soon_count' => $shopSoonCount,
            'shop_overdue_count' => $shopOverdueCount,
            'shop_other_count' => $shopOtherCount,
            'all_shop_order_count' => $allShopOrderCount,
            'all_shop_user_count' => $allShopUserCount,
            'renew_amount' => round($renewAmount, 2),
        ];
    }

    /**
     * @notes 商城用户数量排行榜
     * @return mixed
     * @author Tab
     * @date 2021/12/21 12:04
     */
    public static function topUser($params)
    {
        $field = [
            'ps.id',
            'ps.name',
            'count(u.id)' => 'user_count'
        ];
        $lists = User::withoutGlobalScope()
            ->alias('u')
            ->leftJoin('platform_shop ps', 'ps.id = u.sid')
            ->field($field)
            ->group('ps.id,ps.name')
            ->order([
                'user_count' => 'desc',
                'id' => 'asc'
            ])
            ->limit(50)
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count = User::withoutGlobalScope()
            ->alias('u')
            ->leftJoin('platform_shop ps', 'ps.id = u.sid')
            ->field($field)
            ->group('ps.id,ps.name')
            ->order([
                'user_count' => 'desc',
                'id' => 'asc'
            ])
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
     * @notes 商城营业额排行榜
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/12/21 14:10
     */
    public static function topAmount($params)
    {
        $field = [
            'ps.id',
            'ps.name',
            'sum(o.order_amount)' => 'sum_amount'
        ];
        $lists = Order::withoutGlobalScope()
            ->alias('o')
            ->leftJoin('platform_shop ps', 'ps.id = o.sid')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('ps.id,ps.name')
            ->order([
                'sum_amount' => 'desc',
                'id' => 'asc'
            ])
            ->limit(50)
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count = Order::withoutGlobalScope()
            ->alias('o')
            ->leftJoin('platform_shop ps', 'ps.id = o.sid')
            ->field($field)
            ->where('o.pay_status', YesNoEnum::YES)
            ->group('ps.id,ps.name')
            ->order([
                'sum_amount' => 'desc',
                'id' => 'asc'
            ])
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
     * @notes 检测新版本
     * @return mixed
     * @author ljj
     * @date 2023/5/25 7:02 下午
     */
    public static function checkVersion()
    {
        $version = config('project.version');
        $product_code = config('project.product_code');
        $check_domain = config('project.check_domain');
        $result = Requests::get($check_domain.'/api/version/hasNew?code='.$product_code.'&version='.$version);
        $result = json_decode($result->body,true);
        return $result['data'] ?? ['result'=>false,'version'=>''];
    }

    /**
     * @notes 正版检测
     * @return mixed
     * @author ljj
     * @date 2023/5/16 11:49 上午
     */
    public static function checkLegal()
    {
        $check_domain = config('project.check_domain');
        $product_code = config('project.product_code');
        $domain = $_SERVER['HTTP_HOST'];
        $result = \Requests::get($check_domain.'/api/version/productAuth?code='.$product_code.'&domain='.$domain);
        $result = json_decode($result->body,true);
        return $result['data'];
    }
}
