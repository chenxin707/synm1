<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\shopapi\logic;

use app\common\enum\AccountLogEnum;
use app\common\enum\SignEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\BaseLogic;
use app\common\model\SignDaily;
use app\common\model\SignLog;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Db;

/**
 * 签到逻辑层
 * Class SignLogic
 * @package app\shopapi\logic
 */
class SignLogic extends BaseLogic
{
    /**
     * @notes 查看签到列表
     * @param $userId
     * @return array
     * @author Tab
     * @date 2021/8/16 16:49
     */
    public static function lists($userId)
    {
        // 用户信息
        $user = User::field('nickname,avatar,user_integral')->findOrEmpty($userId)->toArray();
        $user['avatar'] = FileService::getFileUrl($user['avatar']);
        // 先标识用户当天未签到
        $user['today_sign'] = false;
        // 先标识用户连续签到天数为0
        $user['continuous_days'] = 0;
        // 获取昨天签到记录
        $yesterdaySign = SignLog::where('user_id', $userId)->whereDay('create_time', 'yesterday')->findOrEmpty();
        if(!$yesterdaySign->isEmpty()) {
            // 读取连续签到天数
            $user['continuous_days'] = $yesterdaySign['days'];
        }
        // 获取当天签到记录
        $todaySign = SignLog::where('user_id', $userId)->whereDay('create_time')->findOrEmpty();
        if(!$todaySign->isEmpty()) {
            // 标识用户已签到
            $user['today_sign'] = true;
            // 读取连续签到天数
            $user['continuous_days'] = $todaySign['days'];
        }
        // 获取签到规则列表
        $signDailyLists = SignDaily::field('type,days,integral,integral_status')->order(['type' => 'asc', 'days' => 'asc'])->column('*', 'days');
        $lists =[];
        if(!empty($signDailyLists)) {
            // 格式化列表
            $lists = self::format($signDailyLists, $user);
        }

        $isOpen = ConfigService::get('sign', 'is_open', YesNoEnum::NO);
        $earn = [];
        if ($isOpen && $signDailyLists) {
            // 赚积分
            $earn[] = [
                'name' => '每日签到',
                'is_done' => $user['today_sign'],
                'integral' => $signDailyLists[1]['integral'],
                'icon' => FileService::getFileUrl('resource/image/shopapi/default/sign.png')
            ];
        }


        return [
            'user' => $user,
            'lists' => $lists,
            'earn' => $earn,
            'is_open' => $isOpen,
        ];
    }

    /**
     * @notes 格式化连续签到列表
     * @param $lists
     * @param $user
     * @return array
     * @author Tab
     * @date 2021/8/16 16:38
     */
    public static function format($lists, $user)
    {
        // 取最后一个数组元素
        $last = end($lists);
        $data = [];
        for($i = 1; $i <= $last['days']; $i++) {
            $item['day'] = $i . '天';
            if(isset($lists[$i]) && $i > 1) {
                // 每日签到奖励
                $dayIntegral = $lists[1]['integral_status'] ? $lists[1]['integral'] : 0;
                // 有连续签到奖励 ： 连续签到赠送积分 + 每日签到赠送积分
                $item['integral'] = $lists[$i]['integral_status'] ? ($lists[$i]['integral'] + $dayIntegral) : $dayIntegral;
                $item['is_sign'] = $i <= $user['continuous_days'] ? true : false;
            }else{
                // 没有连续签到奖励: 每日签到赠送积分
                $item['integral'] = $lists[1]['integral_status'] ? $lists[1]['integral'] : 0;
                $item['is_sign'] = $i <= $user['continuous_days'] ? true : false;
            }
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @notes 签到
     * @param $userId
     * @return bool
     * @author Tab
     * @date 2021/8/16 17:35
     */
    public static function sign($userId)
    {
        Db::startTrans();
        try {
            $isOpen = ConfigService::get('sign', 'is_open', YesNoEnum::YES);
            if(!$isOpen) {
                throw new \think\Exception('系统已关闭签到功能');
            }
            // 判断是否已签到
            $signLog = SignLog::where('user_id', $userId)->whereDay('create_time')->findOrEmpty();
            if(!$signLog->isEmpty()) {
                throw new \think\Exception('今天已签到,请明天再来');
            }
            // 判断是否连续签到
            $continuousDays = 0;
            $yesterdaySignLog = SignLog::where('user_id', $userId)->whereDay('create_time', 'yesterday')->findOrEmpty();
            if(!$yesterdaySignLog->isEmpty()) {
                // 记录已连续签到天数
                $continuousDays = $yesterdaySignLog['days'];
            }
            // 计算积分赠送数量
            $dailySign = SignDaily::order(['type' => 'asc', 'days' => 'asc'])->column('*', 'days');
            if(empty($dailySign)) {
                // 每日签到赠送积分
                $integral = 0;
                // 连续签到赠送积分
                $continuousIntegral = 0;
            }
            // 首次签到
            if($continuousDays == 0) {
                $integral = isset($dailySign[1]) &&  $dailySign[1]['integral_status'] ? $dailySign[1]['integral'] : 0;
                $continuousIntegral = 0;
            } else { // 连续签到
                $integral = isset($dailySign[1]) &&  $dailySign[1]['integral_status'] ? $dailySign[1]['integral'] : 0;
                $continuousIntegral = isset($dailySign[$continuousDays + 1]) && $dailySign[$continuousDays + 1]['integral_status'] ? $dailySign[$continuousDays + 1]['integral'] : 0;
            }
            if($integral == 0) {
                throw new \think\Exception('无赠送积分');
            }
            // 获取最大连续签到天数
            $maxDays = SignDaily::max('days');
            $continuousDays = $continuousDays >= $maxDays ? 0 : $continuousDays;
            $data = [
                'days' => $continuousDays + 1,
                'user_id' => $userId,
                'integral' => $integral,
                'continuous_integral' => $continuousIntegral
            ];
            $signLog = SignLog::duokaiCreate($data);
            // 增加用户积分
            $user = User::findOrEmpty($userId);
            $user->user_integral = $user->user_integral + $integral + $continuousIntegral;
            $user->duokaiSave();
            // 记录积分流水
            $remark = $continuousDays == 0 ? '每日签到' : '连续签到' . ($continuousDays + 1) . '天';
            AccountLogLogic::add($userId, AccountLogEnum::INTEGRAL_INC_SIGN, AccountLogEnum::INC, ($integral + $continuousIntegral), '', $remark);

            Db::commit();
            return [
                'total_integral' => $integral + $continuousIntegral,
                'days' => $continuousDays + 1
            ];
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }
}