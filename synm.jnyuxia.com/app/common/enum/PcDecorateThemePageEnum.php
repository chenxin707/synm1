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

class PcDecorateThemePageEnum
{
    const TYPE_HOME       = 1;
    const TYPE_LOGIN      = 2;
    const TYPE_SECKILL    = 3;
    const TYPE_COUPON     = 4;
    const TYPE_NEWS       = 5;
    const TYPE_HELP       = 6;


    /**
     * @notes 获取页面类型
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2021/11/30 15:30
     */
    public static function getPageType($from = true){
        $desc = [
            self::TYPE_HOME     => '首页',
            self::TYPE_LOGIN    => '登录页',
            self::TYPE_SECKILL  => '限时秒杀',
            self::TYPE_COUPON   => '领券中心',
            self::TYPE_NEWS     => '商城资讯',
            self::TYPE_HELP     => '帮助中心',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }


}