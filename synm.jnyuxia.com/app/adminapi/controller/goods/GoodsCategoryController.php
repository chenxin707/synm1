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

namespace app\adminapi\controller\goods;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\goods\GoodsCategoryCommonLists;
use app\adminapi\lists\goods\GoodsCategoryLists;
use app\adminapi\logic\goods\GoodsCategoryLogic;
use app\adminapi\validate\goods\GoodsCategoryValidate;

class GoodsCategoryController extends BaseAdminController
{
    public function commonLists()
    {
        return $this->dataLists(new GoodsCategoryCommonLists());
    }

    /**
     * @notes 添加商品分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/17 4:38 下午
     */
    public function add()
    {
        $params = (new GoodsCategoryValidate())->post()->goCheck('add');
        (new GoodsCategoryLogic())->add($params);
        return $this->success('商品分类添加成功',[],1,1);
    }

    /**
     * @notes 查看商品分类列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/17 5:54 下午
     */
    public function lists()
    {
        return $this->dataLists(new GoodsCategoryLists());
    }

    /**
     * @notes 修改商品分类状态
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 11:46 上午
     */
    public function status()
    {
        $params = (new GoodsCategoryValidate())->post()->goCheck('status');
        (new GoodsCategoryLogic())->status($params);
        return $this->success('状态修改成功',[],1,1);
    }

    /**
     * @notes 删除商品分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 2:04 下午
     */
    public function del()
    {
        $params = (new GoodsCategoryValidate())->post()->goCheck('del');
        (new GoodsCategoryLogic())->del($params);
        return $this->success('商品分类删除成功',[],1,1);
    }

    /**
     * @notes 编辑商品分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 4:02 下午
     */
    public function edit()
    {
        $params = (new GoodsCategoryValidate())->post()->goCheck('edit');
        (new GoodsCategoryLogic())->edit($params);
        return $this->success('商品分类修改成功',[],1,1);
    }

    /**
     * @notes 查看商品分类详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 4:36 下午
     */
    public function detail()
    {
        $params = (new GoodsCategoryValidate())->goCheck('detail');
        $result = (new GoodsCategoryLogic())->detail($params);
        return $this->success('商品分类详情获取成功',$result,1,0);
    }
}