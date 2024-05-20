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

namespace app\adminapi\controller\sign;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\sign\SignLogic;
use app\adminapi\validate\sign\SignValidate;

/**
 * 签到控制器
 * Class SignController
 * @package app\adminapi\controller\sign
 */
class SignController extends BaseAdminController
{
    /**
     * @notes 获取签到设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/14 19:05
     */
    public function getConfig()
    {
        $result = SignLogic::getConfig();
        return $this->data($result);
    }

    /**
     * @notes 设置签到规则
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 9:53
     */
    public function setConfig()
    {
        $params = (new SignValidate())->post()->goCheck('setConfig');
        $result = SignLogic::setConfig($params);
        if($result) {
            return $this->success('保存成功', [], 1, 1);
        }
        return $this->fail(SignLogic::getError());
    }

    /**
     * @notes 添加连续签到规则
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 15:20
     */
    public function add()
    {
        $params = (new SignValidate())->post()->goCheck('add');
        $result = SignLogic::add($params);
        if($result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(SignLogic::getError());
    }

    /**
     * @notes 编辑连续签到规则
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 15:42
     */
    public function edit()
    {
        $params = (new SignValidate())->post()->goCheck('edit');
        $result = SignLogic::edit($params);
        if($result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(SignLogic::getError());
    }

    /**
     * @notes 删除连续签到规则
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 15:49
     */
    public function delete()
    {
        $params = (new SignValidate())->post()->goCheck('delete');
        $result = SignLogic::delete($params);
        if($result) {
            return $this->success('删除成功', [], 1, 1);
        }
        return $this->fail(SignLogic::getError());
    }

    /**
     * @notes 查看连续签到规则详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 11:35
     */
    public function detail()
    {
        $params = (new SignValidate())->goCheck('detail');
        $result = SignLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 重置说明
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 19:21
     */
    public function resetRemark()
    {
        if(!$this->request->isPost()) {
            return $this->fail('请求方式错误');
        }
        SignLogic::resetRemark();
        return $this->success('重置成功', [], 1, 1);
    }

    /**
     * @notes 查看签到记录
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 19:30
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 签到数据中心
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 19:53
     */
    public function dataCenter()
    {
        $result = SignLogic::dataCenter();
        return $this->data($result);
    }
}