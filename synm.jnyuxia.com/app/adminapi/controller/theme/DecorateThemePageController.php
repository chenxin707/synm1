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
namespace app\adminapi\controller\theme;

use app\adminapi\{
    controller\BaseAdminController,
    logic\theme\DecorateThemePageLogic,
    validate\theme\DecoratePageValidate,
};


/**
 * 主题页面控制器
 * Class ThemePageController
 * @package app\adminapi\controller\decorate
 */
class DecorateThemePageController extends BaseAdminController
{

    /**
     * @notes 首页接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/14 15:05
     */
    public function index(){
        $detail = (new DecorateThemePageLogic())->index();
        return $this->success('', $detail);
    }
    /**
     * @notes 主题页面列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/5 10:44
     */
    public function lists()
    {
        return $this->dataLists();
    }


    /**
     * @notes 获取主题页面
     * @author cjhao
     * @date 2021/8/5 10:44
     */
    public function getPage()
    {
        $params = $this->request->get();
        $detail = (new DecorateThemePageLogic())->getPage($params);
        return $this->success('', $detail);
    }




    /**
     * @notes 设置主页
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/6 16:08
     */
    public function setHome()
    {
        $params = (new DecoratePageValidate())->post()->goCheck('setHome');
        (new DecorateThemePageLogic())->setHome($params['id']);
        return $this->success('操作成功', [], 1, 1);
    }


    /**
     * @notes 新增主题页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/5 11:03
     */
    public function add()
    {
        $params = $this->request->post();
        $id = (new DecorateThemePageLogic())->add($params);
        return $this->success('操作成功', ['id' => $id]);
    }

    /**
     * @notes 编辑主题页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/5 11:04
     */
    public function edit()
    {
        $params = (new DecoratePageValidate())->post()->goCheck('edit');
        $data = (new DecorateThemePageLogic())->edit($params);
        return $this->success('操作成功', $data, 1, 1);
    }

    /**
     * @notes 删除主页
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/9 19:31
     */
    public function del()
    {
        $params = (new DecoratePageValidate())->post()->goCheck('del');
        (new DecorateThemePageLogic())->del($params['id']);
        return $this->success('操作成功', [], 1, 1);
    }

    /**
     * @notes 商城页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/12 16:28
     */
    public function getShopPage()
    {
        $type = $this->request->get('type','shop');
        $page = (new DecorateThemePageLogic())->getShopPage($type);
        return $this->success('操作成功',$page);


    }


}
