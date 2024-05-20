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

namespace app\platformapi\controller\shop;

use app\common\service\JsonService;
use app\platformapi\controller\BasePlatformController;
use app\platformapi\logic\shop\ShopDefaultDataLogic;
use app\platformapi\logic\shop\ShopLogic;
use app\platformapi\validate\shop\ShopValidate;

/**
 * 商城管理
 */
class ShopController extends BasePlatformController
{
    /**
     * @notes 查看商城列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/14 16:46
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 添加商城
     * @author Tab
     * @date 2021/12/14 11:31
     */
    public function add()
    {
        $params = (new ShopValidate())->post()->goCheck('add');
        $params['platform_id'] = $this->platformAdminId;
        $result = ShopLogic::add($params);
        if ($result) {
            return JsonService::success('添加成功', [], 1, 1);
        }
        return JsonService::fail(ShopLogic::getError());
    }

    /**
     * @notes 商城详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/15 10:29
     */
    public function detail()
    {
        $params = (new ShopValidate())->goCheck('detail');
        $result = ShopLogic::detail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 商城编辑
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/15 10:34
     */
    public function edit()
    {
        $params = (new ShopValidate())->post()->goCheck('edit');
        $params['platform_id'] = $this->platformAdminId;
        $result = ShopLogic::edit($params);
        if ($result) {
            return JsonService::success('编辑成功', [], 1, 1);
        }
        return JsonService::fail(ShopLogic::getError());
    }

    /**
     * @notes 修改超级管理员
     * @author Tab
     * @date 2021/12/15 10:54
     */
    public function changeSuperAdmin()
    {
        $params = (new ShopValidate())->post()->goCheck('changeSuperAdmin');
        $result = ShopLogic::changeSuperAdmin($params);
        if ($result) {
            return JsonService::success('修改成功', [], 1, 1);
        }
        return JsonService::fail(ShopLogic::getError());
    }

    /**
     * @notes 删除商城
     * @author Tab
     * @date 2021/12/15 11:17
     */
    public function delete()
    {
        $params = (new ShopValidate())->post()->goCheck('delete');
        $result = ShopLogic::delete($params);
        if ($result) {
            return JsonService::success('删除成功', [], 1, 1);
        }
        return JsonService::fail(ShopLogic::getError());
    }

    /**
     * @notes 切换商城状态
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/20 11:36
     */
    public function switchStatus()
    {
        $params = (new ShopValidate())->post()->goCheck('switchStatus');
        $result = ShopLogic::switchStatus($params);
        if ($result) {
            return JsonService::success('切换状态成功', [], 1, 1);
        }
        return JsonService::fail(ShopLogic::getError());
    }


    /**
     * @notes 套餐记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/7 11:09 上午
     */
    public function setMealLogLists()
    {
        $params = (new ShopValidate())->goCheck('setMealLogLists');
        $result = ShopLogic::setMealLogLists($params);
        return JsonService::data($result);
    }

    /**
     * @notes 修改备注
     * @author cjhao
     * @date 2022/3/9 16:05
     */
    public function changeRemark()
    {
        $params = (new ShopValidate())->post()->goCheck('changeRemark');
        ShopLogic::changeRemark($params);
        return JsonService::success('设置成功', [], 1, 1);

    }
}