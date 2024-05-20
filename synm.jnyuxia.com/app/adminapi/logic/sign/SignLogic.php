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

namespace app\adminapi\logic\sign;

use app\common\enum\AccountLogEnum;
use app\common\enum\SignEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AccountLog;
use app\common\model\SignDaily;
use app\common\model\SignLog;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Config;
use think\facade\Db;

/**
 * 签到逻辑层
 * Class SignLogic
 * @package app\adminapi\logic
 */
class SignLogic extends BaseLogic
{
    /**
     * @notes 获取签到规则
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/16 9:38
     */
    public static function getConfig()
    {
        $daily = SignDaily::field('integral_status,integral')
            ->where('type', SignEnum::DAILY)
            ->findOrEmpty()
            ->toArray();
        if(empty($daily)) {
            $daily = [
                'integral_status' => YesNoEnum::NO,
                'integral' => 0
            ];
        }
        $continuous = SignDaily::field('id,integral_status,integral,days,sort')
            ->where('type', SignEnum::CONTINUOUS)
            ->order(['sort'=>'desc','days'=>'asc'])
            ->select()
            ->toArray();
        $config = [
            'is_open' => ConfigService::get('sign', 'is_open', YesNoEnum::YES),
            'daily' => $daily,
            'continuous'  => $continuous,
            'remark' => ConfigService::get('sign', 'remark')
        ];

        return $config;
    }

    /**
     * @notes 设置签到规则
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/16 10:29
     */
    public static function setConfig($params)
    {
        Db::startTrans();
        try {
            // 更新状态
            ConfigService::set('sign', 'is_open', $params['is_open']);

            //删除旧的签到规则
            $sing_daily_ids = SignDaily::where('id','>',0)->column('id');
            SignDaily::duokaiDestroy($sing_daily_ids);
            //添加每日签到规则
            SignDaily::duokaiCreate(['type'=>SignEnum::DAILY,'integral'=>$params['daily']['integral'],'integral_status'=>$params['daily']['integral_status'],'days'=>1]);
            //添加连续签到规则
            foreach ($params['continuous'] as $val) {
                SignDaily::duokaiCreate(['type'=>SignEnum::CONTINUOUS,'integral'=>$val['integral'],'integral_status'=>1,'days'=>$val['days'],'sort'=>$val['sort'] ?? 0]);
            }

            // 更新签到说明
            ConfigService::set('sign', 'remark', $params['remark']);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 添加连续签到规则
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/16 15:34
     */
    public static function add($params)
    {
        try {
            SignDaily::duokaiCreate($params);
            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 编辑连续签到规则
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/16 15:43
     */
    public static function edit($params)
    {
        try {
            SignDaily::duokaiUpdate($params, [], [], '', false);
            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 删除连续签到规则
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/16 15:50
     */
    public static function delete($params)
    {
        try {
            SignDaily::duokaiDestroy($params['id']);
            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 查看连续签到规则详情
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/8/16 11:42
     */
    public static function detail($params)
    {
        $signDialy = SignDaily::field('integral, integral_status, days, id')
            ->where('id', $params['id'])
            ->findOrEmpty()
            ->toArray();

        return $signDialy;
    }

    /**
     * @notes 重置说明
     * @author Tab
     * @date 2021/8/16 19:26
     */
    public static function resetRemark()
    {
        $default = Config::get('project.sign.remark');
        ConfigService::set('sign', 'remark', $default);
    }

    /**
     * @notes 签到数据中心
     * @return array
     * @author Tab
     * @date 2021/8/16 19:56
     */
    public static function dataCenter()
    {
        return [
            'sign_data' => self::signData(),
            'recent_data' => self::recentData(),
            'top_data' => self::topData()
        ];
    }

    /**
     * @notes 签到数据
     * @return array
     * @author Tab
     * @date 2021/8/16 19:59
     */
    public static function signData()
    {
        $totalSign = SignLog::count();
        $totalIntegral = AccountLog::where('change_type', AccountLogEnum::INTEGRAL_INC_SIGN)->sum('change_amount');
        return [
            'total_sign' => $totalSign,
            'total_integral' => $totalIntegral
        ];
    }

    /**
     * @notes 近30天签到数据
     * @return array
     * @author Tab
     * @date 2021/8/16 20:50
     */
    public static function recentData()
    {
        $field = [
            "FROM_UNIXTIME(create_time,'%Y%m%d') as date",
            "count(id) as num"
        ];
        $lists = SignLog::field($field)
            ->group('date')
            ->whereMonth('create_time')
            ->select()
            ->toArray();
        $lists = array_column($lists, 'num', 'date');

        $data = [];
        for($i = 0; $i < 30; $i ++) {
            $today = new \DateTime();
            $targetDay = $today->add(\DateInterval::createFromDateString('-'. $i . 'day'));
            $targetDay = $targetDay->format('Ymd');
            $item['date'] = $targetDay;
            $item['num'] = isset($lists[$targetDay]) ? $lists[$targetDay]: 0;
            $data[] = $item;
        }
        return $data;
    }

    /**
     * @notes 签到排行榜
     * @return array
     * @author Tab
     * @date 2021/8/16 21:07
     */
    public static function topData()
    {
        $fieldNum = 'count(sl.id) as num, u.nickname,u.avatar';
        $topNum = SignLog::withoutGlobalScope()
            ->aliasSid('sl')
            ->alias('sl')
            ->leftJoin('user u', 'u.id = sl.user_id')
            ->field($fieldNum)
            ->group('u.id')
            ->order('num', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        foreach($topNum as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
        }
        $fieldAward = 'sum(al.change_amount) as amount, u.nickname,u.avatar';
        $topAward = AccountLog::withoutGlobalScope()
            ->aliasSid('al')
            ->alias('al')
            ->leftJoin('user u', 'u.id = al.user_id')
            ->field($fieldAward)
            ->where('change_type', AccountLogEnum::INTEGRAL_INC_SIGN)
            ->group('u.id')
            ->order('amount', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        foreach($topAward as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
            $item['amount'] = (int)$item['amount'];
        }
        return [
            'top_num' => $topNum,
            'top_award' => $topAward
        ];
    }
}