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


use app\shopapi\logic\Order\CartLogic;
use app\shopapi\validate\CartValidate;

/**
 * 购物车
 * Class CartController
 * @package app\shopapi\controller
 */
class CartController extends BaseShopController
{

    /**
     * @notes 购物车列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/20 16:29
     */
    public function lists()
    {
        return $this->data(CartLogic::getCartLists($this->userId));
    }


    /**
     * @notes 添加
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/19 19:10
     */
    public function add()
    {
        $params = (new CartValidate())->post()->gocheck('add', ['user_id' => $this->userId]);
        $result = CartLogic::addCart($params, $this->userId);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('加入成功');
    }


    /**
     * @notes 删除
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/19 19:10
     */
    public function del()
    {
        $params = (new CartValidate())->post()->goCheck('del', ['user_id' => $this->userId]);
        CartLogic::del($params['cart_id'], $params['user_id']);
        return $this->success('删除成功');
    }


    /**
     * @notes 变更购物车数量
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/19 19:10
     */
    public function change()
    {
        $params = (new CartValidate())->post()->goCheck('change', ['user_id' => $this->userId]);
        $result = CartLogic::changeCartNum($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success();
    }


    /**
     * @notes 选中状态
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/19 19:10
     */
    public function selected()
    {
        $params = (new CartValidate())->post()->goCheck('selected', ['user_id' => $this->userId]);
        CartLogic::selected($params);
        return $this->success();
    }


    /**
     * @notes 购物车数量
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/19 19:11
     */
    public function num()
    {
        return $this->data(CartLogic::getCartNum($this->userId));
    }


}