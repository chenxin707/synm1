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

use app\shopapi\validate\UserAddressValidate;
use app\shopapi\logic\UserAddressLogic;


/**
 * 收货地址
 * Class UserAddressController
 * @package app\shopapi\controller
 */
class UserAddressController extends BaseShopController
{

    /**
     * @notes 列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:41
     */
    public function lists()
    {
        return $this->data(UserAddressLogic::getLists($this->userId));
    }


    /**
     * @notes 详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:41
     */
    public function detail()
    {
        $params = (new UserAddressValidate())->goCheck('detail', ['user_id' => $this->userId]);
        $result = UserAddressLogic::getDetail($params['id'], $this->userId);
        return $this->data($result);
    }


    /**
     * @notes 获取默认地址
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:41
     */
    public function getDefault()
    {
        $result = UserAddressLogic::getDefault($this->userId);
        return $this->data($result);
    }


    /**
     * @notes 设置默认地址
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:41
     */
    public function setDefault()
    {
        $params = (new UserAddressValidate())->post()->goCheck('set', ['user_id' => $this->userId]);
        UserAddressLogic::setDefault($params, $this->userId);
        return $this->success();
    }


    /**
     * @notes 添加收货地址
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:42
     */
    public function add()
    {
        $params = (new UserAddressValidate())->post()->goCheck('add');
        UserAddressLogic::addAddress($params, $this->userId);
        return $this->success();
    }


    /**
     * @notes 编辑收货地址
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:42
     */
    public function edit()
    {
        $params = (new UserAddressValidate())->post()->goCheck('', ['user_id' => $this->userId]);
        UserAddressLogic::editAddress($params);
        return $this->success();
    }


    /**
     * @notes 删除
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/7/22 11:42
     */
    public function del()
    {
        $params = (new UserAddressValidate())->post()->goCheck('del', ['user_id' => $this->userId]);
        UserAddressLogic::del($params);
        return $this->success();
    }

    /**
     * @notes 将省市区名称转为id
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 16:02
     */
    public function handleRegion()
    {
        $params = (new UserAddressValidate())->goCheck('handleRegion');
        $result = UserAddressLogic::handleRegion($params);
        return $this->data($result);
    }
}