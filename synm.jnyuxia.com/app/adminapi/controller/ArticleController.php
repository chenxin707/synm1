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

use app\adminapi\validate\ArticleValidate;
use app\adminapi\logic\ArticleLogic;

class ArticleController extends BaseAdminController
{
    /**
     * @notes 查看文章/帮助列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/14 9:33
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 添加文章/帮助
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 15:49
     */
    public function add()
    {
        $params = (new ArticleValidate())->post()->goCheck('add');
        ArticleLogic::add($params);
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 查看文章/帮助详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/13 16:53
     */
    public function detail()
    {
        $params = (new ArticleValidate())->goCheck('detail');
        $result = ArticleLogic::detail($params);
        return $this->data($result);
    }

    /** 编辑文章/帮助
     * @notes
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/14 9:20
     */
    public function edit()
    {
        $params = (new ArticleValidate())->post()->goCheck('edit');
        ArticleLogic::edit($params);
        return $this->success('编辑成功',[],1,1);
    }

    /**
     * @notes 删除文章/帮助
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/14 9:24
     */
    public function delete()
    {
        $params = (new ArticleValidate())->post()->goCheck('delete');
        ArticleLogic::delete($params);
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
        $params = (new ArticleValidate())->post()->goCheck('show');
        ArticleLogic::isShow($params);
        return $this->success('修改成功',[],1,1);
    }
}