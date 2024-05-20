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

use app\common\enum\PayEnum;
use app\common\logic\BaseLogic;
use app\common\model\Config;
use app\common\model\RechargeOrder;
use app\common\model\RechargeTemplate;
use app\common\service\ConfigService;
use think\response\Json;

/**
 * 充值逻辑层
 * Class RechargeLogic
 * @package app\shopapi\logic
 */
class RechargeLogic extends BaseLogic
{
    public static function recharge($params)
    {
        try {
            // 校验数据
            self::validateData($params);
            if(isset($params['template_id'])) {
                // 选择模板充值
                return self::rechargeByTemplate($params);
            }else{
                // 输入金额充值
                return self::rechargeByMoney($params);
            }
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 校验数据
     * @param $params
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/11 10:52
     */
    public static function validateData($params)
    {
        $open = ConfigService::get('recharge', 'open');
        if(!$open) {
            throw new \think\Exception('充值功能已关闭');
        }
        if(!isset($params['pay_way'])) {
            throw new \think\Exception('请选择支付方式');
        }
        if(!isset($params['template_id']) && !isset($params['money'])) {
            throw new \think\Exception('请输入充值金额');
        }
    }

    /**
     * @notes 输入金额充值
     * @param $params
     * @author Tab
     * @date 2021/8/11 11:27
     */
    public static function rechargeByMoney($params)
    {
        if ($params['money'] <= 0) {
            throw new \think\Exception('充值金额须大于0');
        }
        $params['template_id'] = 0;
        $params['award'] = [];
        return self::addRechargeOrder($params);
    }

    /**
     * @notes 选择模板充值
     * @param $params
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/11 11:25
     */
    public static function rechargeByTemplate($params)
    {
        $template = RechargeTemplate::findOrEmpty($params['template_id']);
        if($template->isEmpty()) {
            throw new \think\Exception('充值模板不存在');
        }
        $params['money'] = $template->money;
        $params['template_id'] = $template->id;
        $params['award'] = $template->award;
        return self::addRechargeOrder($params);
    }

    /**
     * @notes 添加充值订单
     * @param $params
     * @author Tab
     * @date 2021/8/11 11:23
     */
    public static function addRechargeOrder($params)
    {
        $minAmount = ConfigService::get('recharge', 'min_amount');
        if($params['money'] < $minAmount) {
            throw new \think\Exception('最低充值金额:' . $minAmount . "元");
        }
        $award = empty($params['award']) ? '' : json_encode($params['award'], JSON_UNESCAPED_UNICODE);
        $data = [
            'sn'      => generate_sn((new RechargeOrder()),'sn'),
            'terminal'      => $params['terminal'],
            'user_id'       => $params['user_id'],
            'pay_status'    => PayEnum::UNPAID,
            'pay_way'       => $params['pay_way'],
            'order_amount'  => $params['money'],
            'template_id'   => $params['template_id'],
            'award'         => $award
        ];

        $order = RechargeOrder::duokaiCreate($data);
        return [
            'order_id' => $order->id,
            'from' => 'recharge'
        ];
    }
}