<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\enum;


class SetMealLogEnum
{
    //操作人类型
    const TYPE_SYSTEM   = 1;//系统
    const TYPE_PLATFORM = 2;//平台
    const TYPE_SHOP     = 3;//商户


    //套餐操作
    const PLATFORM_OPEN_SHOP  = 101;//后台开通商城
    const PLATFORM_ADJUST     = 201;//后台调整
    const SHOP_RENEW          = 801;//商户续费

    // 套餐时长
    const MONTH = 1;
    const YEAR = 2;
    const FOREVER= 3;

    /**
     * @notes 套餐操作日志
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/3/7 9:52 上午
     */
    public static function getRecordDesc($value = true)
    {
        $desc = [
            self::PLATFORM_OPEN_SHOP   => '后台开通商城',
            self::PLATFORM_ADJUST   => '后台调整',
            self::SHOP_RENEW   => '商户续费',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }

    /**
     * @notes 套餐时长
     */
    public static function getTimeTypeDesc($value = true)
    {
        $desc = [
            self::MONTH   => '月',
            self::YEAR   => '年',
            self::FOREVER   => '永久',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }
}
