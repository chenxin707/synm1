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

/**
 * 聊天商品
 * Class ChatGoodsEnum
 * @package app\common\enum
 */
class ChatGoodsEnum
{
    // 聊天商品类型
    const GOODS_NORMAL = 'normal'; // 普通商品
    const GOODS_SECKILL = 'seckill';// 秒杀商品
    const GOODS_TEAM = 'team';// 拼团商品
    const GOODS_BARGAIN = 'bargain';// 砍价商品

    //聊天商品类型
    const GOODS_TYPE = [
        self::GOODS_NORMAL,
        self::GOODS_SECKILL,
        self::GOODS_TEAM,
        self::GOODS_BARGAIN,
    ];

}