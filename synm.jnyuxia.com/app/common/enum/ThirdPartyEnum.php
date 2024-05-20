<?php
namespace app\common\enum;

/**
 * 第三方
 */
class ThirdPartyEnum
{
    const KUAIDI100 = 1;

    /**
     * 面单类型
     */
    const FACE_SHEET_TYPE = [
        self::KUAIDI100
    ];

    /**
     * @notes 获取第三方平台描述
     * @param null $thirdParty
     * @param string $lang
     * @return mixed|string|string[]
     * @author Tab
     * @date 2021/11/22 11:30
     */
    public static function getThirdPartyDesc($thirdParty = null, $lang = 'zh')
    {
        $zhDesc = [
            self::KUAIDI100 => '快递100'
        ];
        $enDesc = [
            self::KUAIDI100 => 'kuaidi100'
        ];
        if (is_null($thirdParty) && $lang == 'zh') {
            return $zhDesc;
        }
        if (is_null($thirdParty) && $lang == 'en') {
            return $enDesc;
        }
        if ($lang == 'zh') {
            return $zhDesc[$thirdParty] ??  '';
        }
        if ($lang == 'en') {
            return $enDesc[$thirdParty] ??  '';
        }
    }
}