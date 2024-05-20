<?php
namespace app\adminapi\controller\express_assistant;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\express_assistant\FaceSheetOrderLogic;
use app\adminapi\validate\express_assistant\FaceSheetOrderValidate;
use app\common\service\JsonService;

/**
 * 打印电子面单
 */
class FaceSheetOrderController extends BaseAdminController
{
    /**
     * @notes 打印电子面单
     * @author Tab
     * @date 2021/11/22 16:39
     */
    public function print()
    {
        $params = (new FaceSheetOrderValidate())->post()->goCheck();
        $params['admin_id'] = $this->adminId;
        $result = FaceSheetOrderLogic::print($params);
        if ($result) {
            return JsonService::success('打印完成', ['code' => 1, 'msg' => '打印完成'],1, 0);
        }
        return JsonService::fail(FaceSheetOrderLogic::getError());
    }
}