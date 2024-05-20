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
use app\adminapi\lists\distribution\FansLists;
use app\adminapi\logic\distribution\DistributionMemberLogic;
use app\adminapi\validate\distribution\DistributionMemberValidate;
use app\common\model\Distribution;
use app\common\model\DistributionLevel;
use app\common\model\User;

/**
 * 分销会员控制器
 * Class DistributionMemberController
 * @package app\adminapi\controller\distribution
 */
class DistributionMemberController extends BaseAdminController
{
    /**
     * @notes 查看分销会员列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/27 16:27
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 开通分销
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/27 17:37
     */
    public function open()
    {
        $params = (new DistributionMemberValidate())->post()->goCheck('open');
        $result = DistributionMemberLogic::open($params);
        if ($result) {
            return $this->success('开通成功', [], 1, 1);
        }
        return $this->fail(DistributionMemberLogic::getError());
    }

    /**
     * @notes 查看分销商详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 16:53
     */
    public function detail()
    {
        $params = (new DistributionMemberValidate())->goCheck('detail');
        $result = DistributionMemberLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 调整分销等级界面信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 18:39
     */
    public function adjustLevelInfo()
    {
        $params = (new DistributionMemberValidate())->goCheck('adjustLevelInfo');
        $result = DistributionMemberLogic::adjustLevelInfo($params);
        return $this->data($result);
    }

    /**
     * @notes 调整分销商等级
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 18:55
     */
    public function adjustLevel()
    {
        $params = (new DistributionMemberValidate())->post()->goCheck('adjustLevel');
        $result = DistributionMemberLogic::adjustLevel($params);
        if ($result) {
            return $this->success('调整成功', [], 1, 1);
        }
        return $this->fail(DistributionMemberLogic::getError());
    }

    /**
     * @notes 冻结/解冻资格
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 19:09
     */
    public function freeze()
    {
        $params = (new DistributionMemberValidate())->post()->goCheck('freeze');
        $result = DistributionMemberLogic::freeze($params);
        if ($result) {
            return $this->success('操作成功', [], 1, 1);
        }
        return $this->fail(DistributionMemberLogic::getError());
    }

    /**
     * @notes 查看下级
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 19:22
     */
    public function getFans()
    {
        $params = (new DistributionMemberValidate())->goCheck('fans');
        $result = DistributionMemberLogic::getFans($params);
        return $this->data($result);
    }

    /**
     * @notes 下级列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 19:52
     */
    public function getFansLists()
    {
        $params = (new DistributionMemberValidate())->goCheck('fansLists');
        return $this->dataLists(new FansLists());
    }

    /**
     * @notes 分销初始化数据
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/6 14:26
     */
    public function updateTable()
    {
        try {
            $defaultLevel = DistributionLevel::where('is_default', 1)->findOrEmpty()->toArray();
            if (empty($defaultLevel)) {
                // 没有默认等级，初始化
                DistributionLevel::duokaiCreate([
                    'name' => '默认等级',
                    'weights' => '1',
                    'is_default' => '1',
                    'remark' => '默认等级',
                    'update_relation' => '1'
                ]);
            }
            // 默认分销会员等级
            $defaultLevelId = DistributionLevel::where('is_default', 1)->value('id');
            // 生成分销基础信息表
            $users = User::field('id')->select()->toArray();
            $distribution = Distribution::column('user_id');
            $addData = [];
            foreach($users as $item) {
                if (in_array($item['id'], $distribution)) {
                    // 已有基础分销记录，跳过
                    continue;
                }
                $data = [
                    'user_id' => $item['id'],
                    'level_id' => $defaultLevelId,
                    'is_distribution' => 0,
                    'is_freeze' => 0,
                    'remark' => '',
                ];
                $addData[] = $data;
            }
            $distributionModel = new Distribution();
            $distributionModel->duokaiSaveAll($addData);

            return $this->success('初始化完成');
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}