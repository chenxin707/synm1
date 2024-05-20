<?php
namespace app\adminapi\logic\express_assistant;

use app\common\logic\BaseLogic;
use app\common\model\FaceSheetSender;

class FaceSheetSenderLogic extends BaseLogic
{
    /**
     * @notes 添加发件人
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/22 15:58
     */
    public static function add($params)
    {
        try {
            FaceSheetSender::duokaiCreate($params);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 发件人详情
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/11/22 16:10
     */
    public static function detail($params)
    {
        return FaceSheetSender::withoutField('create_time,update_time,delete_time')->findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes 编辑发件人
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/22 16:14
     */
    public static function edit($params)
    {
        try {
            FaceSheetSender::duokaiUpdate($params, [], [], '', false);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除发件人
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/22 16:19
     */
    public static function delete($params)
    {
        try {
            FaceSheetSender::duokaiDestroy($params['id']);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}