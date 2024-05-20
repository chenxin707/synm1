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

namespace app\adminapi\controller\distribution;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\distribution\DistributionConfigLogic;
use app\adminapi\validate\distribution\DistributionConfigValidate;
use app\common\logic\DistributionOrderGoodsLogic;

/**
 * 分销配置控制器
 * Class DistributionConfigController
 * @package app\adminapi\controller\distribution
 */
class DistributionConfigController extends BaseAdminController
{
    /**
     * @notes 获取分销配置
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/22 9:39
     */
    public function getConfig()
    {
        $result = DistributionConfigLogic::getConfig();
        return $this->data($result);
    }

    /**
     * @notes 分销配置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/22 9:42
     */
    public function setConfig()
    {
        $params = (new DistributionConfigValidate())->post()->goCheck();
        $result  = DistributionConfigLogic::setConfig($params);
        if ($result) {
            return $this->success('设置成功');
        }
        return $this->fail(DistributionConfigLogic::getError());
    }

    // 测试用
    public function test()
    {
        DistributionOrderGoodsLogic::add(1);
    }
}