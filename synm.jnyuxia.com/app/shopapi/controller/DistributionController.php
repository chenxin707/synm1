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

use app\common\enum\DistributionConfigEnum;
use app\common\model\DistributionConfig;
use app\common\service\FileService;
use app\shopapi\lists\DistributionOrderGoodsLists;
use app\shopapi\lists\FansLists;
use app\shopapi\logic\DistributionLogic;
use app\shopapi\validate\DistributionValidate;

/**
 * 分销控制器
 * Class DistributionController
 * @package app\shopapi\controller
 */
class DistributionController extends BaseShopController
{
    public array $notNeedLogin = ['fixAncestorRelation'];

    /** 填写邀请码
     * @notes
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/16 17:53
     */
    public function code()
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        try {
            validate(DistributionValidate::class)->scene('code')->check($params);
        } catch (\Exception $e) {
            if (isset($params['hide'])) {
                return $this->fail($e->getMessage(), [], 0, 0);
            }
            return $this->fail($e->getMessage());
        }
        $result = DistributionLogic::code($params);
        // 不弹出提示语场景
        if($result && isset($params['hide'])) {
            return $this->success('邀请成功');
        }
        // 弹出提示语场景
        if($result) {
            return $this->success('邀请成功', [], 1, 1);
        }
        // 不弹出提示语场景
        if (isset($params['hide'])) {
            return $this->fail(DistributionLogic::getError(),[], 0, 0);
        }
        // 弹出提示语场景
        return $this->fail(DistributionLogic::getError());
    }

    /**
     * @notes 申请分销
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/17 10:36
     */
    public function apply()
    {
        $params = (new DistributionValidate())->post()->goCheck('apply', ['user_id' => $this->userId]);
        DistributionLogic::apply($params);
        return $this->success('申请成功');
    }

    /**
     * @notes 查看申请详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/17 11:38
     */
    public function applyDetail()
    {
        $result = DistributionLogic::applyDetail($this->userId);
        return $this->data($result);
    }

    /**
     * @notes 查看分销推广主页
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/17 15:40
     */
    public function index()
    {
        $result = DistributionLogic::index($this->userId);
        return $this->data($result);
    }

    /**
     * @notes 查看分销订单列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/17 16:46
     */
    public function order()
    {
        $params = $this->request->get();
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 25;
        $params['user_id'] = $this->userId;
        $result = DistributionLogic::order($params);
        return $this->data($result);
    }

    /**
     * @notes 查看月度账单
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/17 18:45
     */
    public function monthBill()
    {
        $params = $this->request->get();
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 25;
        $params['user_id'] = $this->userId;
        $result = DistributionLogic::monthBill($params);
        return $this->data($result);
    }

    /**
     * @notes 查看月度账单明细
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/19 10:25
     */
    public function monthDetail()
    {
        $params = $this->request->get();
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 25;
        $params['year'] = $params['year'] ?? date('Y');
        $params['month'] = $params['month'] ?? date('m');
        $params['user_id'] = $this->userId;
        $result = DistributionLogic::monthDetail($params);
        return $this->data($result);
    }

    /**
     * @notes 我的粉丝
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/5 17:11
     */
    public function fans()
    {
        return $this->dataLists(new FansLists());
    }

    /**
     * @notes 分销海报
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/6 9:52
     */
    public function poster()
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        $params['terminal'] = $this->userInfo['terminal'];
        $result = DistributionLogic::poster($params);
        if($result === false) {
            return $this->fail(DistributionLogic::getError());
        }
        return $this->data($result);
    }

    /**
     * 修复旧的关系链
     */
    public function fixAncestorRelation()
    {
        $result = DistributionLogic::fixAncestorRelation();
        if ($result) {
            return $this->success('修复成功');
        }
        return $this->fail(DistributionLogic::getError());
    }


    /**
     * @notes 获取分享海报
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/17 18:36
     */
    public function getPoster(){
        $poster = DistributionLogic::getPoster();
        return $this->success('',$poster);
    }
}