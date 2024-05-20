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

namespace app\shopapi\controller;


use app\shopapi\lists\CouponLists;
use app\shopapi\lists\CouponMyLists;
use app\shopapi\logic\CouponLogic;
use app\shopapi\validate\CouponValidate;
use think\response\Json;

class CouponController extends BaseShopController
{
    public array $notNeedLogin = ['lists'];
    /**
     * @notes 优惠券列表
     * @return Json
     * @author 张无忌
     * @date 2021/7/22 10:18
     */
    public function lists()
    {
        return $this->dataLists(new CouponLists());
    }

    /**
     * @notes 我的优惠券
     * @author 张无忌
     * @date 2021/7/22 10:18
     */
    public function my()
    {
        return $this->dataLists(new CouponMyLists());
    }

    /**
     * @notes 领取优惠券
     * @author 张无忌
     * @date 2021/7/22 10:19
     */
    public function receive()
    {
        $params = (new CouponValidate())->post()->goCheck('receive');
        $result = CouponLogic::receive($params, $this->userId);
        if ($result === true) {
            return $this->success('领取成功',[],1,1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 结算页优惠券
     * @return Json
     * @author Tab
     * @date 2021/9/8 10:53
     */
    public function orderCoupon()
    {
        $params = (new CouponValidate())->post()->goCheck('orderCoupon');
        $params['user_id'] = $this->userId;
        $result = CouponLogic::orderCoupon($params);
        return $this->success('', $result);
    }
}