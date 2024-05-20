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
use app\adminapi\logic\distribution\DistributionLevelLogic;
use app\adminapi\validate\distribution\DistributionLevelValidate;

/**
 * 分销会员等级控制器
 * Class DistributionLevelController
 * @package app\adminapi\controller\distribution
 */
class DistributionLevelController extends BaseAdminController
{
    /**
     * @notes 查看分销会员等级列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/23 9:24
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 添加分销会员等级
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/22 11:19
     */
    public function add()
    {
        $params = (new DistributionLevelValidate())->post()->goCheck('add');
        $result = DistributionLevelLogic::add($params);
        if($result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(DistributionLevelLogic::getError());
    }

    /**
     * @notes 查看分销会员等级详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/22 14:51
     */
    public function detail()
    {
        $params = (new DistributionLevelValidate())->goCheck('detail');
        $result = DistributionLevelLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 编辑分销会员等级
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/22 16:22
     */
    public function edit()
    {
        $params = (new DistributionLevelValidate())->post()->goCheck('edit');
        $result = DistributionLevelLogic::edit($params);
        if($result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(DistributionLevelLogic::getError());
    }

    /**
     * @notes 删除分销会员等级
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/22 16:29
     */
    public function delete()
    {
        $params = (new DistributionLevelValidate())->post()->goCheck('delete');
        $result = DistributionLevelLogic::delete($params);
        if($result) {
            return $this->success('删除成功', [], 1, 1);
        }
        return $this->fail(DistributionLevelLogic::getError());
    }

    /**
     * @notes 更新分销会员等级
     * @param $userId
     * @author Tab
     * @date 2021/7/27 14:02
     */
    public function updateDistributionLevel($userId)
    {
        DistributionLevelLogic::updateDistributionLevel($userId);
    }
}