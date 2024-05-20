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

namespace app\adminapi\controller;

use app\adminapi\validate\ArticleCategoryValidate;
use app\adminapi\logic\ArticleCategoryLogic;

class ArticleCategoryController extends BaseAdminController
{
    /**
     * @notes 查看文章/帮助分类列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 14:05
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 添加文章/帮助分类
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 14:06
     */
    public function add()
    {
        $params = (new ArticleCategoryValidate())->post()->goCheck('add');
        ArticleCategoryLogic::add($params);
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 查看文章/帮助详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 14:07
     */
    public function detail()
    {
        $params = (new ArticleCategoryValidate())->goCheck('detail');
        $result = ArticleCategoryLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 编辑文章/帮助分类
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 14:11
     */
    public function edit()
    {
        $params = (new ArticleCategoryValidate())->post()->goCheck('edit');
        ArticleCategoryLogic::edit($params);
        return $this->success('编辑成功',[],1,1);
    }

    /**
     * @notes 删除文章/帮助分类
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 14:20
     */
    public function delete()
    {
        $params = (new ArticleCategoryValidate())->post()->goCheck('delete');
        ArticleCategoryLogic::delete($params);
        return $this->success('删除成功',[],1,1);
    }
    /**
     * @notes 修改是否显示状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/14 11:36
     */
    public function isShow()
    {
        $params = (new ArticleCategoryValidate())->post()->goCheck('show');
        ArticleCategoryLogic::isShow($params);
        return $this->success('修改成功',[],1,1);
    }
}