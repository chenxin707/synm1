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

namespace app\platformapi\controller\settings;

use app\common\service\JsonService;
use app\platformapi\controller\BasePlatformController;
use app\platformapi\logic\settings\PlatformLogic;
use app\platformapi\validate\settings\PlatformValidate;

/**
 * 平台设置
 */
class PlatformController extends BasePlatformController
{
    public array $notNeedLogin = ['getConfig'];

    /**
     * @notes 获取台基础信息
     * @author Tab
     * @date 2021/12/13 17:25
     */
    public function getBaseConfig()
    {
        $result = PlatformLogic::getBaseConfig();
        return JsonService::data($result);
    }

    /**
     * @notes 平台基础信息设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/13 18:26
     */
    public function setBaseConfig()
    {
        $params = (new PlatformValidate())->post()->goCheck('setBaseConfig');
        $result = PlatformLogic::setBaseConfig($params);
        if ($result) {
            return JsonService::success('设置成功', [],1 ,1);
        }
        return JsonService::fail(PlatformLogic::getError());
    }

    /**
     * @notes 获取备案信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/14 11:08
     */
    public function getRecordConfig()
    {
        $result = PlatformLogic::getRecordConfig();
        return JsonService::data($result);
    }

    /**
     * @notes 设置备案信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/14 11:13
     */
    public function setRecordConfig()
    {
        if (!$this->request->isPost()) {
            return JsonService::fail('请求方式错误');
        }
        $params = $this->request->post();
        $result = PlatformLogic::setRecordConfig($params);
        if ($result) {
            return JsonService::success('设置成功', [],1 ,1);
        }
        return JsonService::fail(PlatformLogic::getError());
    }

    /**
     * @notes 获取所有配置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/17 11:29
     */
    public function getConfig()
    {
        $result = PlatformLogic::getConfig();
        return JsonService::data($result);
    }
}