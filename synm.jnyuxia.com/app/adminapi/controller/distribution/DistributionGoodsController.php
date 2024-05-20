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
use app\adminapi\logic\distribution\DistributionGoodsLogic;
use app\adminapi\validate\distribution\DistributionGoodsValidate;

/**
 * 分销商品控制器
 * Class DistributionGoodsController
 * @package app\adminapi\controller\distribution
 */
class DistributionGoodsController extends BaseAdminController
{
    /**
     * @notes 查看分销商品列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/23 10:42
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 设置佣金
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/23 16:43
     */
    public function set()
    {
        $params = (new DistributionGoodsValidate())->post()->goCheck('set');
        $result = DistributionGoodsLogic::set($params);
        if($result) {
            return $this->success('设置成功');
        }
        return $this->fail(DistributionGoodsLogic::getError());
    }

    /**
     * @notes 参与/不参与分销
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/23 18:01
     */
    public function join()
    {
        $params = (new DistributionGoodsValidate())->post()->goCheck('join');
        $result = DistributionGoodsLogic::join($params);
        if($result) {
            return $this->success('设置成功', [], 1, 1);
        }
        return $this->fail(DistributionGoodsLogic::getError());
    }

    /**
     * @notes 查看分销商品详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/23 18:16
     */
    public function detail()
    {
        $params = (new DistributionGoodsValidate())->goCheck('detail');
        $result = DistributionGoodsLogic::detail($params);
        return $this->data($result);
    }
}
