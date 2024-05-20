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

namespace app\adminapi\controller\after_sale;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\after_sale\AfterSaleLogic;
use app\adminapi\validate\after_sale\AfterSaleValidate;
use app\common\model\Order;
use app\common\model\Refund;
use app\common\service\pay\AliPayService;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use think\facade\Log;

/**
 * 售后控制器
 * Class AfterSaleController
 * @package app\adminapi\controller\after_sale
 */
class AfterSaleController extends BaseAdminController
{
    /**
     * @notes 查看售后列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/2 14:16
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 卖家同意售后
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/2 19:28
     */
    public function agree()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('agree');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::agree($params);
        if($result) {
            return  $this->success('卖家同意售后', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 卖家拒绝售后
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/2 19:57
     */
    public function refuse()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('refuse');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::refuse($params);
        if($result) {
            return  $this->success('卖家拒绝售后', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 卖家拒绝收货
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 11:55
     */
    public function refuseGoods()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('refuseGoods');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::refuseGoods($params);
        if($result) {
            return  $this->success('卖家拒绝收货', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 卖家确认收货
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 14:01
     */
    public function confirmGoods()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('confirmGoods');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::confirmGoods($params);
        if($result) {
            return  $this->success('卖家确认收货', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 卖家同意退款
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 14:18
     */
    public function agreeRefund()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('agreeRefund');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::agreeRefund($params);
        if($result) {
            return  $this->success('卖家同意退款', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 卖家拒绝退款
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 14:28
     */
    public function refuseRefund()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('refuseRefund');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::refuseRefund($params);
        if($result) {
            return  $this->success('卖家拒绝退款', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 卖家确认退款
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 10:47
     */
    public function confirmRefund()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('confirmRefund');
        $params['admin_id'] = $this->adminId;
        $result = AfterSaleLogic::confirmRefund($params);
        if($result) {
            return  $this->success('卖家确认退款', [], 1, 1);
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 查看售后详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/9 18:08
     */
    public function detail()
    {
        $params = (new AfterSaleValidate())->goCheck('detail');
        $result = AfterSaleLogic::detail($params);
        return $this->data($result);
    }
}