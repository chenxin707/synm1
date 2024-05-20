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
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\shopapi\controller;

use app\common\enum\UserTerminalEnum;
use app\common\logic\PaymentLogic;
use app\common\service\pay\AliPayService;
use app\common\service\pay\ToutiaoPayService;
use app\common\service\pay\WeChatPayService;
use app\shopapi\validate\PayValidate;
use think\facade\Log;

/**
 * 支付
 * Class PayController
 * @package app\shopapi\controller
 */
class PayController extends BaseShopController
{

    public array $notNeedLogin = ['notifyMnp', 'notifyOa', 'notifyApp', 'aliNotify', 'toutiaoNotify'];


    /**
     * @notes 预支付
     * @return \think\response\Json
     * @throws \Exception
     * @author 段誉
     * @date 2021/8/3 14:03
     */
    public function prepay()
    {
        $params = (new PayValidate())->post()->goCheck();
        //订单信息
        $order = PaymentLogic::getPayOrderInfo($params);
        if (false === $order) {
            return $this->fail(PaymentLogic::getError(), $params);
        }
        //支付流程
        $result = PaymentLogic::pay($params['pay_way'], $params['from'], $order, $this->userInfo['terminal']);
        if (false === $result) {
            return $this->fail(PaymentLogic::getError(), $params);
        }
        return $this->success('', $result);
    }

    /**
     * @notes 小程序支付回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @author 段誉
     * @date 2021/8/13 14:17
     */
    public function notifyMnp()
    {
        return (new WeChatPayService(UserTerminalEnum::WECHAT_MMP))->notify();
    }


    /**
     * @notes 公众号支付回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @author 段誉
     * @date 2021/8/13 14:17
     */
    public function notifyOa()
    {
        return (new WeChatPayService(UserTerminalEnum::WECHAT_OA))->notify();
    }



    /**
     * @notes app支付回调
     * @author 段誉
     * @date 2021/8/13 14:16
     */
    public function notifyApp()
    {
        return (new WeChatPayService(UserTerminalEnum::IOS))->notify();
    }


    /**
     * @notes 支付宝回调
     * @return bool
     * @author 段誉
     * @date 2021/8/13 14:16
     */
    public function aliNotify()
    {
        $params = $this->request->post();
        $result = (new AliPayService())->notify($params);
        if (true === $result) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    /**
     * @notes 头条支付回调(字节小程序)
     * @return mixed
     * @author Tab
     * @date 2021/11/17 11:40
     */
    public function toutiaoNotify()
    {
        $params = $this->request->post();
        $result =  (new ToutiaoPayService())->notify($params);
        return $result;
    }


    /**
     * @notes  支付方式列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/8/13 14:33
     */
    public function payway()
    {
        $params = (new PayValidate())->goCheck('payway');
        $result = PaymentLogic::getPayWay($this->userId, $this->userInfo['terminal'], $params);
        if ($result === false) {
            return $this->fail(PaymentLogic::getError());
        }
        return $this->data($result);
    }

    public function payStatus()
    {
        $params = (new PayValidate())->goCheck('paystatus');
        $result = PaymentLogic::getPayStatus($params);
        if ($result === false) {
           return $this->fail(PaymentLogic::getError());
        }
        return $this->data($result);
    }

    /**
     * @notes 获取支付结果
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/6 15:23
     */
    public function payResult()
    {
        $params = (new PayValidate())->goCheck('payresult');
        $result = PaymentLogic::getPayResult($params);
        if ($result === false) {
            return $this->fail(PaymentLogic::getError());
        }
        return $this->data($result);
    }

}