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
namespace app\adminapi\controller;

use app\adminapi\logic\auth\AuthLogic;
use app\adminapi\logic\ConfigLogic;
use think\facade\Config;

/**
 * 配置控制器
 * Class ConfigController
 * @package app\adminapi\controller
 */
class ConfigController extends BaseAdminController
{
    public array $notNeedLogin = ['getConfig'];

    /**
     * @notes 获取配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/19 16:29
     */
    public function getConfig()
    {
        $data = ConfigLogic::getConfig();
        return $this->data($data);
    }

    /**
     * @notes 获取菜单
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/25 17:46
     */
    public function getMenu()
    {
        $auth = AuthLogic::getMenu('menu');
        return $this->data($auth);
    }

    /**
     * @notes 获取权限
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/25 19:32
     */
    public function getAuth()
    {
        $data = ConfigLogic::getAuth($this->adminInfo);
        return $this->data($data);
    }

    /**
     * @notes 获取营销模块接口
     * @return \think\response\Jon
     * @author cjhao
     * @date 2021/9/8 17:19
     */
    public function getMarketingModule()
    {
        $data = ConfigLogic::getMarketingModule($this->adminInfo);
        return $this->data($data);
    }

    /**
     * @notes 获取应用中心模块接口
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/24 16:58
     */
    public function getAppModule()
    {
        $data = ConfigLogic::getAppModule($this->adminInfo);
        return $this->data($data);
    }


}
