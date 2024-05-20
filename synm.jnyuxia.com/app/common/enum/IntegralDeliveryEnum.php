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


class IntegralDeliveryEnum
{


    /**
     * 配送状态
     * NOT_SHIPPED 未发货
     * SHIPPED 已发货
     */
    const NOT_SHIPPED = 0;
    const SHIPPED = 1;


    /**
     * 发货方式
     * EXPRESS 快递配送
     * NO_EXPRESS 无需快递
     */
    const EXPRESS = 1;//快递配送
    const NO_EXPRESS = 2;//无需快递


    public static function getDeliveryStatusDesc($value = true)
    {
        $data = [
            self::NOT_SHIPPED => '未发货',
            self::SHIPPED => '已发货',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }


    /**
     * @notes 配送方式
     * @param bool $value
     * @return string|string[]
     * @author 段誉
     * @date 2022/4/1 14:31
     */
    public static function getSendTypeDesc($value = true)
    {
        $data = [
            self::EXPRESS => '快递配送',
            self::NO_EXPRESS => '无需快递',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }
}