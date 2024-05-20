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

namespace app\adminapi\controller\marketing;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\marketing\TeamLists;
use app\adminapi\lists\marketing\TeamRecordLists;
use app\adminapi\logic\marketing\TeamLogic;
use app\adminapi\validate\marketing\TeamValidate;

class TeamController extends BaseAdminController
{
    /**
     * @notes 拼团活动列表
     * @author 张无忌
     * @date 2021/8/2 11:54
     */
    public function lists()
    {

        return $this->dataLists((new TeamLists()));
    }

    /**
     * @notes 拼团概况
     * @author 张无忌
     * @date 2021/8/2 19:09
     */
    public function survey()
    {

        $result = TeamLogic::survey();
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 拼团详细信息
     * @author 张无忌
     * @date 2021/8/2 19:10
     */
    public function detail()
    {

        $params = (new TeamValidate())->goCheck('id');
        $result = TeamLogic::detail($params);
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 拼团数据信息
     * @author 张无忌
     * @date 2021/8/2 19:10
     */
    public function info()
    {

        $params = (new TeamValidate())->goCheck('id');
        $result = TeamLogic::info($params);
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 新增拼团活动
     * @author 张无忌
     * @date 2021/8/2 11:54
     */
    public function add()
    {

        $params = (new TeamValidate())->post()->goCheck('add');
        $result = TeamLogic::add($params);
        if ($result === true) {
            return $this->success('添加成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 编辑拼团活动
     * @author 张无忌
     * @date 2021/8/2 11:54
     */
    public function edit()
    {

        $params = (new TeamValidate())->post()->goCheck();
        $result = TeamLogic::edit($params);
        if ($result === true) {
            return $this->success('编辑成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 删除拼团活动
     * @author 张无忌
     * @date 2021/8/2 11:55
     */
    public function delete()
    {

        $params = (new TeamValidate())->post()->goCheck('id');
        $result = TeamLogic::delete($params);
        if ($result === true) {
            return $this->success('删除成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 启动拼团活动
     * @author 张无忌
     * @date 2021/8/2 20:43
     */
    public function open()
    {

        $params = (new TeamValidate())->post()->goCheck('id');
        TeamLogic::open($params);
        return $this->success('启动成功');
    }

    /**
     * @notes 停止拼团活动
     * @author 张无忌
     * @date 2021/8/2 20:43
     */
    public function stop()
    {
        $params = (new TeamValidate())->post()->goCheck('id');
        $result = TeamLogic::stop($params);
        if ($result === true) {
            return $this->success('结束成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 拼团记录
     * @author suny
     * @date 2021/9/23 3:29 下午
     */
    public function record()
    {

        return $this->dataLists(new TeamRecordLists());
    }

    /**
     * @notes 结束拼团记录
     * @author suny
     * @date 2021/9/30 11:30 上午
     */
    public function cancel()
    {
        $params = (new TeamValidate())->post()->goCheck('id');
        TeamLogic::cancel($params);
        return $this->success('结束成功');
    }
}