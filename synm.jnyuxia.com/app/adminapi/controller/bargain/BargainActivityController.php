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

namespace app\adminapi\controller\bargain;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\bargain\ActivityRecordList;
use app\adminapi\lists\bargain\GoodsLists;
use app\adminapi\logic\bargain\BargainActivityLogic;
use app\adminapi\validate\bargain\BargainActivityValidate;
use think\Validate;

/**
 * 砍价活动控制器
 * Class BargainActivityController
 * @package app\adminapi\controller\bargain
 */
class BargainActivityController extends BaseAdminController
{
    /**
     * @notes 选择商品
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/26 15:59
     */
    public function chooseGoods()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('chooseGoods');
        $result = BargainActivityLogic::chooseGoods($params);
        return $this->data($result);
    }

    /**
     * @notes 添加砍价活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 10:49
     */
    public function add()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('add');
        $result = BargainActivityLogic::add($params);
        if ($result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(BargainActivityLogic::getError());
    }

    /**
     * @notes 查看砍价活动详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 11:56
     */
    public function detail()
    {
        $params = (new BargainActivityValidate())->goCheck('detail');
        $result = BargainActivityLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 编辑砍价活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 15:07
     */
    public function edit()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('edit');
        $result = BargainActivityLogic::edit($params);
        if ($result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(BargainActivityLogic::getError());
    }

    /**
     * @notes 确认砍价活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 17:08
     */
    public function confirm()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('confirm');
        $result = BargainActivityLogic::confirm($params);
        if ($result) {
            return $this->success('确认成功', [], 1, 1);
        }
        return $this->fail(BargainActivityLogic::getError());
    }

    /**
     * @notes 结束砍价活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 17:27
     */
    public function stop()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('stop');
        $result = BargainActivityLogic::stop($params);
        if ($result) {
            return $this->success('结束成功', [], 1, 1);
        }
        return $this->fail(BargainActivityLogic::getError());
    }

    /**
     * @notes 删除砍价活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 17:34
     */
    public function delete()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('delete');
        $result = BargainActivityLogic::delete($params);
        if ($result) {
            return $this->success('删除成功', [], 1, 1);
        }
        return $this->fail(BargainActivityLogic::getError());
    }

    /**
     * @notes 砍价活动列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/27 17:49
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 活动数据
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/23 18:53
     */
    public function activityData()
    {
        $params = (new BargainActivityValidate())->goCheck('activityData');
        $result = BargainActivityLogic::activityData($params);
        return $this->data($result);
    }

    /**
     * @notes 活动记录
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/24 15:33
     */
    public function activityRecord()
    {
        return $this->dataLists(new ActivityRecordList());
    }

    /**
     * @notes 结束砍价
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/24 18:13
     */
    public function stopInitiate()
    {
        $params = (new BargainActivityValidate())->post()->goCheck('stopInitiate');
        $result = BargainActivityLogic::stopInitiate($params);
        if ($result) {
            return $this->success('结束砍价成功', [], 1, 1);
        }
        return $this->fail(BargainActivityLogic::getError());
    }

    /**
     * @notes 数据中心
     * @author Tab
     * @date 2021/9/24 18:24
     */
    public function dataCenter()
    {
        $result = BargainActivityLogic::dataCenter();
        return $this->data($result);
    }
}