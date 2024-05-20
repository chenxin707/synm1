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

namespace app\common\model;

use app\common\enum\PayEnum;

/**
 * 充值订单模型
 * Class RechargeOrder
 * @package app\common\model
 */
class RechargeOrder extends BaseModel
{
    /**
     * @notes 奖励获取器
     * @param $value
     * @return array|mixed
     * @author Tab
     * @date 2021/8/11 9:31
     */
    public function getAwardAttr($value)
    {
        if(empty($value)) {
            return [];
        }
        return json_decode($value, true);
    }

    /**
     * @notes 充值金额获取器
     * @param $value
     * @return int|mixed|string
     * @author Tab
     * @date 2021/8/11 15:11
     */
    public function getOrderAmountAttr($value)
    {
        return clear_zero($value);
    }

    /**
     * @notes 支付方式获取器
     * @param $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/11 15:59
     */
    public function getPayWayAttr($value)
    {
        return PayEnum::getPayDesc($value);
    }

    /**
     * @notes 支付状态获取器
     * @param $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/11 16:06
     */
    public function getPayStatusAttr($value)
    {
        return PayEnum::getPayStatusDesc($value);
    }

    /**
     * @notes 关联用户授权模型
     * @return \think\model\relation\HasOne
     * @author ljj
     * @date 2023/7/5 4:39 下午
     */
    public function userAuth()
    {
        return $this->hasOne(UserAuth::class, 'user_id', 'user_id')
            ->field('user_id,openid');
    }
}