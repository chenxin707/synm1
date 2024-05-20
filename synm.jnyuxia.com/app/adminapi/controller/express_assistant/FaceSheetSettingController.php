<?php
namespace app\adminapi\controller\express_assistant;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\express_assistant\FaceSheetSettingLogic;
use app\adminapi\validate\express_assistant\FaceSheetSettingValidate;
use app\common\service\JsonService;

/**
 * 电子面单设置
 */
class FaceSheetSettingController extends BaseAdminController {
    /**
     * @notes 获取面单类型
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 11:43
     */
    public function getFaceSheetType()
    {
        $result = FaceSheetSettingLogic::getFaceSheetType();
        return JsonService::data($result);
    }

    /**
     * @notes 获取电子面单设置
     * @author Tab
     * @date 2021/11/22 11:11
     */
    public function getConfig()
    {
        $result = FaceSheetSettingLogic::getConfig();
        return JsonService::data($result);
    }

    /**
     * @notes 电子面单设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/22 11:56
     */
    public function setConfig()
    {
        $params = (new FaceSheetSettingValidate())->post()->goCheck();
        $result = FaceSheetSettingLogic::setConfig($params);
        if ($result) {
            return JsonService::success('设置成功', [], 1, 1);
        }
        return JsonService::fail(FaceSheetSettingLogic::getError());
    }
}