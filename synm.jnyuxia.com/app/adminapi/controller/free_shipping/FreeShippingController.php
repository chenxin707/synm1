<?php
namespace app\adminapi\controller\free_shipping;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\free_shipping\FreeShippingLists;
use app\adminapi\logic\free_shipping\FreeShippingLogic;
use app\adminapi\validate\free_shipping\FreeShippingValidate;
use app\common\service\JsonService;

/**
 * 包邮活动
 */
class FreeShippingController extends BaseAdminController
{
    /**
     * @notes 添加包邮活动
     */
    public function add()
    {
        $params = (new FreeShippingValidate())->post()->goCheck('add');
        $result = FreeShippingLogic::add($params);
        if ($result) {
            return JsonService::success('添加成功');
        }
        return JsonService::fail(FreeShippingLogic::getError());
    }

    /**
     * @notes 包邮活动详情
     */
    public function detail()
    {
        $params = (new FreeShippingValidate())->goCheck('detail');
        $result = FreeShippingLogic::detail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 编辑包邮活动
     */
    public function edit()
    {
        $params = (new FreeShippingValidate())->post()->goCheck('edit');
        $result = FreeShippingLogic::edit($params);
        if ($result) {
            return JsonService::success('编辑成功');
        }
        return JsonService::fail(FreeShippingLogic::getError());
    }

    /**
     * @notes 开始包邮活动
     */
    public function start()
    {
        $params = (new FreeShippingValidate())->post()->goCheck('start');
        $result = FreeShippingLogic::start($params);
        if ($result) {
            return JsonService::success('操作成功');
        }
        return JsonService::fail(FreeShippingLogic::getError());
    }

    /**
     * @notes 结束包邮活动
     */
    public function end()
    {
        $params = (new FreeShippingValidate())->post()->goCheck('end');
        $result = FreeShippingLogic::end($params);
        if ($result) {
            return JsonService::success('操作成功');
        }
        return JsonService::fail(FreeShippingLogic::getError());
    }

    /**
     * @notes 删除包邮活动
     */
    public function delete()
    {
        $params = (new FreeShippingValidate())->post()->goCheck('delete');
        $result = FreeShippingLogic::delete($params);
        if ($result) {
            return JsonService::success('删除成功');
        }
        return JsonService::fail(FreeShippingLogic::getError());
    }

    /**
     * @notes 包邮活动列表
     */
    public function lists()
    {
        return JsonService::dataLists(new FreeShippingLists());
    }
}
