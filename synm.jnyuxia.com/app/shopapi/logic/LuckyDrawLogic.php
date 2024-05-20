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

use app\common\enum\AccountLogEnum;
use app\common\enum\LuckyDrawEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\BaseLogic;
use app\common\model\Coupon;
use app\common\model\LuckyDraw;
use app\common\model\LuckyDrawPrize;
use app\common\model\LuckyDrawRecord;
use app\common\model\User;
use think\facade\Db;

/**
 * 幸运抽奖
 */
class LuckyDrawLogic extends BaseLogic
{
    /**
     * @notes 查看活动信息
     * @author Tab
     * @date 2021/11/25 11:45
     */
    public static function activity($params)
    {
        $activity = LuckyDraw::findOrEmpty($params['id']);
        if ($activity->isEmpty()) {
            return [];
        }
        $prizes = LuckyDrawPrize::field('name,image, location')->where('activity_id', $activity->id)->select()->toArray();
        $user = User::findOrEmpty($params['user_id']);
        $btn = self::btnInfo($activity);

        // 处理返回格式
        $prizes = self::formatReturn($prizes, $btn);

        return [
            'user_integral' => $user->user_integral,
            'rule' => $activity->rule,
            'show_winning_list' => $activity->show_winning_list,
            'limit' => self::limitInfo($activity),
            'prizes' => $prizes,
        ];
    }

    /**
     * @notes 处理返回格式
     * @param $prizes
     * @param $btn
     * @author Tab
     * @date 2021/11/26 10:25
     */
    private static function formatReturn($prizes, $btn)
    {
        $prizes = array_column($prizes, null, 'location');
        // 前端需要的顺序 [1, 2, 3, 8, 抽奖按钮 ,4, 7, 6, 5];
        $sort = [1, 2, 3, 8, 4, 7, 6, 5];
        $data = [];
        foreach ($sort as $location) {
            $data[] = $prizes[$location];
        }
        // 插入抽奖按钮
        array_splice($data, 4, 0, [$btn]);
        return $data;
    }


    /**
     * @notes 抽奖按钮信息
     * @param $activity
     * @author Tab
     * @date 2021/11/25 11:57
     */
    private static function btnInfo($activity)
    {
        $btnInfo = [
            'flag' => true,
            'tips' => '开始抽奖'
        ];
        if ($activity->status == LuckyDrawEnum::END || $activity->end_time <= time()) {
            $btnInfo['flag'] = false;
            $btnInfo['tips'] = '活动已结束';

        }
        if ($activity->status == LuckyDrawEnum::WAIT || $activity->start_time > time()) {
            $btnInfo['flag'] = false;
            $btnInfo['tips'] = '活动未开始';
        }
        if ($activity->need_integral > 0) {
            $btnInfo['tips'] = '消耗' . $activity->need_integral . '积分';
        }
        return $btnInfo;
    }

    /**
     * @notes 抽奖次数限制
     * @param $activity
     * @author Tab
     * @date 2021/11/25 14:03
     */
    private static function limitInfo($activity)
    {
        $limitInfo = '不限制抽奖次数，具体请查看活动规则';
        if ($activity->frequency_type == 1) {
            $limitInfo = '每日可抽奖' . $activity->frequency .'次，具体请查看活动规则';
        }
        return $limitInfo;
    }

    /**
     * @notes 抽奖
     * @param $params
     * @author Tab
     * @date 2021/11/24 17:11
     */
    public static function lottery($params)
    {
        Db::startTrans();
        try {
            // 获取活动信息
            $activity = LuckyDraw::findOrEmpty($params['id']);

            // 获取用户信息
            $user = User::findOrEmpty($params['user_id']);

            // 获取奖品信息
            $prizes = self::prizeInfo($params['id']);

            // 随机抽奖
            $randomPrize = self::randomLottery($prizes);

            // 积分处理
            if ($activity->need_integral > 0) {
                // 扣减积分
                self::decIntegral($user, $activity->need_integral);
                // 记录账户流水
                AccountLogLogic::add($user->id, AccountLogEnum::INTEGRAL_DEC_LOTTERY,AccountLogEnum::DEC, $activity->need_integral, '', '幸运抽奖');
            }

            // 开奖处理
            $sendPrizeInfo = [
                'flag' => false,
                'msg' => ''
            ];
            if (in_array($randomPrize['type'], LuckyDrawEnum::WIN_PRIZE_TYPE)) {
                // 发奖
                $sendPrizeInfo = self::sendPrize($randomPrize, $user);
                // 扣减奖品数量
                self::decPrize($randomPrize);
            }

            // 增加抽奖记录
            self::addLotteryRecord($user, $activity, $randomPrize, $sendPrizeInfo);

            // 提交事务
            Db::commit();

            // 返回抽奖结果
            return [
                'location' => $randomPrize['location'],
                'tips' => $randomPrize['tips']
            ];
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 提取奖品信息
     * @param $activityId
     * @author Tab
     * @date 2021/11/24 18:02
     */
    private static function prizeInfo($activityId)
    {
        $prizes = LuckyDrawPrize::where('activity_id', $activityId)->select()->toArray();
        // 属于中奖的奖品
        $winPrize = [];
        // 属于不中奖的奖品
        $notWinPrize = [];
        foreach ($prizes as $item) {
            if ($item['type'] == LuckyDrawEnum::NOT_WIN) {
                $notWinPrize[] = $item;
                continue;
            }
            // 生成概率范围
            $winPrizeCount = count($winPrize);
            if ($winPrizeCount == 0) {
                // 首个属于中奖的奖品
                $item['min_range'] = 1;
                $item['max_range'] = $item['probability'];
            } else {
                $item['min_range'] = $winPrize[$winPrizeCount-1]['max_range'] + 1;
                $item['max_range'] = $winPrize[$winPrizeCount-1]['max_range'] + $item['probability'];
            }
            $winPrize[] = $item;
        }
        return [
            'win_prize' => $winPrize,
            'not_win_prize' => $notWinPrize,
        ];
    }

    /**
     * @notes 随机抽奖
     * @param $prizes
     * @author Tab
     * @date 2021/11/24 18:10
     */
    private static function randomLottery($prizes)
    {
        // 生成随机种子数
        $randInt = mt_rand(1, 10000);
        // 判断种子数命中哪个奖品,并且奖品数量充足
        foreach($prizes['win_prize'] as $item) {
            if ($randInt >= $item['min_range'] && $randInt <= $item['max_range'] && $item['num'] > 0) {
                return $item;
            }
        }
        if (count($prizes['not_win_prize']) == 0) {
            throw new \Exception('奖品数量已抽完，请下次再来');
        }
        // 随机返回一个属于未中奖的奖品
        $randInt = mt_rand(0, count($prizes['not_win_prize']) - 1);
        return $prizes['not_win_prize'][$randInt];
    }

    /**
     * @notes 扣减积分
     * @param $userId
     * @param $needIntegral
     * @author Tab
     * @date 2021/11/24 18:43
     */
    private static function decIntegral($user, $needIntegral)
    {
        $user->user_integral = $user->user_integral - $needIntegral;
        $user->duokaiSave();
    }

    /**
     * @notes 发奖
     * @param $randomPrize
     * @author Tab
     * @date 2021/11/24 18:58
     */
    private static function sendPrize($randomPrize, $user)
    {
        // 发奖信息
        $sendPrizeInfo = [
            'flag' => true,
            'msg' => '发奖成功',
        ];
        switch ($randomPrize['type']) {
            case LuckyDrawEnum::INTEGRAL:
                // 发放积分
                self::sendIntegral($randomPrize, $user);
                // 记录账户流水
                AccountLogLogic::add($user->id, AccountLogEnum::INTEGRAL_INC_LOTTERY,AccountLogEnum::INC, (int)$randomPrize['type_value'], '', '幸运抽奖');
                break;
            case LuckyDrawEnum::COUPON:
                // 发放优惠券
                $params = [
                    'id' => (int)$randomPrize['type_value'],
                    'send_user_num' => 1,
                    'send_user' => [$user->id],
                ];
                $result = \app\adminapi\logic\marketing\CouponLogic::send($params);
                if ($result !== true) {
                    $sendPrizeInfo['flag'] = false;
                    $sendPrizeInfo['msg'] = $result;
                }
                break;
            case LuckyDrawEnum::BALANCE:
                // 发放余额
                self::sendBalance($randomPrize, $user);
                // 记录账户流水
                AccountLogLogic::add($user->id, AccountLogEnum::BNW_INC_LOTTERY,AccountLogEnum::INC, (int)$randomPrize['type_value'], '', '幸运抽奖');
                break;
        }
        return $sendPrizeInfo;
    }

    /**
     * 发放积分
     */
    private static function sendIntegral($randomPrize, $user)
    {
        $user->user_integral = $user->user_integral + (int)$randomPrize['type_value'];
        $user->duokaiSave();
    }

    /**
     * @notes 发放余额
     * @param $randomPrize
     * @param $user
     * @author Tab
     * @date 2021/11/25 9:52
     */
    private static function sendBalance($randomPrize, $user)
    {
        $user->user_money = $user->user_money + (int)$randomPrize['type_value'];
        $user->duokaiSave();
    }

    /**
     * @notes 奖品数量减1
     * @param $randomPrize
     * @author Tab
     * @date 2021/11/25 9:45
     */
    private static function decPrize($randomPrize)
    {
        $prize = LuckyDrawPrize::findOrEmpty($randomPrize['id']);
        $prize->num = $prize->num - 1;
        $prize->duokaiSave();
    }

    /**
     * @notes 添加抽奖记录
     * @author Tab
     * @date 2021/11/25 10:17
     */
    private static function addLotteryRecord($user, $activity, $randomPrize, $sendPrizeInfo)
    {
        $data = [
            'user_id' => $user->id,
            'activity_id' => $activity->id,
            'prize_id' => $randomPrize['id'],
            'prize_type' => $randomPrize['type'],
            'is_send' => $sendPrizeInfo['flag'] ? YesNoEnum::YES : YesNoEnum::NO,
            'remark' => $sendPrizeInfo['msg'],
        ];

        LuckyDrawRecord::duokaiCreate($data);
    }

    /**
     * @notes 查看抽奖记录
     * @author Tab
     * @date 2021/11/25 10:55
     */
    public static function record($params)
    {
        $field = [
            'ldr.id',
            'ldr.is_send',
            'ld.need_integral',
            'ldp.name',
            'ldp.type',
            'ldp.type_value',
            'ldp.image',
            'ldr.create_time',
        ];
        $where = [
            'ldr.activity_id' => $params['id'],
            'ldr.user_id' => $params['user_id'],
        ];
        $lists = LuckyDrawRecord::withoutGlobalScope()
            ->aliasSid('ldr')
            ->alias('ldr')
            ->leftJoin('lucky_draw_prize ldp', 'ldp.id = ldr.prize_id')
            ->leftJoin('lucky_draw ld', 'ld.id = ldr.activity_id')
            ->field($field)
            ->where($where)
            ->order('ldr.id', 'desc')
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        $count = LuckyDrawRecord::withoutGlobalScope()
            ->aliasSid('ldr')
            ->alias('ldr')
            ->leftJoin('lucky_draw_prize ldp', 'ldp.id = ldr.prize_id')
            ->leftJoin('lucky_draw ld', 'ld.id = ldr.activity_id')
            ->field($field)
            ->where($where)
            ->count();

        foreach ($lists as &$item) {
            $item['title'] = self::formatTitle($item);
            $item['send_tips'] = !$item['is_send'] && $item['type'] != LuckyDrawEnum::NOT_WIN ? '自动领取失败，请联系客服人员' : '';
            $item['need_integral_tips'] = $item['need_integral'] > 0 ?  '-'. $item['need_integral'] . '积分' : '';
        }

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
     * @notes 格式化抽奖记录标题
     * @author Tab
     * @date 2021/11/25 11:10
     */
    private static function formatTitle($item)
    {
        $title = trim($item['name']);
        if ($item['type'] == LuckyDrawEnum::INTEGRAL) {
            $title .= '(' . $item['type_value'] . '积分)';
        }
        if ($item['type'] == LuckyDrawEnum::COUPON) {
            $couponName = Coupon::where('id', $item['type_value'])->value('name');
            $title .= empty($couponName) ? '(优惠券)' : '('.$couponName.')';
        }
        if ($item['type'] == LuckyDrawEnum::BALANCE) {
            $title .= '(' . $item['type_value'] . '元)';
        }

        return $title;
    }

    /**
     * @notes 格式化中奖名单标题
     * @param $item
     * @return string
     * @author Tab
     * @date 2021/11/25 14:32
     */
    private static function formatTitleWin($item)
    {
        $title = "恭喜";
        $userName = User::where('id', $item['user_id'])->value('nickname');
        // 隐私处理
        $userName = '**' . mb_substr($userName, -1 , 1);
        $title .= $userName . '抽中了';
        if ($item['type'] == LuckyDrawEnum::INTEGRAL) {
            $title .= $item['type_value'] . '积分';
        }
        if ($item['type'] == LuckyDrawEnum::COUPON) {
            $couponName = Coupon::where('id', $item['type_value'])->value('name');
            $title .= empty($couponName) ? '优惠券' : $couponName;
        }
        if ($item['type'] == LuckyDrawEnum::BALANCE) {
            $title .= $item['type_value'] . '元';
        }

        return $title;
    }

    /**
     * @notes 查看中奖名单
     * @author Tab
     * @date 2021/11/25 14:14
     */
    public static function winningList($params)
    {
        $field = [
            'ldr.id',
            'ldr.user_id',
            'ldr.create_time',
            'ldp.image',
            'ldp.name',
            'ldp.type',
            'ldp.type_value',
        ];
        $where = [
            ['ldr.prize_type', '<>', LuckyDrawEnum::NOT_WIN],
            ['ldr.activity_id', '=', $params['id']],
        ];
        $lists = LuckyDrawRecord::withoutGlobalScope()
            ->aliasSid('ldr')
            ->alias('ldr')
            ->field($field)
            ->leftJoin('lucky_draw_prize ldp', 'ldp.id = ldr.prize_id')
            ->where($where)
            ->order('ldr.id', 'desc')
            ->page($params['page_no'], $params['page_size'])
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['title'] = self::formatTitleWin($item);
        }

        $count = LuckyDrawRecord::withoutGlobalScope()
            ->aliasSid('ldr')
            ->alias('ldr')
            ->field($field)
            ->leftJoin('lucky_draw_prize ldp', 'ldp.id = ldr.prize_id')
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
}