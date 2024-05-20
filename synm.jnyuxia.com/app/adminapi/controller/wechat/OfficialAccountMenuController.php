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
use app\adminapi\logic\wechat\OfficialAccountMenuLogic;
use app\adminapi\validate\wechat\OfficialAccountMenuValidate;

/**
 * 微信公众号菜单控制器
 * Class OfficialAccountMenuController
 * @package app\adminapi\controller\wechat
 */
class OfficialAccountMenuController extends BaseAdminController
{
    /**
     * @notes 保存
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 10:48
     */
    public function save()
    {
        $params = (new OfficialAccountMenuValidate())->post()->goCheck();
        $result = OfficialAccountMenuLogic::save($params);
        if($result) {
            return $this->success('保存成功');
        }
        return $this->fail(OfficialAccountMenuLogic::getError());
    }

    /**
     * @notes 保存发布菜单
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 10:49
     */
    public function saveAndPublish()
    {
        $params = (new OfficialAccountMenuValidate())->post()->goCheck();
        $result = OfficialAccountMenuLogic::saveAndPublish($params);
        if($result) {
            return $this->success('保存并发布成功');
        }
        return $this->fail(OfficialAccountMenuLogic::getError());
    }

    /**
     * @notes 查看菜单详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 18:41
     */
    public function detail()
    {
        $result = OfficialAccountMenuLogic::detail();
        return $this->data($result);
    }
}
