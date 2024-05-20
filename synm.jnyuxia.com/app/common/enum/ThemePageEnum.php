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
 * 主题页面枚举
 * Class ThemePageEnum
 * @package app\common\enum
 */
class ThemePageEnum
{
    const TYPE_HOME             = 1;
    const TYPE_GOODS_CATEGORY   = 2;
    const TYPE_MEMBER_CENTRE    = 3;
    const TYPE_CART             = 4;
    const TYPE_GOODS_DETAIL     = 5;

    //组件
    const GOODS         = 'goodsgroup';     //商品组件
    const COUPON        = 'coupon';         //优惠券组件
    const USERSERVE     = 'userserve';      //我的服务
    const TABS          = 'tabs';           //选项卡
    const NOTICE        = 'notice';         //公告
    const GOODSRECOM    = 'goodsrecom';     //商品推荐
    const NAVIGATION    = 'navigation';     //导航组件
    const SPELLGROUP    = 'spellgroup';     //拼团组件
    const SECKILL       = 'seckill';        //秒杀组件
    const MNPLIVE       = 'mnplive';        //小程序直播组件

    //需要替换数据的组件
    const MODULE = [
        self::GOODS,self::COUPON,self::USERSERVE,self::TABS, self::NOTICE,
        self::GOODSRECOM,self::SPELLGROUP,self::SECKILL,self::MNPLIVE
    ];

    /**
     * @notes 获取类型
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2021/8/6 16:27
     */
    public static function getTypeDesc($from = true)
    {
        $desc = [
            self::TYPE_HOME             => '首页',
            self::TYPE_GOODS_CATEGORY   => '商品分类',
            self::TYPE_MEMBER_CENTRE    => '个人中心',
            self::TYPE_CART             => '购物车',
            self::TYPE_GOODS_DETAIL     => '商品详情',
        ];

        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';

    }

}