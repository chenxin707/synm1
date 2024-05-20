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
use app\adminapi\lists\goods\GoodsSupplierLists;
use app\adminapi\logic\goods\GoodsSupplierLogic;
use app\adminapi\validate\goods\GoodsSupplierValidate;

class GoodsSupplierController extends BaseAdminController
{
    /**
     * @notes 添加供应商
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/17 11:46
     */
    public function add()
    {
        $params = (new GoodsSupplierValidate())->post()->goCheck('add');
        (new GoodsSupplierLogic())->add($params);
        return $this->success('供应商添加成功',[],1,1);
    }

    /**
     * @notes 查看供应商列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/17 2:11
     */
    public function lists()
    {
        return $this->dataLists(new GoodsSupplierLists());
    }

    /**
     * @notes 删除供应商
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/17 2:46
     */
    public function del()
    {
        $params = (new GoodsSupplierValidate())->post()->goCheck('del');
        (new GoodsSupplierLogic())->del($params);
        return $this->success('供应商删除成功',[],1,1);
    }

    /**
     * @notes 编辑供应商
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/17 3:18 下午
     */
    public function edit()
    {
        $params = (new GoodsSupplierValidate())->post()->goCheck('edit');
        (new GoodsSupplierLogic())->edit($params);
        return $this->success('供应商修改成功',[],1,1);
    }

    /**
     * @notes 查看供应商详情
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 4:56 下午
     */
    public function detail()
    {
        $params = (new GoodsSupplierValidate())->goCheck('detail');
        $result = (new GoodsSupplierLogic())->detail($params);
        return $this->success('供应商详情获取成功',$result,1,0);
    }
}