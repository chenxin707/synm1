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

namespace app\adminapi\controller\settings\pay;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\settings\delivery\PayConfigLists;
use app\adminapi\logic\settings\pay\PayConfigLogic;
use app\adminapi\validate\settings\pay\PayConfigValidate;

class PayConfigController extends BaseAdminController
{
    /**
     * @notes 设置支付配置
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/27 5:29 下午
     */
    public function setConfig()
    {
        $params = (new PayConfigValidate())->post()->goCheck();
        (new PayConfigLogic())->setConfig($params);
        return $this->success('设置成功',[],1,1);
    }

    /**
     * @notes 查看支付配置
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/27 5:36 下午
     */
    public function getConfig()
    {
        $id = (new PayConfigValidate())->goCheck('get');
        $result = (new PayConfigLogic())->getConfig($id);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 查看支付配置列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/31 2:23 下午
     */
    public function lists()
    {
        return $this->dataLists(new PayConfigLists());
    }
}