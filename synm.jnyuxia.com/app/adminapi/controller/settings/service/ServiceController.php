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

namespace app\adminapi\controller\settings\service;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\settings\service\ServiceLogic;

/**
 * 客服设置控制器
 * Class ServiceController
 * @package app\adminapi\controller\settings\service
 */
class ServiceController extends BaseAdminController
{
    /**
     * @notes 获取客服设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/11 17:27
     */
    public function getConfig()
    {
        $result = ServiceLogic::getConfig();
        return $this->data($result);
    }

    /**
     * @notes 客服设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/11 17:36
     */
    public function setConfig()
    {
        $params = $this->request->post();
        ServiceLogic::setConfig($params);
        return $this->success('设置成功', [], 1, 1);
    }
}