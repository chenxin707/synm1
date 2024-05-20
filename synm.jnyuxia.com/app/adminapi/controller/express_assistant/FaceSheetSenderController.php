<?php
namespace app\adminapi\controller\express_assistant;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\express_assistant\FaceSheetSenderList;
use app\adminapi\logic\express_assistant\FaceSheetSenderLogic;
use app\adminapi\validate\express_assistant\FaceSheetSenderValidate;
use app\common\service\JsonService;

/**
 * 发件人模板
 */
class FaceSheetSenderController extends BaseAdminController
{
    /**
     * @notes 添加发件人
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 15:49
     */
    public function add()
    {
        $params = (new FaceSheetSenderValidate())->post()->goCheck('add');
        $result = FaceSheetSenderLogic::add($params);
        if ($result) {
            return JsonService::success('添加成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetSenderLogic::getError());
    }

    /**
     * @notes 获取发件人详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 16:07
     */
    public function detail()
    {
        $params = (new FaceSheetSenderValidate())->goCheck('detail');
        $result = FaceSheetSenderLogic::detail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 编辑发件人
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 16:13
     */
    public function edit()
    {
        $params = (new FaceSheetSenderValidate())->post()->goCheck('edit');
        $result = FaceSheetSenderLogic::edit($params);
        if ($result) {
            return JsonService::success('编辑成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetSenderLogic::getError());
    }

    /**
     * @notes 删除发件人
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 16:18
     */
    public function delete()
    {
        $params = (new FaceSheetSenderValidate())->post()->goCheck('delete');
        $result = FaceSheetSenderLogic::delete($params);
        if ($result) {
            return JsonService::success('删除成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetSenderLogic::getError());
    }

    /**
     * @notes 发件人列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 16:21
     */
    public function lists()
    {
        return JsonService::dataLists(new FaceSheetSenderList());
    }
}