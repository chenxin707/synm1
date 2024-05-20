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
use app\adminapi\{
    validate\user\UserLevelValidate,
    logic\user\UserLevelLogic,
    controller\BaseAdminController,
};

/**
 * 会员等级控制器
 * Class UserLevelController
 * @package app\adminapi\controller\user
 */
class UserLevelController extends BaseAdminController
{
    /**
     * @notes 用户等级列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 11:53
     */
    public function lists()
    {
        return $this->dataLists();
    }


    /**
     * @notes 添加会员等级
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 15:09
     */
    public function add()
    {
        $params = (new UserLevelValidate())->post()->goCheck('add');
        (new UserLevelLogic)->add($params);
        return $this->success('添加等级成功',[],1,1);
    }


    /**
     * @notes 获取会员等级
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 15:45
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = (new UserLevelLogic())->detail($id);
        return $this->success('',$detail);
    }

    /**
     * @notes 修改会员等级
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 15:14
     */
    public function edit()
    {
        $params = (new UserLevelValidate())->post()->goCheck();
        (new UserLevelLogic)->edit($params);
        return $this->success('修改等级成功',[],1,1);
    }

    /**
     * @notes 删除会员等级
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/29 10:19
     */
    public function del(){
        $params = (new UserLevelValidate())->post()->goCheck('del');
        (new UserLevelLogic())->del($params['id']);
        return $this->success('删除成功');
    }


}