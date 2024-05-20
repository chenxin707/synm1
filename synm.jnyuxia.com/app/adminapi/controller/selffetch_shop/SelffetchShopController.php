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

namespace app\adminapi\controller\selffetch_shop;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\selffetch_shop\SelffetchShopLists;
use app\adminapi\logic\selffetch_shop\SelffetchShopLogic;
use app\adminapi\validate\selffetch_shop\SelffetchShopValidate;

class SelffetchShopController extends BaseAdminController
{
    /**
     * @notes 添加自提门店
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 3:14 下午
     */
    public function add()
    {
        $params = (new SelffetchShopValidate())->post()->goCheck('add');
        (new SelffetchShopLogic())->add($params);
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 编辑自提门店
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 3:29 下午
     */
    public function edit()
    {
        $params = (new SelffetchShopValidate())->post()->goCheck('edit');
        (new SelffetchShopLogic())->edit($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 查看自提门店详情
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 3:46 下午
     */
    public function detail()
    {
        $params = (new SelffetchShopValidate())->goCheck('detail');
        $result = (new SelffetchShopLogic())->detail($params);
        return $this->success('',$result);
    }

    /**
     * @notes 查看自提门店列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 3:58 下午
     */
    public function lists()
    {
        return $this->dataLists(new SelffetchShopLists());
    }

    /**
     * @notes 修改自提门店状态
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 4:17 下午
     */
    public function status()
    {
        $params = (new SelffetchShopValidate())->post()->goCheck('status');
        (new SelffetchShopLogic())->status($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 删除自提门店
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 5:13 下午
     */
    public function del()
    {
        // TODO 当核销员表用到门店时，不可删除
        $params = (new SelffetchShopValidate())->post()->goCheck('del');
        (new SelffetchShopLogic())->del($params);
        return $this->success('删除成功',[],1,1);
    }

    /**
     * @notes 腾讯地图区域搜索
     * @return \think\response\Json
     * @author ljj
     * @date 2021/9/28 6:43 下午
     */
    public function regionSearch()
    {
        $params = (new SelffetchShopValidate())->goCheck('regionSearch');
        $result = (new SelffetchShopLogic())->regionSearch($params);
        if ($result['status'] !== 0) {
            return $this->fail($result['message']);
        }
        return $this->success('',$result);
    }
}