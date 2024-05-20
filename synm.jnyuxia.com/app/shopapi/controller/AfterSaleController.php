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

use app\common\service\after_sale\AfterSaleService;
use app\shopapi\logic\AfterSaleLogic;
use app\shopapi\validate\AfterSaleValidate;
use think\facade\Db;
use think\Validate;

/**
 * 售后控制器
 * Class AfterSaleController
 * @package app\shopapi\controller
 */
class AfterSaleController extends BaseShopController
{
    /**
     * @notes 子订单商品信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/31 17:52
     */
    public function orderGoodsInfo()
    {
        $params = (new AfterSaleValidate())->goCheck('orderGoodsInfo');
        $result = AfterSaleLogic::orderGoodsInfo($params);
        return $this->data($result);
    }

    /**
     * @notes 申请商品售后
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/2 11:34
     */
    public function apply()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('apply');
        $params['user_id'] = $this->userId;
        $result = AfterSaleLogic::apply($params);
        if($result === false) {
            return $this->fail(AfterSaleLogic::getError());
        }
        return $this->success('申请商品售后成功', $result);

    }

    /**
     * @notes 买家取消售后
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 10:05
     */
    public function cancel()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('cancel');
        $params['user_id'] = $this->userId;
        $result = AfterSaleLogic::cancel($params);
        if($result) {
            return $this->success('买家取消售后成功');
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 买家确认退货
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/3 11:25
     */
    public function returnGoods()
    {
        $params = (new AfterSaleValidate())->post()->goCheck('returnGoods');
        $params['user_id'] = $this->userId;
        $result = AfterSaleLogic::returnGoods($params);
        if($result) {
            return $this->success('买家确认退货');
        }
        return $this->fail(AfterSaleLogic::getError());
    }

    /**
     * @notes 查看售后列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/10 10:09
     */
    public function lists()
    {
        $params = (new AfterSaleValidate())->goCheck('lists');
        $params['user_id'] = $this->userId;
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 25;
        $result = AfterSaleLogic::lists($params);
        return $this->data($result);
    }

    /**
     * @notes 查看售后详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/10 15:07
     */
    public function detail()
    {
        $params = (new AfterSaleValidate())->goCheck('detail');
        $result = AfterSaleLogic::detail($params);
        return $this->data($result);
    }

}