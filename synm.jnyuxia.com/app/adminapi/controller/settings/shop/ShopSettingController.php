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

namespace app\adminapi\controller\settings\shop;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\settings\shop\ShopSettingLogic;
use app\adminapi\validate\settings\shop\ShopSettingsValidate;

/**
 * 店铺设置控制器
 * Class ShopSettingController
 * @package app\adminapi\controller\settings\shop
 */
class ShopSettingController extends BaseAdminController
{
    /**
     * @notes 获取店铺信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 14:17
     */
    public function getShopInfo()
    {
        $result = ShopSettingLogic::getShopInfo();
        return $this->data($result);
    }

    /**
     * @notes 设置店铺信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 14:42
     */
    public function setShopInfo()
    {
        $params = (new ShopSettingsValidate())->post()->goCheck('setShopInfo');
        ShopSettingLogic::setShopInfo($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 获取备案信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 15:01
     */
    public function getRecordInfo()
    {
        $result = ShopSettingLogic::getRecordInfo();
        return $this->data($result);
    }

    /**
     * @notes 设置备案信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 15:22
     */
    public function setRecordInfo()
    {
        $params = $this->request->post();
        ShopSettingLogic::setRecordInfo($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 获取分享设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 15:25
     */
    public function getShareSetting()
    {
        $result = ShopSettingLogic::getShareSetting();
        return $this->data($result);
    }

    /**
     * @notes 分享设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 15:38
     */
    public function setShareSetting()
    {
        $params = (new ShopSettingsValidate())->post()->goCheck('setShareSetting');
        ShopSettingLogic::setShareSetting($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 获取政策协议
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 16:06
     */
    public function getPolicyAgreement()
    {
        $result = ShopSettingLogic::getPolicyAgreement();
        return $this->data($result);
    }

    /**
     * @notes 设置政策协议
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 16:11
     */
    public function setPolicyAgreement()
    {
        $params = $this->request->post();
        ShopSettingLogic::setPolicyAgreement($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 设置地图秘钥
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/10 5:12 下午
     */
    public function setMapKey()
    {
        $params = $this->request->post();
        ShopSettingLogic::setMapKey($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 获取地图秘钥
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/10 5:12 下午
     */
    public function getMapKey()
    {
        $result = ShopSettingLogic::getMapKey();
        return $this->data($result);
    }

}