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
 *  包邮活动
 */
class FreeShippingEnum
{
    const WAIT = 0;
    const ING = 1;
    const END = 2;
    const ALL_USER = 1;
    const ALL_GOODS = 1;
    const BY_AMOUNT = 1;
    const BY_QUANTITY = 2;

    /**
     * @notes 获取状态描述
     */
    public static function getStatusDesc($value = null)
    {
        $desc = [
            self::WAIT => '未开始',
            self::ING => '进行中',
            self::END => '已结束',
        ];
        if (is_null($value)) {
            return $desc;
        }

        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取目标用户类型描述
     */
    public static function getTargetUserTypeDesc($value = null)
    {
        $desc = [
            self::ALL_USER => '全部用户',
        ];
        if (is_null($value)) {
            return $desc;
        }

        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取目标用户类型描述
     */
    public static function getTargetGoodsTypeDesc($value = null)
    {
        $desc = [
            self::ALL_GOODS => '全部商品',
        ];
        if (is_null($value)) {
            return $desc;
        }

        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取条件类型
     */
    public static function getConditionTypeDesc($value = null)
    {
        $desc = [
            self::BY_AMOUNT => '按订单实付金额',
            self::BY_QUANTITY => '按购买件数',
        ];
        if (is_null($value)) {
            return $desc;
        }

        return $desc[$value] ?? '';
    }
}
