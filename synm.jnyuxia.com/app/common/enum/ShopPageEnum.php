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
 * 商城页面枚举
 * Class ShopPageEnum
 * @package app\common\enum
 */
class ShopPageEnum{
    //各个页面预览路径

    //首页
    const HOME_PAGE               = [
        'mobile'     =>  '/pages/index/index',
        'pc'         =>  '/',
    ];

    //商品分类
    const GOODS_CATEGORY_PAGE     = [
        'mobile'     => '/pages/category/category',
        'pc'         => '/category',
    ];

    //购物车路径
    const CART_PAGE               = [
        'mobile'     => '/pages/shop_cart/shop_cart',
        'pc'         =>  '/shop_cart',
    ];

    //个人中心路径
    const MEMBER_CENTRE_PAGE      = [
        'mobile'        => '/pages/user/user',
        'pc'            => '/pc/user/profile',
    ];

    //商品详情页路径
    const GOODS_DETAIL            = [
        'mobile'        => '/pages/goods_detail/goods_detail',
        'pc'            => '',
    ];

    //微页面路径
    const MICRO_PAGE              = [
        'mobile'        =>  '/pages/page/page',
        'pc'            =>  '',
    ];

    //商品搜索
    const GOODS_SEARCH_PAGE       = [
        'mobile'        => '/pages/goods_search/goods_search',
        'pc'            => '/goods_list',
    ];

    //订单列表
    const ORDER_LIST_PAGE         = [
        'mobile'        => '/pages/order_list/order_list',
        'pc'            => '/user/order',
    ];

    //收货地址
    const ADDRESS_PAGE            = [
        'mobile'        => '/pages/address/address',
        'pc'            => '/user/address',
    ];

    //用户设置
    const USER_PROFILE_PAGE       = [
        'mobile'        => '/bundle/pages/user_profile/user_profile',
        'pc'            => '/user/profile',
    ];

    //用户中心
    const USER_VIP_PAGE           = [
        'mobile'        => '/bundle/pages/user_vip/user_vip',
        'pc'            =>  '',
    ];

    //商品评价
    const GOODS_COMMENT_PAGE      = [
        'mobile'        => '/bundle/pages/goods_comment/goods_comment',
        'pc'            => '/user/evaluation',
    ];

    //售后
    const AFTER_SALE_PAGE         = [
        'mobile'        => '/bundle/pages/after_sale/after_sale',
        'pc'            => '/user/after_sales',
    ];

    //优惠券
    const COUPON_PAGE             = [
        'mobile'        => '/bundle/pages/coupon/coupon',
        'pc'            => '/user/coupons',
    ];

    //领券中心
    const COUPON_GET_PAGE         = [
        'mobile'        => '/bundle/pages/coupon_get/coupon_get',
        'pc'            => '/get_coupons',
    ];

    //用户钱包
    const USER_WALLET_PAGE        = [
        'mobile'        => '/bundle/pages/user_wallet/user_wallet',
        'pc'            => '/user/user_wallet',
    ];

    //信息中心
    const MESSAGE_CENTER_PAGE     = [
        'mobile'        => '/bundle/pages/message_center/message_center',
        'pc'            =>  '',
    ];

    //我的收藏
    const GOODS_COLLECTS_PAGE     = [
        'mobile'        => '/bundle/pages/goods_collects/goods_collects',
        'pc'            => '/user/collection',
    ];

    //商城公告
    const STORE_NOTICE_PAGE       = [
        'mobile'        => '/bundle/pages/store_notice/store_notice',
        'pc'            => '/news_list',
    ];

    //分销推广
    const USER_SPREAD_PAGE        = [
        'mobile'        => '/bundle/pages/user_spread/user_spread',
        'pc'            => '',
    ];

    //在线客服
    const SERVICE_PAGE            = [
        'mobile'        => '/bundle/pages/service/service',
        'pc'            => '',
    ];

    //核销员
    const VERIFICATION            = [
        'mobile'        => '/bundle/pages/verification_list/verification_list',
        'pc'            => '',
    ];

    const INITIATE                = [
        'mobile'        => '/bundle/pages/bargain_progress/bargain_progress',
        'pc'            => '',
    ];

    //会员签到
    const USER_SIGN_PAGE          = [
        'mobile'        => '/bundle/pages/user_sign/user_sign',
        'pc'            => '',
    ];

    //邀请海报
    const INVITE_POSTER_PAGE      = [
        'mobile'        => '/bundle/pages/invite_poster/invite_poster',
        'pc'            => '',
    ];

    //拼团
    const GOODS_TEAM_PAGE         = [
        'mobile'        => '/bundle/pages/goods_team/goods_team',
        'pc'            => '',
    ];

    //秒杀
    const GOODS_SECKILL_PAGE      = [
        'mobile'        => '/bundle/pages/goods_seckill/goods_seckill',
        'pc'            => '/seckill',
    ];
    //砍价
    const GOODS_BARGAIN_PAGE      = [
        'mobile'        => '/bundle/pages/goods_bargain/goods_bargain',
        'pc'            => '',
    ];

    //砍价记录
    const BARGAIN_CODE_PAGE       = [
        'mobile'        => '/bundle/pages/bargain_code/bargain_code',
        'pc'            => '',
    ];

    //拼团记录
    const GOODS_TEAM_HISTORY_PAGE = [
        'mobile'        => '/bundle/pages/goods_team_history/goods_team_history',
        'pc'            => '',
    ];
    //商城资讯
    const ARTICLE_PAGE = [
        'mobile'        => '/bundle/pages/article_lists/article_lists',
        'pc'            => '',
    ];
    //积分商城
    const INTEGRAL_MALL = [
        'mobile'        => '/bundle/pages/integral_mall/integral_mall',
        'pc'            => '',
    ];




    //商城路径页面
    const SHOP_PAGE = [
        [
            'index'     => 1,
            'name'      => '商城首页',
            'path'      => self::HOME_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 2,
            'name'      => '商品分类',
            'path'      => self::GOODS_CATEGORY_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 3,
            'name'      => '购物车',
            'path'      => self::CART_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 4,
            'name'      => '个人中心',
            'path'      => self::MEMBER_CENTRE_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 5,
            'name'      => '商品搜索',
            'path'      => self::GOODS_SEARCH_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 6,
            'name'      => '我的订单',
            'path'      => self::ORDER_LIST_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 7,
            'name'      => '收货地址',
            'path'      => self::ADDRESS_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 8,
            'name'      => '个人设置',
            'path'      => self::USER_PROFILE_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 9,
            'name'      => '会员中心',
            'path'      => self::USER_VIP_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 10,
            'name'      => '评价列表',
            'path'      => self::GOODS_COMMENT_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 10,
            'name'      => '售后列表',
            'path'      => self::AFTER_SALE_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 11,
            'name'      => '我的优惠券',
            'path'      => self::COUPON_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 12,
            'name'      => '领券中心',
            'path'      => self::COUPON_GET_PAGE['mobile'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 13,
            'name'      => '我的钱包',
            'path'      => self::USER_WALLET_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 14,
            'name'      => '消息中心',
            'path'      => self::MESSAGE_CENTER_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 15,
            'name'      => '我的收藏',
            'path'      => self::GOODS_COLLECTS_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 16,
            'name'      => '商城公告',
            'path'      => self::STORE_NOTICE_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 17,
            'name'      => '分销推广',
            'path'      => self::USER_SPREAD_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 18,
            'name'      => '在线客服',
            'path'      => self::SERVICE_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 19,
            'name'      => '核销员',
            'path'      => self::VERIFICATION['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 20,
            'name'      => '积分签到',
            'path'      => self::USER_SIGN_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 21,
            'name'      => '分销海报',
            'path'      => self::INVITE_POSTER_PAGE['mobile'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 22,
            'name'      => '限时秒杀',
            'path'      => self::GOODS_SECKILL_PAGE['mobile'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 23,
            'name'      => '超级拼团',
            'path'      => self::GOODS_TEAM_PAGE['mobile'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 24,
            'name'      => '砍价活动',
            'path'      => self::GOODS_BARGAIN_PAGE['mobile'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 25,
            'name'      => '砍价记录',
            'path'      => self::BARGAIN_CODE_PAGE['mobile'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 26,
            'name'      => '拼团记录',
            'path'      => self::GOODS_TEAM_HISTORY_PAGE['mobile'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 27,
            'name'      => '商城资讯',
            'params'    => [],
            'path'      => self::ARTICLE_PAGE['mobile'],
            'type'      => 'shop',
        ],
        [
        'index'     => 28,
        'name'      => '积分商城',
        'params'    => [],
        'path'      => self::INTEGRAL_MALL['mobile'],
        'type'      => 'shop',
        ]

    ];


    //pc端各个页面的路径
    const PC_SHOP_PAGE = [
        [
            'index'     => 1,
            'name'      => '商城首页',
            'path'      => self::HOME_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '商品分类',
            'path'      => self::GOODS_CATEGORY_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '购物车',
            'path'      => self::CART_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '商品搜索',
            'path'      => self::GOODS_SEARCH_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '我的订单',
            'path'      => self::ORDER_LIST_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '收货地址',
            'path'      => self::ADDRESS_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '个人设置',
            'path'      => self::USER_PROFILE_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '评价列表',
            'path'      => self::GOODS_COMMENT_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '售后列表',
            'path'      => self::AFTER_SALE_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '我的优惠券',
            'path'      => self::COUPON_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '我的钱包',
            'path'      => self::USER_WALLET_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '我的收藏',
            'path'      => self::GOODS_COLLECTS_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '商城公告',
            'path'      => self::STORE_NOTICE_PAGE['pc'],
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'index'     => 1,
            'name'      => '领券中心',
            'path'      => self::COUPON_GET_PAGE['pc'],
            'params'    => [],
            'type'      => 'marking',
        ],
        [
            'index'     => 1,
            'name'      => '限时秒杀',
            'path'      => self::GOODS_SECKILL_PAGE['pc'],
            'params'    => [],
            'type'      => 'marking',
        ],
    ];
    

    
    


}