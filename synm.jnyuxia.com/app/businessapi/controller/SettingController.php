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
namespace app\businessapi\controller;

use app\businessapi\validate\ResetPasswordValidate;
use app\businessapi\logic\SettingLogic;
use app\businessapi\validate\ShopSettingsValidate;

/**
 * 配置逻辑类
 * Class SettingController
 * @package app\businessapi\controller
 */
class SettingController extends BaseBusinesseController
{

    /**
     * @notes 获取店铺配置
     * @return array
     * @author cjhao
     * @date 2023/2/16 14:35
     */
    public function getShopConfig()
    {

        $config = (new SettingLogic())->getShopConfig();
        return $this->data($config);
    }


    /**
     * @notes 设置店铺配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/16 14:38
     */
    public function setShopConfig()
    {
        $params = (new ShopSettingsValidate())->post()->goCheck();
        (new SettingLogic())->setShopConfig($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 设置用户信息
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/16 14:45
     */
    public function getAdminInfo()
    {
        $info = (new SettingLogic())->getAdminInfo($this->adminId);
        return $this->data($info);
    }


    /**
     * @notes 修改管理员密码
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/4/21 15:16
     *
     */
    public function resetPassword(){
        $params = (new ResetPasswordValidate())->post()->goCheck(null,['admin_id'=>$this->adminId]);
        $result = (new SettingLogic())->resetPassword($params,$this->adminId);
        if(true === $result){
            return $this->success('密码修改成功',[],1,1);
        }
        return $this->fail($result);
    }
}