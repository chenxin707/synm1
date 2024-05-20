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

use app\platformapi\controller\BasePlatformController;
use app\platformapi\logic\settings\PayConfigLogic;
use app\platformapi\validate\settings\PayConfigValidate;

/**
 * 支付配置
 */
class PayConfigController extends BasePlatformController
{
    /**
     * @notes 支付列表
     */
    public function lists() {
        $data = PayConfigLogic::lists();
        return $this->data($data);
    }

    /**
     * @notes 获取支付配置
     */
    public function getConfig()
    {
        $params = (new PayConfigValidate())->goCheck('get');
        $result = (new PayConfigLogic())->getConfig($params);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 支付配置
     */
    public function setConfig()
    {
        $params = (new PayConfigValidate())->post()->goCheck();
        (new PayConfigLogic())->setConfig($params);
        return $this->success('设置成功',[],1,1);
    }
}
