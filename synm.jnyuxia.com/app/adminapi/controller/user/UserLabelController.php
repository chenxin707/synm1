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
    logic\user\UserLabelLogic,
    controller\BaseAdminController,
    validate\user\UserLableValidate,
};

class UserLabelController extends BaseAdminController
{
    /**
     * @notes 等级标签列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 16:44
     */
    public function lists()
    {
        return $this->dataLists();
    }


    /**
     * @notes 新增用户标签
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 17:48
     */
    public function add()
    {
        $params = (new UserLableValidate())->post()->goCheck('add');
        (new UserLabelLogic)->add($params);
        return $this->success('添加标签成功', [], 1, 1);


    }

    /**
     * @notes 获取用户标签
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 18:05
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = (new UserLabelLogic())->detail($id);
        return $this->success('',$detail);
    }


    /**
     * @notes 编辑用户标签
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 18:06
     */
    public function edit()
    {
        $params = (new UserLableValidate())->post()->goCheck('edit');
        (new UserLabelLogic())->edit($params);
        return $this->success('修改标签成功', [], 1, 1);

    }

    /**
     * @notes 删除标签
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/28 18:38
     */
    public function del(){
        $params = (new UserLableValidate())->post()->goCheck('del');
        (new UserLabelLogic())->del($params['ids']);
        return $this->success('删除成功');
    }
}