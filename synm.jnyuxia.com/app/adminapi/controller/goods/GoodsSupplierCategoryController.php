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
use app\adminapi\lists\goods\GoodsSupplierCategoryLists;
use app\adminapi\logic\goods\GoodsSupplierCategoryLogic;
use app\adminapi\validate\goods\GoodsSupplierCategoryValidate;

class GoodsSupplierCategoryController extends BaseAdminController
{
    /**
     * @notes 添加供应商分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/16 5:25
     */
    public function add()
    {
        $params = (new GoodsSupplierCategoryValidate())->post()->gocheck('add');
        (new GoodsSupplierCategoryLogic())->add($params);
        return $this->success('供应商分类添加成功',[],1,1);
    }

    /**
     * @notes 查看供应商分类列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/16 6:16
     */
    public function lists()
    {
        return $this->dataLists(new GoodsSupplierCategoryLists());
    }

    /**
     * @notes 删除供应商分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/16 6:57
     */
    public function del()
    {
        $params = (new GoodsSupplierCategoryValidate())->post()->gocheck('del');
        (new GoodsSupplierCategoryLogic())->del($params);
        return $this->success('供应商分类删除成功',[],1,1);
    }

    /**
     * @notes 编辑供应商分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/16 7:18
     */
    public function edit()
    {
        $params = (new GoodsSupplierCategoryValidate())->post()->goCheck('edit');
        (new GoodsSupplierCategoryLogic())->edit($params);
        return $this->success('供应商分类修改成功',[],1,1);
    }

    /**
     * @notes 查看供应商分类详情
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 4:49 下午
     */
    public function detail()
    {
        $params = (new GoodsSupplierCategoryValidate())->goCheck('detail');
        $result = (new GoodsSupplierCategoryLogic())->detail($params);
        return $this->success('供应商分类详情获取成功',$result,1,0);
    }
}