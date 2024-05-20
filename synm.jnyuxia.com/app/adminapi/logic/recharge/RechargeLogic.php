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

namespace app\adminapi\logic\recharge;

use app\common\enum\PayEnum;
use app\common\logic\BaseLogic;
use app\common\model\RechargeOrder;
use app\common\model\RechargeTemplate;
use app\common\service\ConfigService;
use app\common\service\FileService;

/**
 * 充值逻辑层
 * Class RechargeLogic
 * @package app\adminapi\logic\recharge
 */
class RechargeLogic extends BaseLogic
{
    /**
     * @notes 获取充值设置
     * @return array
     * @author Tab
     * @date 2021/8/10 17:19
     */
    public static function getConfig()
    {
        $config = [
            // 充值设置
            'set' => self::getSet(),
            // 充值规则
            'rule' => self::getRule(),
        ];

        return $config;
    }

    /**
     * @notes 充值设置
     * @return array
     * @author Tab
     * @date 2021/8/10 17:31
     */
    public static function getSet()
    {
        $set = [
            'open' => ConfigService::get('recharge', 'open'),
            'min_amount' => ConfigService::get('recharge', 'min_amount')
        ];
        return $set;
    }

    /**
     * @notes 获取规则
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/11 9:36
     */
    public static function getRule()
    {
        $lists = RechargeTemplate::field('id,money,award')->select()->toArray();
        return $lists;
    }

    /**
     * @notes 充值设置
     * @param $params
     * @author Tab
     * @date 2021/8/10 17:59
     */
    public static function setConfig($params)
    {
        try {
            // 更新设置
            self::updateSet($params);
            // 更新规则
            self::updateRule($params);

            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 更新设置
     * @param $params
     * @author Tab
     * @date 2021/8/10 17:59
     */
    public static function updateSet($params)
    {
        if(isset($params['open'])) {
            ConfigService::set('recharge', 'open', $params['open']);
        }

        if (isset($params['min_amount']) && $params['min_amount'] < 0) {
            throw new \Exception('最低充值金额不能为负数');
        }
        if(isset($params['min_amount'])) {
            ConfigService::set('recharge', 'min_amount', $params['min_amount']);
        }
    }

    /**
     * @notes 更新规则
     * @param $params
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/10 19:14
     */
    public static function updateRule($params)
    {
        // 清除旧数据
        $deleteIds = RechargeTemplate::column('id');
        RechargeTemplate::duokaiDestroy($deleteIds);

        // 未设置充值规则,直接返回
        if(!isset($params['rule']) || empty($params['rule'])) {
            return true;
        }

        if (!is_array($params['rule'])) {
            throw new \Exception('充值规则格式不正确');
        }

        $data = [];
        foreach($params['rule'] as $key => $item) {
            $data[] = self::validateData($key, $item);
        }
        (new RechargeTemplate())->duokaiSaveAll($data);

    }

    /**
     * @notes 校验数据
     * @param $key
     * @param $item
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/10 18:58
     */
    public static function validateData($key, $item)
    {
        if(!isset($item['money'])) {
            throw new \think\Exception('规则' . ($key + 1) . '请输入充值金额');
        }
        if($item['money'] <= 0) {
            throw new \think\Exception('规则' . ($key + 1) . '充值金额须大于0');
        }
        if(!isset($item['award'])) {
            throw new \think\Exception('规则' . ($key + 1) . '请选择充值奖励');
        }
        if(!is_array($item['award']) || empty($item['award'])) {
            throw new \think\Exception('规则' . ($key + 1) . '充值奖励格式错误或为空');
        }
        foreach($item['award'] as $subItem) {
            if ($subItem['give_money'] < 0) {
                throw new \think\Exception('规则' . ($key + 1) . '充值奖励不能为负数');
            }
        }
        $item['award'] = json_encode($item['award'], JSON_UNESCAPED_UNICODE);
        return $item;
    }

    /**
     * @notes 充值数据中心
     * @return array
     * @author Tab
     * @date 2021/8/11 16:45
     */
    public static function dataCenter()
    {
        return [
            'recharge_data' => self::rechargeData(),
            'top_user' => self::topUser(),
            'top_rule' => self::topRule(),
        ];
    }

    /**
     * @notes 充值数据
     * @return array
     * @author Tab
     * @date 2021/8/11 16:48
     */
    public static function rechargeData()
    {
        $totalAmount = RechargeOrder::where(['pay_status' => PayEnum::ISPAID
        ])->sum('order_amount');
        $totalTimes = RechargeOrder::where(['pay_status' => PayEnum::ISPAID
        ])->count();
        return [
            'total_amount' => $totalAmount,
            'total_times' => $totalTimes
        ];
    }

    /**
     * @notes 用户充值榜
     * @return mixed
     * @author Tab
     * @date 2021/8/11 16:55
     */
    public static function topUser()
    {
        $field = 'u.nickname,u.avatar,sum(ro.order_amount) as total_amount';
        $lists = RechargeOrder::withoutGlobalScope()
            ->aliasSid('ro')
            ->alias('ro')
            ->leftJoin('user u', 'u.id = ro.user_id')
            ->field($field)
            ->where('pay_status', PayEnum::ISPAID)
            ->group('ro.user_id')
            ->order('total_amount', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl( $item['avatar']);
        }

        return $lists;
    }

    /**
     * @notes 充值规则榜
     * @return mixed
     * @author Tab
     * @date 2021/8/11 17:03
     */
    public static function topRule()
    {
        $field = 'rt.money, count(ro.id) as total_num';
        $lists =RechargeTemplate::withoutGlobalScope()
            ->aliasSid('rt')
            ->alias('rt')
            ->leftJoin('recharge_order ro', 'ro.template_id = rt.id')
            ->field($field)
            ->where('pay_status', PayEnum::ISPAID)
            ->group('rt.id')
            ->order('total_num', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        return $lists;
    }
}