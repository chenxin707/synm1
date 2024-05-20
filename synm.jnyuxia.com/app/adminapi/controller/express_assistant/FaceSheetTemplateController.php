<?php
namespace app\adminapi\controller\express_assistant;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\express_assistant\FaceSheetTemplateList;
use app\adminapi\logic\express_assistant\FaceSheetTemplateLogic;
use app\adminapi\validate\express_assistant\FaceSheetTemplateValidate;
use app\common\service\JsonService;

/**
 * 电子面单模板
 */
class FaceSheetTemplateController extends BaseAdminController
{
    /**
     * @notes 获取电子面单支付方式
     * @author Tab
     * @date 2021/11/22 14:33
     */
    public function getFaceSheetPayment()
    {
        $result = FaceSheetTemplateLogic::getFaceSheetPayment();
        return JsonService::data($result);
    }

    /**
     * @notes 添加模板
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 14:19
     */
    public function add()
    {
        $params = (new FaceSheetTemplateValidate())->post()->goCheck('add');
        $result = FaceSheetTemplateLogic::add($params);
        if ($result) {
            return JsonService::success('添加成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetTemplateLogic::getError());
    }

    /**
     * @notes 获取模板详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 14:57
     */
    public function detail()
    {
        $params = (new FaceSheetTemplateValidate())->goCheck('detail');
        $result = FaceSheetTemplateLogic::detail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 编辑模板
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 15:04
     */
    public function edit()
    {
        $params = (new FaceSheetTemplateValidate())->post()->goCheck('edit');
        $result = FaceSheetTemplateLogic::edit($params);
        if ($result) {
            return JsonService::success('编辑成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetTemplateLogic::getError());
    }

    /**
     * @notes 删除模板
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 15:11
     */
    public function delete()
    {
        $params = (new FaceSheetTemplateValidate())->post()->goCheck('delete');
        $result = FaceSheetTemplateLogic::delete($params);
        if ($result) {
            return JsonService::success('删除成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetTemplateLogic::getError());
    }

    /**
     * @notes 模板列表
     * @author Tab
     * @date 2021/11/22 15:19
     */
    public function lists()
    {
        return JsonService::dataLists(new FaceSheetTemplateList());
    }
}