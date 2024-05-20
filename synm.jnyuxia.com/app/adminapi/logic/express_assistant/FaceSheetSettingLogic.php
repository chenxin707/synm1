<?php
namespace app\adminapi\logic\express_assistant;

use app\common\enum\ThirdPartyEnum;
use app\common\logic\BaseLogic;
use app\common\service\ConfigService;

class FaceSheetSettingLogic extends BaseLogic
{
    /**
     * @notes 获取面单类型
     * @author Tab
     * @date 2021/11/22 11:44
     */
    public static function getFaceSheetType()
    {
        $data = [];
        foreach(ThirdPartyEnum::FACE_SHEET_TYPE as $value) {
            $temp['key'] = $value;
            $temp['value'] = ThirdPartyEnum::getThirdPartyDesc($value);
            $data[] = $temp;
        }
        return  $data;
    }

    /**
     * @notes 获取电子面单设置
     * @author Tab
     * @date 2021/11/22 11:24
     */
    public static function getConfig()
    {
        $config = [
            'type' => ConfigService::get('face_sheet', 'type', ThirdPartyEnum::KUAIDI100),
            'key' => ConfigService::get('kuaidi100', 'key', ''),
            'secret' => ConfigService::get('kuaidi100', 'secret', ''),
            'siid' => ConfigService::get('kuaidi100', 'siid', '')
        ];

        return $config;
    }

    /**
     * @notes 电子面单设置
     * @author Tab
     * @date 2021/11/22 11:57
     */
    public static function setConfig($params)
    {
        try {
            $faceTypeEnName = ThirdPartyEnum::getThirdPartyDesc($params['type'], 'en');
            ConfigService::set('face_sheet', 'type', $params['type']);
            ConfigService::set($faceTypeEnName, 'key', $params['key']);
            ConfigService::set($faceTypeEnName, 'secret', $params['secret']);
            ConfigService::set($faceTypeEnName, 'siid', $params['siid']);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}