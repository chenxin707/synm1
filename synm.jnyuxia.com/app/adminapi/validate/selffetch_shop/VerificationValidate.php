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

namespace app\adminapi\validate\selffetch_shop;


use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\model\Order;
use app\common\validate\BaseValidate;

class VerificationValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'confirm' => 'require|in:0,1'
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'confirm.require' => '参数缺失',
        'confirm.in' => '参数错误',
    ];

    public function sceneVerification()
    {
        return $this->only(['id','confirm'])
            ->append('id','checkVerification');
    }

    public function sceneVerificationQuery()
    {
        return $this->only(['id'])
            ->append('id','checkVerificationQuery');
    }

    public function sceneVerificationDetail()
    {
        return $this->only(['id'])
            ->append('id','checkVerificationDetail');
    }

    /**
     * @notes 检查订单ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/12 11:44 上午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Order::where('id', $value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }

    /**
     * @notes 检查订单是否可以提货核销
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/12 11:47 上午
     */
    public function checkVerification($value,$rule,$data)
    {
        $result = Order::where('id', $value)->findOrEmpty();
        if ($result['pay_status'] != PayEnum::ISPAID) {
            return '订单未支付，不允许核销';
        }
        if ($result['delivery_type'] != DeliveryEnum::SELF_DELIVERY) {
            return '非自提订单，不允许核销';
        }
        if ($result['verification_status'] == OrderEnum::WRITTEN_OFF) {
            return '订单已核销';
        }
        if ($result['order_type'] == OrderEnum::TEAM_ORDER){
            if ($result['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS){
                return '拼团成功后才能核销';
            }
        }
        return true;
    }

    /**
     * @notes 检查订单是否已核销
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/26 6:40 下午
     */
    public function checkVerificationQuery($value,$rule,$data)
    {
        $result = Order::where('id', $value)->findOrEmpty();
        if ($result['verification_status'] == OrderEnum::NOT_WRITTEN_OFF) {
            return '订单未核销，无法查询';
        }
        return true;
    }

    /**
     * @notes 检测是否为自提订单
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/27 10:49 上午
     */
    public function checkVerificationDetail($value,$rule,$data)
    {
        $result = Order::where('id', $value)->findOrEmpty();
        if ($result['delivery_type'] != DeliveryEnum::SELF_DELIVERY) {
            return '非自提订单，不允许核销';
        }
        return true;
    }

}