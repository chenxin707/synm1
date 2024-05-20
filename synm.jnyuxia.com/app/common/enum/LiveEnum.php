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



class LiveEnum
{
    const LIVE_ING          = 101;
    const LIVE_NOT_START    = 102;
    const LIVE_OVER         = 103;
    const LIVE_FORBID       = 104;
    const LIVE_STOP         = 105;
    const LIVE_EXCEPTION    = 106;
    const LIVE_OVERDUE      = 107;

    const GOODS_UNREVIEWED  = 0;
    const GOODS_AUDIT       = 1;
    const GOODS_PASS        = 2;
    const GOODS_PRJECT      = 3;

    /**
     * @notes 获取直播间状态
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2021/11/22 15:25
     */
    public static function getLiveStatus($from = true){
        $desc = [
            self::LIVE_ING          => '直播中',
            self::LIVE_NOT_START    => '未开始',
            self::LIVE_OVER         => '已结束',
            self::LIVE_FORBID       => '禁播',
            self::LIVE_STOP         => '暂停',
            self::LIVE_EXCEPTION    => '异常',
            self::LIVE_OVERDUE      => '已过期',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }

    /**
     * @notes 商品状态
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2021/11/23 15:15
     */
    public static function getGoodsStatus($from = true){
        $desc = [
            self::GOODS_UNREVIEWED      => '未审核',
            self::GOODS_AUDIT           => '审核中',
            self::GOODS_PASS            => '审核通过',
            self::GOODS_PRJECT          => '审核驳回',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }

}