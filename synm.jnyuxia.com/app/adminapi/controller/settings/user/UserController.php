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
namespace app\adminapi\controller\settings\user;
use app\adminapi\{
    controller\BaseAdminController,
    logic\settings\user\UserLogic,
    validate\settings\user\UserConfigValidate
};


/**
 * 设置-用户设置控制器
 * Class UserController
 * @package app\adminapi\controller\config
 */
class UserController extends BaseAdminController
{

    /**
     * @notes 获取用户设置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/27 17:29
     */
    public function getConfig()
    {
        $result = (new UserLogic())->getConfig();
        return $this->data($result);
    }

    /**
     * @notes 设置用户设置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/27 17:59
     */
    public function setConfig()
    {
        $params = (new UserConfigValidate())->post()->goCheck('user');
        (new UserLogic())->setConfig($params);
        return $this->success('设置成功',[],1,1);

    }

    /**
     * @notes 获取注册配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/14 17:11
     */
    public function getRegisterConfig()
    {
        $result = (new UserLogic())->getRegisterConfig();
        return $this->data($result);
    }

    /**
     * @notes 设置注册配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/14 17:29
     */
    public function setRegisterConfig()
    {
        $params = (new UserConfigValidate())->post()->goCheck('register');
        (new UserLogic())->setRegisterConfig($params);
        return $this->success('设置成功',[],1,1);
    }

    /**
     * @notes 获取提现配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/14 17:30
     */
    public function getWithdrawConfig()
    {
        $result = (new UserLogic())->getWithdrawConfig();
        return $this->data($result);
    }

    /**
     * @notes 提现配置设置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/16 15:13
     */
    public function setWithdrawConfig()
    {
        $params = (new UserConfigValidate())->post()->goCheck('withdraw');
        (new UserLogic())->setWithdrawConfig($params);
        return $this->success('设置成功',[],1,1);
    }

}