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

namespace app\shopapi\logic;

use app\common\enum\DistributionConfigEnum;
use app\common\enum\DistributionEnum;
use app\common\enum\DistributionOrderGoodsEnum;
use app\common\enum\TerminalEnum;
use app\common\enum\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\logic\PosterLogic;
use app\common\model\DistributionConfig;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\model\User;
use app\common\model\Distribution;
use app\common\model\DistributionApply;
use app\common\service\FileService;
use app\common\service\RegionService;
use think\facade\Db;

/**
 * 分销逻辑层
 * Class DistributionLogic
 * @package app\shopapi\logic
 */
class DistributionLogic extends BaseLogic
{
    /**
     * @notes 判断用户是否为分销会员
     * @param $userId
     * @return array
     * @author Tab
     * @date 2021/8/4 11:15
     */
    public static function isDistributionMember($userId)
    {
        // 是否为分销会员
        $isDistribution = Distribution::where('user_id', $userId)->value('is_distribution');
        // 分销会员开通方式
        $open = DistributionConfig::where('key', 'open')->value('value');
        $open = $open ?: DistributionConfigEnum::DEFAULT_OPEN;
        $show_apply_page = $open == DistributionConfigEnum::OPEN_APPLY ? YesNoEnum::YES : YesNoEnum::NO;

        return [
            'is_distributon' => $isDistribution,
            'show_apply_page' => $show_apply_page
        ];
    }

    /**
     * @notes 填写邀请码
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/7/16 18:44
     */
    public static function code($params)
    {
        Db::startTrans();
        try{
            $firstLeader = User::field(['id', 'first_leader', 'second_leader', 'third_leader', 'ancestor_relation'])
                ->where(['code' => $params['code']])
                ->findOrEmpty()->toArray();

            // 上级
            $firstLeaderId = $firstLeader['id'];
            // 上上级
            $secondLeaderId = $firstLeader['first_leader'];
            // 上上上级
            $thirdLeaderId = $firstLeader['second_leader'];
            // 拼接关系链
            $firstLeader['ancestor_relation'] = boolval($firstLeader['ancestor_relation']) ? $firstLeader['ancestor_relation'] : ''; // 清空null值及0
            $myAncestorRelation = $firstLeaderId. ',' . $firstLeader['ancestor_relation'];
            // 去除两端逗号
            $myAncestorRelation = trim($myAncestorRelation, ',');

            $user = User::findOrEmpty($params['user_id']);
            // 旧关系链
            if (!empty($user->ancestor_relation)) {
                $old_ancestor_relation = $user->id . ',' .$user->ancestor_relation;
            } else {
                $old_ancestor_relation = $user->id;
            }

            $data = [
                'id'           => $params['user_id'],
                'first_leader'      => $firstLeaderId,
                'second_leader'     => $secondLeaderId,
                'third_leader'      => $thirdLeaderId,
                'ancestor_relation' => $myAncestorRelation,
            ];

            // 更新当前用户的分销关系
            User::duokaiUpdate($data, [], [], '', false);

            //更新当前用户下级的分销关系
            $data = [
                'second_leader' => $firstLeaderId,
                'third_leader'  => $secondLeaderId,
            ];
            User::where('first_leader', $params['user_id'])->update($data);

            //更新当前用户下下级的分销关系
            $data = [
                'third_leader' => $firstLeaderId,
            ];
            User::where('second_leader', $params['user_id'])->update($data);

            //更新当前用户所有后代的关系链
            $posterityArr = User::field('id,ancestor_relation')
                ->whereFindInSet('ancestor_relation', $params['user_id'])
                ->select()
                ->toArray();
            $updateData = [];
            $replaceAncestorRelation = $params['user_id'] . ','. $myAncestorRelation;
            foreach($posterityArr as $item) {
                $updateData[] = [
                    'id' => $item['id'], // 主键
                    'ancestor_relation' => trim(str_replace($old_ancestor_relation, $replaceAncestorRelation, $item['ancestor_relation']), ',')
                ];
            }
            // 批量更新
            (new User())->duokaiSaveAll($updateData, true, false, false);

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 申请分销
     * @param $params
     * @author Tab
     * @date 2021/7/17 10:37
     */
    public static function apply($params)
    {
        DistributionApply::duokaiCreate($params);
    }

    /**
     * @notes 查看申请详情
     * @param $userId
     * @return array
     * @author Tab
     * @date 2021/7/17 11:38
     */
    public static function applyDetail($userId)
    {
        $applyDetail = DistributionApply::where('user_id', $userId)->order('id', 'desc')->findOrEmpty();
        if($applyDetail->isEmpty()) {
            return [];
        }

        $applyDetail = $applyDetail->toArray();
        $applyDetail['province'] = RegionService::getAddress($applyDetail['province']);
        $applyDetail['city'] = RegionService::getAddress($applyDetail['city']);
        $applyDetail['district'] = RegionService::getAddress($applyDetail['district']);
        $applyDetail['status_tips'] = DistributionApply::getStatusTips($applyDetail['status']);

        return $applyDetail;
    }

    /**
     * @notes 查看当前用户及上级信息
     * @param $userId
     * @return array
     * @author Tab
     * @date 2021/7/17 14:57
     */
    public static function myLeader($userId)
    {
        $my = User::field('id,nickname,avatar,first_leader,user_earnings,code')->findOrEmpty($userId)->toArray();

        $leader = User::field('nickname')->findOrEmpty($my['first_leader'])->toArray();
        return [
            'user' => $my,
            'leader' => $leader,
        ];
    }

    /**
     * @notes 查看分销推广主页
     * @param $userId
     * @return array
     * @author Tab
     * @date 2021/7/17 16:27
     */
    public static function index($userId)
    {
        // 是否为分销会员
        $distribution = Distribution::where('user_id', $userId)->field('is_distribution,level_id')->find();
        if (empty($distribution['is_distribution'])) {
            $distribution['is_distribution'] = 0;
        }
        // 分销会员开通方式
        $open = DistributionConfig::where('key', 'open')->value('value');
        $open = $open ?: DistributionConfigEnum::DEFAULT_OPEN;
        $show_apply_page = $open == DistributionConfigEnum::OPEN_APPLY ? YesNoEnum::YES : YesNoEnum::NO;

        // 当前用户及上级信息
        $myLeader = self::myLeader($userId);

        // 粉丝数(一级/二级)
        $fans = User::getFans($userId);

        // 今天预估收益(未返佣金)
        $todayEarnings = DistributionOrderGoods::whereDay('create_time')
            ->where(['user_id' => $userId, 'status' => DistributionOrderGoodsEnum::UN_RETURNED])
            ->sum('earnings');

        // 本月预估收益(未返佣金)
        $monthEarnings = DistributionOrderGoods::whereMonth('create_time')
            ->where(['user_id' => $userId, 'status' => DistributionOrderGoodsEnum::UN_RETURNED])
            ->sum('earnings');

        // 累计收益(已返佣金)
        $historyEarnings = DistributionOrderGoods::where(['user_id' => $userId, 'status' => DistributionOrderGoodsEnum::RETURNED])
            ->sum('earnings');

        //申请页顶部宣传图
        $dbConfig = DistributionConfig::column('value', 'key');
        $top_apply_image = $dbConfig['apply_image'] ?? DistributionConfigEnum::DEFAULT_APPLY_IMAGE;
        $protocol_show = $dbConfig['protocol_show'] ?? DistributionConfigEnum::DEFAULT_PROTOCOL_SHOW;

        $data = [
            'is_distributon' => $distribution['is_distribution'],
            'show_apply_page' => $show_apply_page,
            'user' => $myLeader['user'],
            'leader' => $myLeader['leader'],
            'level_name'  => DistributionLevel::where(['id'=>$distribution['level_id']])->value('name') ?? '',
            'fans' => $fans,
            'able_withdrawal' => $myLeader['user']['user_earnings'],
            'code' => $myLeader['user']['code'],
            'today_earnings' => round($todayEarnings, 2),
            'month_earnings' => round($monthEarnings, 2),
            'history_earnings' => round($historyEarnings, 2),
            'top_apply_image' => $top_apply_image ? FileService::getFileUrl($top_apply_image) : '',
            'protocol_show' => $protocol_show,
        ];

        return $data;
    }

    /**
     * @notes 查看分销订单列表
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/7/17 18:22
     */
    public static function order($params)
    {
        $where[] = ['dog.user_id', '=', $params['user_id']];
        // 佣金状态
        if(isset($params['status']) && $params['status']) {
            $where[] = ['dog.status', '=', $params['status']];
        }

        if(isset($params['start_time']) && !empty($params['start_time'])) {
            $where[] = ['dog.create_time', '>=', $params['start_time']];
        }

        if(isset($params['end_time']) && !empty($params['end_time'])) {
            $where[] = ['dog.create_time', '<=', $params['end_time']];
        }

        $field = 'o.sn,o.create_time';
        $field .= ',og.goods_num,og.total_pay_price';
        $field .= ',dog.status,dog.status as status_desc,dog.earnings,dog.id,dog.order_goods_id';
        $field .= ',g.image as goods_image,g.name as goods_name';
        $field .= ',gi.image as item_image,gi.spec_value_str';

        $lists = DistributionOrderGoods::withoutGlobalScope()
            ->aliasSid('dog')
            ->field($field)
            ->alias('dog')
            ->leftJoin('order_goods og', 'dog.order_goods_id=og.id')
            ->leftJoin('order o', 'o.id=og.order_id')
            ->leftJoin('goods g', 'g.id=og.goods_id')
            ->leftJoin('goods_item gi', 'gi.id=og.item_id')
            ->where($where)
            ->order('o.id', 'desc')
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['image'] = $item['item_image'] ? FileService::getFileUrl($item['item_image']) : FileService::getFileUrl($item['goods_image']);
        }

        $count = DistributionOrderGoods::withoutGlobalScope()
            ->aliasSid('dog')
            ->alias('dog')
            ->leftJoin('order_goods og', 'dog.order_goods_id=og.id')
            ->leftJoin('order o', 'o.id=og.order_id')
            ->leftJoin('goods g', 'g.id=og.goods_id')
            ->leftJoin('goods_item gi', 'gi.id=og.item_id')
            ->where($where)
            ->count();

        $data = [
            'lists' => $lists,
            'count' => $count,
            'page_no' => $params['page_no'],
            'page_size' => $params['page_size'],
            'more' => is_more($count, $params['page_no'], $params['page_size'])
        ];

        return $data;
    }

    /**
     * @notes 查看月度账单
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/7/17 19:07
     */
    public static function monthBill($params)
    {
        $field = [
            "any_value(FROM_UNIXTIME(create_time,'%Y年%m月')) as date",
            "any_value(FROM_UNIXTIME(create_time,'%Y')) as year",
            "any_value(FROM_UNIXTIME(create_time,'%m')) as month",
            'sum(earnings) as total_money',
            'count(order_goods_id) as order_goods_num'
        ];

        $lists = DistributionOrderGoods::field($field)
            ->where('user_id', $params['user_id'])
            ->where('status', 'in', [DistributionOrderGoodsEnum::UN_RETURNED, DistributionOrderGoodsEnum::RETURNED])
            ->page($params['page_no'], $params['page_size'])
            ->group('date')
            ->select()
            ->toArray();

        $count = DistributionOrderGoods::field($field)
            ->where('user_id', $params['user_id'])
            ->where('status', 'in', [DistributionOrderGoodsEnum::UN_RETURNED, DistributionOrderGoodsEnum::RETURNED])
            ->group('date')
            ->count();

        $data = [
            'lists' => $lists,
            'count' => $count,
            'page_no' => $params['page_no'],
            'page_size' => $params['page_size'],
            'more' => is_more($count, $params['page_no'], $params['page_size'])
        ];

        return $data;
    }

    /**
     * @notes 查看月度账单明细
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/7/19 10:26
     */
    public static function monthDetail($params)
    {
        // -t 获取指定月份的天数
        list($year, $month, $days) = explode('-', date("{$params['year']}-{$params['month']}-t"));
        // 月初时间
        $params['start_time'] = strtotime("{$year}-{$month}-01 00:00:00");
        // 月末时间
        $params['end_time'] = strtotime("{$year}-{$month}-{$days} 23:59:59");

        return self::order($params);
    }

    /**
     * @notes 分销海报
     * @param $params
     * @return array|false
     * @author Tab
     * @date 2021/8/6 15:07
     */
    public static function poster($params)
    {
        try {
            // 小程序url必填
            if($params['terminal'] == UserTerminalEnum::WECHAT_MMP && (!isset($params['url']) || empty($params['url']))) {
                throw new \think\Exception('请提供url');
            }
            // 公众号url必填
            if($params['terminal'] == UserTerminalEnum::WECHAT_OA && (!isset($params['url']) || empty($params['url']))) {
                throw new \think\Exception('请提供url');
            }
            // 用户信息
            $user = User::field('id,avatar,nickname,code')->findOrEmpty($params['user_id'])->toArray();

            switch($params['terminal']) {
                case UserTerminalEnum::WECHAT_MMP:
                    $urlType = 'path';
                    $qrCodeField = 'mnp_qr_code';
                    $content = $params['url'];
                    break;
                case UserTerminalEnum::WECHAT_OA:
                case UserTerminalEnum::H5:
                    $urlType = 'url';
                    $qrCodeField = 'h5_qr_code';
                    $url = FileService::getFileUrl($params['url']);
                    $content = $url . '?invite_code=' . $user['code'];
                    break;
                case UserTerminalEnum::IOS:
                case UserTerminalEnum::ANDROID:
                    $urlType = 'url';
                    $qrCodeField = 'app_qr_code';
                    $content = url('index/index/app', [], '', true);
                    break;
                default:
                    throw new \think\Exception('终端类型错误');
            }

            // 生成分销海报
            $poster = PosterLogic::generate($user, $content, $urlType, $params['terminal']);

            // 更新分销基础信息表
            Distribution::where('user_id', $params['user_id'])->update([
                $qrCodeField => $poster['url']
            ]);

            return ['url' => FileService::getFileUrl($poster['url'])];
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * 修复旧的关系链
     * @return bool
     */
    public static function fixAncestorRelation()
    {
        Db::startTrans();
        try {
            $userList = User::select()->toArray();
            if (empty($userList)) {
                throw new \Exception('没有用户，无需修复');
            }

            $updateEmptyData = [];
            $updateData = [];
            foreach($userList as $user) {
                $my_ancestor_relation = self::myAncestorRelation($user);
                $updateEmptyData[] = ['id' => $user['id'], 'ancestor_relation' => ''];
                $updateData[] = ['id' => $user['id'], 'ancestor_relation' => $my_ancestor_relation];
            }
            // 先清除所有关系链
            (new User())->duokaiSaveAll($updateEmptyData, true, false, false);
            // 重新设置关系链
            (new User())->duokaiSaveAll($updateData, true, false, false);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function myAncestorRelation($user)
    {
        if (empty($user['first_leader'])) {
            return '';
        }

        return trim(self::findAncestorRelation($user['first_leader']), ',');
    }

    public static function findAncestorRelation($id, $flag = true)
    {
        static $ancestor_relation = '';
        if ($flag) {
            $ancestor_relation = '';
        }
        $ancestor_relation .= $id . ',';
        $user = User::findOrEmpty($id);
        if (empty($user['first_leader'])) {
            return $ancestor_relation;
        }
        return self::findAncestorRelation($user['first_leader'], false);
    }

    /**
     * @notes 获取海报
     * @author cjhao
     * @date 2021/11/17 18:35
     */
    public static function getPoster(){
        $poster = DistributionConfig::where(['key'=>'poster'])->value('value');
        if(empty($poster)){
            $poster = DistributionConfigEnum::DEFAULT_POSTER;
        }
        return ['poster' => FileService::getFileUrl($poster)];
    }
}