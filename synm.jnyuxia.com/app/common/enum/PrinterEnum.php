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

class PrinterEnum
{
    const YLY = 1;

    // 订单列表-小票打印
    const ORDER_PRIINT = 1;
    // 订单支付完成-小票打印
    const ORDER_PAY = 2;

    /**
     * @notes 获取打印机类型
     * @param null $type
     * @return string|string[]
     * @author Tab
     * @date 2021/11/15 16:14
     */
    public static function getPrinterTypeDesc($type = null)
    {
        $desc = [
            self::YLY => '易联云'
        ];

        if (is_null($type)) {
            return $desc;
        }

        return $desc[$type] ?? '';
    }
}