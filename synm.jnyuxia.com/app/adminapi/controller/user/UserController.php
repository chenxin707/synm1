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
namespace app\adminapi\controller\user;

use app\adminapi\{lists\user\SelectUserLists,
    lists\user\UserFansLists,
    lists\user\UserInviterLists,
    logic\user\UserLogic,
    validate\user\UserValidate,
    validate\user\adjustUserWallet,
    controller\BaseAdminController};

/**
 * 用户控制器
 * Class UserController
 * @package app\adminapi\controller\user
 */
class UserController extends BaseAdminController
{

    /**
     * @notes 用户概况
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/17 14:59
     */
    public function index()
    {
        $data = (new UserLogic)->index();
        return $this->success('', $data);

    }


    /**
     * @notes 用户列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/10 16:49
     */
    public function lists()
    {
        return $this->dataLists();
    }


    /**
     * @notes 用户搜索条件列表
     * @author cjhao
     * @date 2021/8/10 16:49
     */
    public function otherList()
    {
        return $this->success('', (new UserLogic())->otherLists());
    }


    /**
     * @notes 批量设置标签
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/16 15:54
     */
    public function setLabel()
    {
        $params = (new UserValidate())->post()->goCheck('setLabel');
        (new UserLogic)->setLabel($params);
        return $this->success('设置成功', [], 1, 1);
    }


    /**
     * @notes 用户详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/18 16:27
     */
    public function detail()
    {
        $params = (new UserValidate())->goCheck('detail');
        $detail = (new UserLogic)->detail($params['user_id']);
        return $this->success('', $detail);

    }


    /**
     * @notes 设置用户信息
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/18 18:07
     */
    public function setInfo()
    {

        $params = (new UserValidate())->post()->goCheck('setInfo');
        (new UserLogic)->setUserInfo($params);
        return $this->success('更新成功', [], 1, 1);
    }

    /**
     * @notes 设置用户标签
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/19 11:14
     */
    public function setUserLabel()
    {
        $params = (new UserValidate())->post()->goCheck('setUserLabel');
        (new UserLogic)->setUserLabel($params);
        return $this->success('更新成功', [], 1, 1);
    }


    /**
     * @notes 调整用户钱包
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/10 18:15
     */
    public function adjustUserWallet()
    {
        $params = (new adjustUserWallet())->post()->goCheck();
        $res = (new UserLogic)->adjustUserWallet($params);
        if(true === $res){
            return $this->success('调整成功', [], 1, 1);
        }
        return $this->fail($res);
    }

    /**
     * @notes 粉丝列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/9/11 10:38
     */
    public function getFans()
    {
        (new UserValidate())->goCheck('fans');
        return $this->dataLists(new UserFansLists());
    }

    /**
     * @notes 用户信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/13 19:32
     */
    public function info()
    {
        $params = (new UserValidate())->goCheck('info');
        $data = UserLogic::info($params);
        return $this->data($data);
    }

    /**
     * @notes 我邀请的人
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 9:40
     */
    public function userInviterLists()
    {
        $params = (new UserValidate())->goCheck('userInviterLists');
        return $this->dataLists(new UserInviterLists());
    }

    /**
     * @notes 上级分销商调整信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 10:50
     */
    public function adjustFirstLeaderInfo()
    {
        $params = (new UserValidate())->goCheck('adjustFirstLeaderInfo');
        $result = UserLogic::adjustFirstLeaderInfo($params);
        return $this->data($result);
    }

    /**
     * @notes 选择用户列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 10:55
     */
    public function selectUserLists()
    {
        return $this->dataLists(new SelectUserLists());
    }

    /**
     * @notes 调整上级分销商
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/14 11:42
     */
    public function adjustFirstLeader()
    {
        $params = (new UserValidate())->post()->goCheck('adjustFirstLeader');
        $result = UserLogic::adjustFirstLeader($params);
        if ($result) {
            return $this->success('调整成功', [], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }
}