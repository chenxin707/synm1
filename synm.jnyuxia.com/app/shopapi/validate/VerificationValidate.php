<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\shopapi\validate;


use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\TeamEnum;
use app\common\model\Order;
use app\common\model\SelffetchVerifier;
use app\common\validate\BaseValidate;

class VerificationValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'pickup_code' => 'require|checkPickupCode',
        'confirm' => 'require|in:0,1'
    ];

    protected $message = [
        'pickup_code.require' => '参数缺失',
        'confirm.require' => '参数缺失',
        'confirm.in' => '参数错误',
    ];

    public function sceneVerification()
    {
        return $this->only(['pickup_code','confirm']);
    }

    public function sceneVerificationConfirm()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检测订单是否可以核销
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/27 5:19 下午
     */
    public function checkPickupCode($value,$rule,$data)
    {
        $result = Order::where(['pickup_code'=>$value])->find();
        if (empty($result)) {
            return '提货码不正确';
        }
        if ($result['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY) {
            return '订单不允许核销';
        }
        if ($result['delivery_type'] != DeliveryEnum::SELF_DELIVERY) {
            return '不是自提订单，不允许核销';
        }
        if ($result['verification_status'] == OrderEnum::WRITTEN_OFF) {
            return '订单已核销';
        }
        if ($result['order_type'] == OrderEnum::TEAM_ORDER){
            if ($result['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS){
                return '拼团成功后才能核销';
            }
        }
        $verifier = SelffetchVerifier::where(['selffetch_shop_id'=>$result['selffetch_shop_id'],'user_id'=>$data['user_id'],'status'=>1])->find();
        if (empty($verifier)) {
            return '非门店核销员，无法核销订单';
        }

        return true;
    }

    /**
     * @notes 检查订单是否可以提货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/27 5:46 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Order::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        if ($result['order_status'] != OrderEnum::STATUS_WAIT_DELIVERY) {
            return '订单不允许提货';
        }
        if ($result['delivery_type'] != DeliveryEnum::SELF_DELIVERY) {
            return '不是自提订单，不允许提货';
        }
        if ($result['verification_status'] == OrderEnum::WRITTEN_OFF) {
            return '订单已核销';
        }
        if ($result['order_type'] == OrderEnum::TEAM_ORDER){
            if ($result['is_team_success'] != TeamEnum::TEAM_FOUND_SUCCESS){
                return '拼团成功后才能提货';
            }
        }
        $verifier = SelffetchVerifier::where(['selffetch_shop_id'=>$result['selffetch_shop_id'],'user_id'=>$data['user_id'],'status'=>1])->find();
        if (empty($verifier)) {
            return '非门店核销员，无法核销订单';
        }

        return true;
    }
}