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


namespace app\common\enum;


/**
 * 运费
 * Class FreightEnum
 * @package app\common\enum
 */
class FreightEnum
{
    //计费方式
    const CHARGE_WAY_PIECE = 1;//按件计费
    const CHARGE_WAY_WEIGHT = 2;//按重量计费
    const CHARGE_WAY_VOLUME = 3; //体积计费

    /**
     * @notes 计算方式
     * @param bool $type
     * @return array|mixed|string
     * @author 段誉
     * @date 2021/7/31 17:08
     */
    public static function getChargeWay($type = true)
    {
        $data = [
            self::CHARGE_WAY_PIECE  => '按件计费',
            self::CHARGE_WAY_WEIGHT => '按重量计费',
            self::CHARGE_WAY_VOLUME => '按体积计费',
        ];
        if (true === $type) {
            return $data;
        }
        return $data[$type] ?? '未知';
    }

}