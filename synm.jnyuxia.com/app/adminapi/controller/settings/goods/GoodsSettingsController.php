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

namespace app\adminapi\controller\settings\goods;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\settings\goods\GoodsSettingsLogic;
use app\adminapi\validate\settings\goods\GoodsSettingsValidate;

class GoodsSettingsController extends BaseAdminController
{
    /**
     * @notes 设置商品配置
     * @author ljj
     * @date 2021/7/26 5:46 下午
     */
    public function setConfig()
    {
        $params = (new GoodsSettingsValidate())->post()->goCheck();
        (new GoodsSettingsLogic())->setConfig($params);
        return $this->success('设置成功',[],1, 1);
    }

    /**
     * @notes 查看商品配置
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/26 6:04 下午
     */
    public function getConfig()
    {
        $result = (new GoodsSettingsLogic())->getConfig();
        return $this->success('获取成功',$result);
    }


    /**
     * @notes 设置商品采集配置
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/16 6:19 下午
     */
    public function setGatherKey()
    {
        $params = (new GoodsSettingsValidate())->post()->goCheck();
        (new GoodsSettingsLogic())->setGatherKey($params);
        return $this->success('设置成功',[],1, 1);
    }

    /**
     * @notes 获取商品采集配置
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/16 6:19 下午
     */
    public function getGatherKey()
    {
        $result = (new GoodsSettingsLogic())->getGatherKey();
        return $this->success('获取成功',$result);
    }
}