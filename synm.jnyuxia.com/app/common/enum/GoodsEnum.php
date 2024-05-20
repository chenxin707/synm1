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
 * 商品枚举
 * Class GoodsEnum
 * @package app\common\enum
 */
class GoodsEnum
{
    const GOODS_REALITY     = 1;        //实物商品
    const GOODS_VIRTUAL     = 2;        //虚拟商品

    const STATUS_SELL       = 1;
    const STATUS_STORAGE    = 0;

    const SEPC_TYPE_SIGNLE  = 1;
    const SEPC_TYPE_MORE    = 2;
    const SPEC_SEPARATOR    = ','; //规格名称分隔符

    const AFTER_PAY_AUTO            = 1;    //自动发货
    const AFTER_PAY_HANDOPERSTION   = 2;    //手动发货

    const AFTER_DELIVERY_AUTO           = 1;    //自动完成订单
    const AFTER_DELIVERY_HANDOPERSTION  = 2;    //需要买家确认

    const GATHER_CHANNEL_UNKNOWN = 0;//未知
    const GATHER_CHANNEL_TAOBAO = 1;//淘宝
    const GATHER_CHANNEL_TMALL = 2;//天猫
    const GATHER_CHANNEL_JD = 3;//京东
    const GATHER_CHANNEL_1688 = 4;//阿里巴巴


    const LIMIT_TYPE_NO = 1;//不限制
    const LIMIT_TYPE_USER = 2;//每人限购
    const LIMIT_TYPE_ORDER = 3;//每笔订单限购

    /**
     * @notes 商品规格类型
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2021/8/23 10:30
     */
    public static function getSpecTypeDesc($from = true)
    {
        $desc = [
            self::SEPC_TYPE_SIGNLE      =>  '单规格',
            self::SEPC_TYPE_MORE        =>  '多规格',
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
     * @date 2021/9/10 15:09
     */
    public static function getStatusDesc($from = true)
    {
        $desc = [
            self::STATUS_SELL       => '销售中',
            self::STATUS_STORAGE    => '仓库中',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }
    /**
     * @notes 获取商品类型
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2022/4/19 12:09
     */
    public static function getGoodsTypeDesc($from = true){
        $desc = [
            self::GOODS_REALITY     => '实物商品',
            self::GOODS_VIRTUAL     => '虚拟商品',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }


    /**
     * @notes 采集渠道
     * @param bool $from
     * @return string|string[]
     * @author ljj
     * @date 2023/3/14 11:03 上午
     */
    public static function getGatherChannelDesc($from = true)
    {
        $desc = [
            self::GATHER_CHANNEL_UNKNOWN     => '未知',
            self::GATHER_CHANNEL_TAOBAO     => '淘宝',
            self::GATHER_CHANNEL_TMALL     => '天猫',
            self::GATHER_CHANNEL_JD     => '京东',
            self::GATHER_CHANNEL_1688     => '阿里巴巴',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }
}
