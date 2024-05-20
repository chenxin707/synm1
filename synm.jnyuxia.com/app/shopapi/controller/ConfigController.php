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
namespace app\shopapi\controller;
use app\common\service\ConfigService;
use app\shopapi\logic\ConfigLogic;

/**
 * 配置控制器
 * Class ConfigController
 * @package app\shopapi\controller
 */
class ConfigController extends BaseShopController
{
    public array $notNeedLogin = ['getconfig', 'getPolicyAgreement'];

    /**
     * @notes 获取商城配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/28 17:23
     */
    public function getConfig()
    {
        $data = (new ConfigLogic())->getConfig();
        return $this->success('',$data);
    }

    /**
     * @notes 获取政策协议
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/28 16:06
     */
    public function getPolicyAgreement()
    {
        $result =  (new ConfigLogic())->getPolicyAgreement();
        return $this->data(['content' => $result]);
    }


    /**
     * @notes 版权资质
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/22 4:03 下午
     */
    public function copyright()
    {
        $result = (new ConfigLogic())->copyright();
        return $this->success('',$result);

    }
}