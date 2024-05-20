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

namespace app\adminapi\controller\wechat;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\wechat\OfficialAccountReplyLogic;
use app\adminapi\validate\wechat\OfficialAccountReplyValidate;
use app\common\enum\OfficialAccountEnum;

/**
 * 微信公众号回复控制器
 * Class OfficialAccountReplyController
 * @package app\adminapi\controller\wechat
 */
class OfficialAccountReplyController extends BaseAdminController
{

    public array $notNeedLogin = ['index'];

    /**
     * @notes 查看回复列表(关注/关键词/默认)
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 17:48
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 添加回复(关注/关键词/默认)
     * @author Tab
     * @date 2021/7/29 15:55
     */
    public function add()
    {
        $params = (new OfficialAccountReplyValidate())->post()->goCheck('add');
        $result = OfficialAccountReplyLogic::add($params);
        if ($result) {
            return $this->success('新增回复成功', [], 1, 1);
        }
        return $this->fail(OfficialAccountReplyLogic::getError());
    }

    /**
     * @notes 查看回复详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 16:53
     */
    public function detail()
    {
        $params = (new OfficialAccountReplyValidate())->goCheck('detail');
        $result = OfficialAccountReplyLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 编辑回复(关注/关键词/默认)
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 17:27
     */
    public function edit()
    {
        $params = (new OfficialAccountReplyValidate())->post()->goCheck('edit');
        $result = OfficialAccountReplyLogic::edit($params);
        if ($result) {
            return $this->success('编辑回复成功', [], 1, 1);
        }
        return $this->fail(OfficialAccountReplyLogic::getError());

    }

    /**
     * @notes 删除回复(关注/关键词/默认)
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 17:44
     */
    public function delete()
    {
        $params = (new OfficialAccountReplyValidate())->post()->goCheck('delete');
        OfficialAccountReplyLogic::delete($params);
        return $this->success('删除回复成功', [], 1, 1);
    }

    /**
     * @notes 更新排序
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 18:11
     */
    public function sort()
    {
        $params = (new OfficialAccountReplyValidate())->post()->goCheck('sort');
        OfficialAccountReplyLogic::sort($params);
        return $this->success('修改成功');
    }

    /**
     * @notes 更新状态
     * @author Tab
     * @date 2021/9/9 18:27
     */
    public function status()
    {
        $params = (new OfficialAccountReplyValidate())->post()->goCheck('status');
        OfficialAccountReplyLogic::status($params);
        return $this->success('修改成功', [], 1, 1);
    }

    /**
     * @notes 微信公众号回调
     * @author Tab
     * @date 2021/7/30 11:58
     */
    public function index()
    {
        OfficialAccountReplyLogic::index();
    }
}