<?php
namespace app\adminapi\logic\express_assistant;

use app\common\enum\DeliveryEnum;
use app\common\logic\BaseLogic;
use app\common\model\FaceSheetTemplate;

class FaceSheetTemplateLogic extends BaseLogic
{
    /**
     * @notes 获取电子面单支付方式
     * @return string|string[]
     * @author Tab
     * @date 2021/11/22 14:34
     */
    public static function getFaceSheetPayment()
    {
        $data = [];
        $faceSheetPayments =  DeliveryEnum::getFaceSheetPaymentDesc();
        foreach ($faceSheetPayments as $key => $value) {
            $temp['key'] = $key;
            $temp['value'] = $value;
            $data[] = $temp;
        }
        return $data;
    }

    /**
     * @notes 添加模板
     * @param $params
     * @author Tab
     * @date 2021/11/22 14:20
     */
    public static function add($params)
    {
        try {
            $params['partner_id'] = $params['partner_id'] ?? '';
            $params['partner_key'] = $params['partner_key'] ?? '';
            FaceSheetTemplate::duokaiCreate($params);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 获取模板详情
     * @param $params
     * @author Tab
     * @date 2021/11/22 15:00
     */
    public static function detail($params)
    {
        $template =  FaceSheetTemplate::withoutField('create_time,update_time,delete_time')->findOrEmpty($params['id'])->toArray();
        $template['pay_type'] = intval($template['pay_type']);
        return $template;
    }

    /**
     * @notes 编辑模板
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/22 15:06
     */
    public static function edit($params)
    {
        try {
            $params['partner_id'] = $params['partner_id'] ?? '';
            $params['partner_key'] = $params['partner_key'] ?? '';
            FaceSheetTemplate::duokaiUpdate($params, [], [], '', false);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除模板
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/22 15:17
     */
    public static function delete($params)
    {
        try {
            FaceSheetTemplate::duokaiDestroy($params['id']);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}