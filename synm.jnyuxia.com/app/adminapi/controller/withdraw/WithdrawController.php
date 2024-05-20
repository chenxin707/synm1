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

namespace app\adminapi\controller\withdraw;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\withdraw\WithdrawLogic;
use app\adminapi\validate\withdraw\WithdrawValidate;

/**
 * 提现控制器
 * Class WithdrawController
 * @package app\adminapi\controller
 */
class WithdrawController extends BaseAdminController
{
    /**
     * @notes 查看提现列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/6 19:34
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 查看提现详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/6 20:32
     */
    public function detail()
    {
        $params = (new WithdrawValidate())->goCheck();
        $result = WithdrawLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 审核拒绝
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/9 9:58
     */
    public function refuse()
    {
        $params = (new WithdrawValidate())->post()->goCheck();
        $result = WithdrawLogic::refuse($params);
        if($result) {
            return $this->success('审核拒绝');
        }
        return $this->fail(WithdrawLogic::getError());
    }

    /**
     * @notes 审核通过
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/9 10:13
     */
    public function pass()
    {
        $params = (new WithdrawValidate())->post()->goCheck();
        $result = WithdrawLogic::pass($params);
        if($result) {
            return $this->success('审核通过');
        }
        return $this->fail(WithdrawLogic::getError());
    }

    /**
     * @notes 查询结果(提现至微信零钱)
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/9 14:09
     */
    public function search()
    {
        $params = (new WithdrawValidate())->goCheck();
        $result = WithdrawLogic::search($params);
        if($result === false) {
            return $this->fail(WithdrawLogic::getError());
        }
        return $this->success($result);
    }

    /**
     * @notes 转账成功
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/9 15:32
     */
    public function transferSuccess()
    {
        $params = (new WithdrawValidate())->post()->goCheck();
        $result = WithdrawLogic::transferSuccess($params);
        if($result) {
            return $this->success('转账成功');
        }
        return $this->fail(WithdrawLogic::getError());
    }

    /**
     * @notes 转账失败
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/9 16:02
     */
    public function transferFail()
    {
        $params = (new WithdrawValidate())->post()->goCheck();
        $result = WithdrawLogic::transferFail($params);
        if($result) {
            return $this->success('转账失败');
        }
        return $this->fail(WithdrawLogic::getError());
    }
}
