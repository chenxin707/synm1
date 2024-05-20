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
/**
 * @todo 权限控制
 */
return [
    //首页
    'index'     => [
        //控制台
        'index' => [
            'page_path'     => '/index',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['workbench/index'],
            ],
        ]
    ],
    //店铺
    'theme'     => [
        //首页
        'shopindex'          => [
            'page_path'     => ['/shop/index','/decorate/index'],
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['theme.decoratethemepage/index'],
            ],
            'edit'          => [
                'button_auth'   => ['edit'],
                'action_auth'   => ['theme.decoratethemepage/getpage'],
            ],
        ],
        //商品分类
        'decoratepages'    => [
            'page_path'     => ['/decorate/category','/decorate/goods_detail','/decorate/cart','/decorate/user'],
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['theme.decoratethemepage/getpage'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['theme.decoratethemepage/edit'],
            ],
        ],
        //页面管理
        'pages'          => [
            'page_path'     => '/shop/pages/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['theme.decoratethemepage/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'theme.decoratethemepage/add',
                    'theme.decoratethemepage/getpage',
                ],
            ],
        ],
        //页面模板
        'template'      => [
            'page_path'     => '/shop/pages/template',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['theme.systemthemepage/lists'],
            ],
            'open'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'theme.decoratethemepage/add',
                ],
            ],
        ],
        //商城风格、底部导航
        'content'        => [
            'page_path'     => ['/shop/theme','/shop/tabbar',],
            'view'          =>[
                'button_auth'   => ['view'],
                'action_auth'   => ['theme.decoratethemeconfig/getcontent'],
            ],
            'save'          => [
                'button_auth'   => ['save'],
                'action_auth'   => ['theme.decoratethemeconfig/setcontent'],
            ],

        ],
        //素材
        'material'         => [
            'page_path'     => '/shop/material',
            'view'          =>[
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'file/lists',
                    'file/listcate',
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'file/addCate',
                    'file/delete',
                    'file/move',
                    'file/rename',
                    'file/rename',
                ]
            ],
        ],
        //pc端装修
        'pcdecorate'          => [
            'page_path'     => [
                '/shop/pc/index',
                '/decorate/pc_index',
                '/shop/pc/adv'
            ],
            'view'          =>[
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'theme.pcdecoratethemepage/index',
                    'theme.pcdecoratethemepage/getpage',
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'theme.pcdecoratethemepage/setpage',
                    'theme.pcdecoratethemepage/getpcpage',
                ]
            ],
        ],
    ],
    //商品
    'goods'     => [
        //商品管理
        'goods'         => [
            'page_path' => '/goods/lists',
            'view'      => [//查看
                'button_auth'   => ['view'],
                'action_auth'   => ['goods.goods/lists','goods.goods/otherlist'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goods/add',
                    'goods.goods/edit',
                    'goods.goods/del',
                    'goods.goods/status',
                    'goods.goods/sort',
                    'goods.goods/export',
                ]
            ],
        ],
        //分类管理
        'category' => [
            'page_path'     => '/goods/category',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['goods.goodscategory/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.category/add',
                    'goods.category/edit',
                    'goods.category/status',
                    'goods.category/del',
                ]
            ],
        ],
        //品牌管理
        'brand'    => [
            'page_path'     => '/goods/brand',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['goods.goodsbrand/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goodsbrand/add',
                    'goods.goodsbrand/edit',
                    'goods.goodsbrand/status',
                    'goods.goodsbrand/del',
                ]
            ],
        ],
        //商品单位
        'unit'     => [
            'page_path'     => '/goods/unit',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['goods.goodsunit/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goodsunit/add',
                    'goods.goodsunit/edit',
                    'goods.goodsunit/status',
                    'goods.goodsunit/del',
                ]
            ],
        ],
        //供应商管理
        'supplier'    => [//商品供应商
            'page_path'   => '/goods/supplier/lists',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['goods.goodssupplier/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goodssupplier/add',
                    'goods.goodssupplier/edit',
                    'goods.goodssupplier/del',
                ]
            ],
        ],
        //供应商分类
        'suppliercategory'    => [
            'page_path'   => '/goods/supplier/category',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['goods.goodssuppliercategory/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goodssuppliercategory/add',
                    'goods.goodssuppliercategory/edit',
                    'goods.goodssuppliercategory/del',
                ]
            ],
        ],
        //评价管理
        'comment'              => [
            'page_path'   => '/goods/evaluation',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['goods.goodscomment/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goodscomment/reply',
                    'goods.goodscomment/status',
                    'goods.goodscomment/del',
                ]
            ],
        ],
    ],
    //订单
    'order'     => [
        //订单管理
        'order'     => [
            'page_path'     => '/order/order',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['order.order/otherlists','order.order/lists'],
            ],
            //取消订单
            'cancel'        => [
                'button_auth'   => ['cancel'],
                'action_auth'   => ['order.order/cancel']
            ],
            //修改价格
            'changeprice'        => [
                'button_auth'   => ['changeprice'],
                'action_auth'   => ['order.order/changeprice']
            ],
            //修改运费
            'changeexpressprice'        => [
                'button_auth'   => ['changeprice'],
                'action_auth'   => ['order.order/changeexpressprice']
            ],
            //修改地址、发货
            'addressdelivery'        => [
                'button_auth'   => ['addressedit','delivery'],
                'action_auth'   => ['order.order/addressedit','order.order/delivery']
            ],
            //确认收货
            'confirm'           => [
                'button_auth'   => ['confirm'],
                'action_auth'   => ['order.order/confirm']
            ],
            'manage'        => [
                'button_auth'   => [
                    'auth_all'
                ],
                'action_auth'   => [
                    'order.order/detail',               //订单详情
                    'order.order/orderremarks',         //商家备注
                    'order.order/deliveryinfo',         //发货订单
                ]
            ],
        ],
        'aftersales'=> [
            'page_path'     => '/order/after_sales',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['after_sale.aftersale/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'order.aftersales/agree',               //同意售后
                    'order.aftersales/refuse',              //拒绝售后
                    'order.aftersales/refusegoods',         //拒绝收货
                    'order.aftersales/confirmgoods',        //确认收货
                    'order.aftersales/agreerefund',         //卖家同意退款
                    'order.aftersales/refuserefund',        //卖家拒绝退款
                    'order.aftersales/confirmrefund',       //卖家确认退款
                    'order.aftersales/detail',              //查看售后详情
                ]
            ],
        ],
    ],
    //用户
    'user'      => [
        //用户概述
        'profile'   => [
            'page_path'     => '/user/profile',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['user.user/index'],
            ],
        ],
        //用户管理
        'list'      => [
            'page_path'     => '/user/lists',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['user.user/lists','user.user/otherlist'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'user.user/detail',             //用户详情
                    'user.user/info',               //用户信息
                    'user.user/setinfo',            //设置用户信息
                    'user.user/setlabel',           //设置批量标签
                    'user.user/userinviterlists',   //我邀请的人
                    'user.user/setuserlabel',       //设置用户标签
                ]
            ],
            'adjustaccount' => [
                'button_auth'   => [
                    'adjustuserwallet'
                ],
                'action_auth'   => [
                    'user.user/adjustuserwallet'
                ],
            ],
        ],
        //用户等级
        'userlevel' => [
            'page_path'     => '/user/grade',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['user.userlevel/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'user.userlevel/add',
                    'user.userlevel/edit',
                    'user.userlevel/detail',
                    'user.userlevel/del',
                ]
            ],
        ],
        //用户等级
        'userlabel' => [
            'page_path'     => '/user/tag',
            'view'      => [
                'button_auth'  => ['view'],
                'action_auth'  => ['user.userlabel/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'user.userlabel/add',
                    'user.userlabel/edit',
                    'user.userlabel/detail',
                    'user.userlabel/del',
                ]
            ],
        ],
    ],
    //营销
    'marketing' => [
        //应用中心
        'marketing' => [
            'page_path'     => '/marketing/index',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['config/getmarketingmodule'],
            ],
        ],
        //营销中心
        'marketingapp' => [
            'page_path'     => '/application/app',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['config/getappmodule'],
            ],
        ],
        //优惠券概览
        'couponsurvey'  => [
            'page_path'     => ['/coupon/survey'],
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.coupon/survey'],
            ],
        ],
        //优惠券
        'couponlists'    => [
            'page_path'     => '/coupon/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.coupon/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'marketing.coupon/add',             //新增优惠券
                    'marketing.coupon/edit',            //编辑优惠券
                    'marketing.coupon/delete',          //删除优惠券
                    'marketing.coupon/info',            //优惠券详情
                    'marketing.coupon/open',            //开启发放优惠券
                    'marketing.coupon/stop',            //结束发放优惠券
                    'marketing.coupon/send',            //卖家发放优惠券
                    'marketing.coupon/detail',          //获取优惠券详细
                ]
            ],
        ],
        //优惠券记录
        'couponrecord'        => [
            'page_path'     => '/coupon/receive_record',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.coupon/record'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'marketing.coupon/void',       //作废优惠券
                    'order.order/detail',          //查看订单
                ]
            ],
        ],
        //秒杀
        'seckilllists'          => [
            'page_path'     => '/seckill/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.seckill/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'marketing.seckill/add',       //新增秒杀
                    'marketing.seckill/edit',      //编辑秒杀
                    'marketing.seckill/info',      //秒杀信息
                    'marketing.seckill/delete',    //删除秒杀
                    'marketing.seckill/open',      //确认开启活动
                    'marketing.seckill/stop',      //停止关闭活动
                ]
            ],
        ],
        //拼团活动
        'combinationsurvey'     => [
            'page_path'     => '/combination/survey',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.team/survey'],
            ],
        ],
        //拼团活动
        'combinationlists'     => [
            'page_path'     => '/combination/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.team/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'marketing.team/add',       //新增拼团
                    'marketing.team/edit',      //编辑拼团
                    'marketing.team/info',      //拼团信息
                    'marketing.team/delete',    //删除拼团
                    'marketing.team/open',      //确认开启活动
                    'marketing.team/stop',      //停止关闭活动
                ]
            ],
        ],
        //砍价活动
        'bargainlists'      => [
            'page_path'     => '/bargain/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['bargain.bargainactivity/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'bargain.bargainactivity/add',             //添加砍价
                    'bargain.bargainactivity/edit',            //编辑砍价
                    'bargain.bargainactivity/detail',          //砍价详情
                    'bargain.bargainactivity/confirm',         //确认砍价
                    'bargain.bargainactivity/stop',            //停止砍价
                    'bargain.bargainactivity/delete',          //删除砍价
                ],
            ],
        ],
        'bargainrecord'       => [
            'page_path'     => '/bargain/bargain_record',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['bargain.bargainactivity/activityrecord'],
            ],
            'stop'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'bargain.bargainactivity/stop',             //停止砍价
                ],
            ],
        ],
        //会员折扣
        'discountlevel'     => [
            'page_path'     => '/member_price/index',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['marketing.discount/lists','marketing.discount/otherLists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'marketing.discount/join',                  //参与折扣
                    'marketing.bargainactivity/quit',           //不参与折扣
                    'marketing.bargainactivity/detail',         //折扣详情
                    'marketing.bargainactivity/setDiscount',    //设置折扣
                ],
            ],
        ],
        //分销概览
        'distributionsurvey'    => [
            'page_path'     => '/distribution/survey',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['distribution.distributiondata/datacenter'],
            ],
        ],
        //分销商
        'distributionstore'    => [
            'page_path'     => '/distribution/store',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'distribution.distributionmember/lists',
                    'distribution.distributionlevel/lists'
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'distribution.distributionmember/detail',           //查看分销商详情
                    'distribution.distributionmember/adjustLevelinfo',  //调整分销等级界面信息
                    'distribution.distributionmember/getfans',          //查看下级
                    'distribution.distributionmember/getfanslists',     //下级列表
                    'distribution.distributionmember/freeze',           //冻结/解冻资格
                    'distribution.distributionmember/open',             //开通分销
                ],
            ],
        ],
        //分销申请
        'distributionapplystore'   => [
            'page_path'     => '/distribution/distribution_apply',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['distribution.distributionapply/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'distribution.distributionapply/detail',    //详情
                    'distribution.distributionapply/pass',      //审核通过
                    'distribution.distributionapply/refuse',    //审核拒绝

                ],
            ],
        ],
        //分销商商品
        'distributiongoods'   => [
            'page_path'     => '/distribution/distribution_goods',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['distribution.distributiongoods/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'distribution.distributiongoods/set',       //设置佣金
                    'distribution.distributionapply/join',      //参与/不参与分销
                    'distribution.distributionapply/detail',    //查看分销商品详情

                ],
            ],
        ],
        //分销商订单
        'distributionorder'   => [
            'page_path'     => '/distribution/distribution_orders',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['distribution.distributionordergoods/lists'],
            ],
        ],
        //分销等级
        'distributiongrade'   => [
            'page_path'     => '/distribution/distribution_grade',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['distribution.distributionlevel/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'distribution.distributionlevel/add',       //添加等级
                    'distribution.distributionlevel/edit',      //编辑等级
                    'distribution.distributionlevel/detail',    //等级详情
                    'distribution.distributionlevel/delete',    //删除等级

                ],
            ],
        ],
        //分销设置-基础设置
        'distributionconfig'   => [
            'page_path'     => ['/distribution/base_setting','/distribution/result_setting'],
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['distribution.distributionconfig/getconfig'],
            ],
            'save'          => [
                'button_auth'   => ['save'],
                'action_auth'   => ['distribution.distributionconfig/setconfig'],
            ],
        ],
        //充值概览
        'rechargesurvey'   => [
            'page_path'     => '/recharge/survey',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['recharge.recharge/datacenter'],
            ]
        ],
        //充值规则
        'rechargerule'   => [
            'page_path'     => '/recharge/rule',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['recharge.recharge/getconfig'],
            ],
            'save'          => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['recharge.recharge/setconfig'],
            ],
        ],
        //充值记录
        'rechargerecord'    => [
            'page_path'     => '/recharge/record',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['recharge.recharge/lists'],
            ],
        ],
        //商城公告
        'shopnotice'        => [
            'page_path'     => '/notice/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['shopnotice.shopnotice/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'shopnotice.shopnotice/add',       //添加公告
                    'shopnotice.shopnotice/edit',      //编辑公告
                    'shopnotice.shopnotice/detail',    //公告详情
                    'shopnotice.shopnotice/status',    //公告状态
                    'shopnotice.shopnotice/del',       //删除公告
                ],
            ],
        ],
        //签到概览
        'calendarsurvey'   => [
            'page_path'     => '/calendar/survey',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['sign.sign/datacenter'],
            ]
        ],
        //签到规则
        'calendarrule'   => [
            'page_path'     => '/calendar/rule',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['sign.sign/getconfig'],
            ],
            'save'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['sign.sign/setconfig'],
            ],
        ],
        //签到记录
        'calendarrecord'    => [
            'page_path'     => '/calendar/record',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['sign.sign/lists'],
            ],
        ],
        //幸运抽奖
        'luckydraw'     => [
            'page_path'     => '/lucky_draw/index',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['luckydraw.luckydraw/lists'],
            ],
            'manage'            => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'luckydraw.luckydraw/add',
                    'luckydraw.luckydraw/edit',
                    'luckydraw.luckydraw/detail',
                    'luckydraw.luckydraw/del',
                    'luckydraw.luckydraw/start',
                    'luckydraw.luckydraw/end',
                    'luckydraw.luckydraw/record',
                    'luckydraw.luckydraw/getprizetype',
                ],
            ],
        ],
        //核销订单
        'selffetchorder'    => [
            'page_path'     => '/selffetch/selffetch_order',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['selffetchshop.verification/lists'],
            ],
            'manage'            => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'selffetchshop.verification/verification',          //提货核销
                    'selffetchshop.verification/verificationquery',     //核销查询
                    'selffetchshop.verification/verificationdetail',    //查看核销详情
                ],
            ],
        ],
        //自提门店
        'selffetchshop'    => [
            'page_path'     => '/selffetch/selffetch_shop',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['selffetchshop.selffetchshop/lists'],
            ],
            'manage'            => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'selffetchshop.selffetchshop/add',              //添加自提门店
                    'selffetchshop.selffetchshop/edit',             //编辑自提门店
                    'selffetchshop.selffetchshop/detail',           //查看自提详情
                    'selffetchshop.selffetchshop/status',           //修改自提门店状态
                    'selffetchshop.selffetchshop/del',              //删除自提门店
                    'selffetchshop.selffetchshop/regionSearch',     //腾讯地图区域搜索
                ],
            ],
        ],
        //核销员
        'selffetchverifier'    => [
            'page_path'     => '/selffetch/selffetch_verifier',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['selffetchshop.selffetchverifier/lists'],
            ],
            'manage'            => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'selffetchshop.selffetchverifier/add',              //添加核销员
                    'selffetchshop.selffetchverifier/edit',             //编辑核销员
                    'selffetchshop.selffetchverifier/detail',           //查看核销员
                    'selffetchshop.selffetchverifier/status',           //修改核销员状态
                    'selffetchshop.selffetchverifier/del',              //删除核销员
                ],
            ],
        ],
        //直播间
        'livebroadcastlists'    => [
            'page_path'     => '/live_broadcast/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['live.liveroom/lists'],
            ],
            'manage'            => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'live.liveroom/lists/add',              //添加
                    'live.liveroom/lists/del',              //删除
                ],
            ],
        ],
        //直播商品
        'livebroadcastgoods'    => [
            'page_path'     => '/live_broadcast/goods',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['live.livegoom/lists'],
            ],
            'manage'            => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'live.livegoom/lists/add',              //添加
                    'live.livegoom/lists/del',              //删除
                ],
            ],
        ],
        //业务通知
        'notice'   => [
            'page_path'     => [
                '/sms/seller',
                '/sms/buyers/buyers',
                '/sms/buyers/business',
            ],
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['notice.notice/settinglists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'notice.notice/detail',
                    'notice.notice/set',
                ],
            ],
        ],
        //短信设置
        'smssetting'   => [
            'page_path'     => '/sms/sms',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['notice.smsconfig/getconfig'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'notice.smsconfig/detail',
                    'notice.smsconfig/setconfig',
                ],
            ],
        ],
        //在线客服
        'service'   => [
            'page_path'     => '/service/setting',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.service.service/getconfig'],
            ],
            'save'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'settings.service.service/setconfig',
                ],
            ],
        ],
        //客服列表
        'servicelists'  => [
            'page_path'     => '/service/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['kefu.kefu/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'kefu.kefu/add',
                    'kefu.kefu/edit',
                    'kefu.kefu/detail',
                    'kefu.kefu/del',
                    'kefu.kefu/status',
                    'kefu.kefu/login',
                ],
            ],
        ],
        //客服话术
        'speechlists'  => [
            'page_path'     => '/service/speech_lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['kefu.kefulang/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'kefu.kefulang/add',
                    'kefu.kefulang/edit',
                    'kefu.kefulang/detail',
                    'kefu.kefulang/del',
                ],
            ],
        ],
        //批量打印
        'expressassistant' => [
            'page_path'     => '/express/batch',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'expressassistant.facesheetsender/lists',
                    'expressassistant.facesheettemplate/lists',
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'expressassistant.facesheetorder/print',
                ],
            ],
        ],
        //发件人模板
        'facesheetsender' => [
            'page_path'     => '/express/sender',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'expressassistant.facesheetsender/lists',

                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'expressassistant.facesheetsender/add',
                    'expressassistant.facesheetsender/detail',
                    'expressassistant.facesheetsender/edit',
                    'expressassistant.facesheetsender/delete',
                ],
            ],
        ],
        //电子面单模板
        'facesheettemplate' => [
            'page_path'     => '/express/face_sheet',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'expressassistant.facesheettemplate/lists',

                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'expressassistant.facesheettemplate/add',
                    'expressassistant.facesheettemplate/detail',
                    'expressassistant.facesheettemplate/edit',
                    'expressassistant.facesheettemplate/delete',
                ],
            ],
        ],
        //面单设置
        'facesheetsetting' => [
            'page_path'     => '/express/face_sheet_set',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'expressassistant.facesheetsetting/getconfig',
                    'expressassistant.facesheetsetting/getfacesheettype',

                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'expressassistant.facesheetsetting/setconfig',

                ],
            ],
        ],
        //小票打印
        'print'         => [
            'page_path'     => '/print/list',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['printer.printer/printerlists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'printer.printer/getprintertype',
                    'printer.printer/printertemplatelists',
                    'printer.printer/printertemplatelists',
                    'printer.printer/printerdetail',
                    'printer.printer/autoprint',
                    'printer.printer/addprinter',
                    'printer.printer/editprinter',
                    'printer.printer/testprinter',
                    'printer.printer/deleteprinter',
                ],
            ],
        ],
        //小票模板
        'printtemplate' => [
            'page_path'     => '/print/template',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.service.service/getconfig'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'printer.printer/printertemplatelists',
                    'printer.printer/addtemplate',
                    'printer.printer/templatedetail',
                    'printer.printer/edittemplate',
                    'printer.printer/deletetemplate',
                ],
            ],
        ],
        //评价助手
        'evaluation' => [
            'page_path'     => '/evaluation/index',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'goods.goods_comment_assistant/lists',
                    'goods.goods/otherList'
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'user.user_level/lists',
                    'goods.goods_comment_assistant/add',
                ],
            ],
        ],
        //商城资讯
        'article'           => [
            'page_path'     => '/article/lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['article/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'article/add',
                    'article/edit',
                    'article/detail',
                    'article/isShow',
                    'article/delete',
                ],
            ],
        ],
        //商城资讯分类
        'articlecategory'   => [
            'page_path'     => '/article/category_lists',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['category_lists/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'article_category/add',
                    'article_category/edit',
                    'article_category/detail',
                    'article_category/isShow',
                    'article_category/delete',
                ],
            ],
        ],
        // 积分商品
        'integralgoods'   => [
            'page_path'     => '/integral_mall/integral_goods',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['integral.integral_goods/lists'],
            ],
            'save'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'integral.integral_goods/add',
                    'integral.integral_goods/edit',
                    'integral.integral_goods/del',
                    'integral.integral_goods/status',
                ],
            ],
        ],
        // 积分订单
        'integralorder'   => [
            'page_path'     => '/integral_mall/exchange_order',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['integral.integral_order/lists'],
            ],
            'save'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'integral.integral_order/delivery',
                    'integral.integral_order/confirm',
                    'integral.integral_order/cancel',
                    'integral.integral_order/logistics',
                ],
            ],
        ],
        // 消费奖励
        'awardintegral'   => [
            'page_path'     => '/consumption_reward/setting',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['award_integral/getConfig'],
            ],
            'save'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'award_integral/setConfig',
                ],
            ],
        ],
        // 包邮活动
        'freeshipping'   => [
            'page_path'     => '/free_shipping/index',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['free_shipping.freeshipping/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'free_shipping.freeshipping/add',
                    'free_shipping.freeshipping/edit',
                    'free_shipping.freeshipping/detail',
                    'free_shipping.freeshipping/start',
                    'free_shipping.freeshipping/end',
                    'free_shipping.freeshipping/delete',
                ],
            ],
        ],
        // 足迹汽泡
        'footprint'   => [
            'page_path'     => '/footprint/index',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'footprint/getconfig',
                    'footprint/lists',
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'footprint/setconfig',
                    'footprint/status',
                ],
            ],
        ],
        // 自定义海报
        'poster'   => [
            'page_path'     => '/custom_poster/goods',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'poster.poster/getgoodsconfig',
                    'poster.poster/getdistributionconfig',
                ],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'poster.poster/setgoodsconfig',
                    'poster.poster/setdistributionconfig',
                ],
            ],
        ],
        // 商品采集
        'gather'        => [
            'page_path'     => '/goods_collection/index',
            'view'          => [
                'button_auth'   => ['view'],
                'action_auth'   => ['goods.goodsgather/logLists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'goods.goodsgather/gather',//采集
                    'goods.goodsgather/del',//删除采集记录
                    'goods.goodsgather/lists',//商品采集列表
                    'goods.goodsgather/gatherGoodsDetail',//采集商品详情
                    'goods.goodsgather/gatherGoodsEdit',//编辑采集商品
                    'goods.goodsgather/createGoods',//创建商品
                ],
            ],
        ],
    ],
    //财务
    'finance'   => [
        //财务概况
        'profile'   => [
            'page_path'     => '/finance/profile',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['finance.finance/datacenter'],
            ],
        ],
        //提现记录
        'userwithdrawal'   => [
            'page_path'     => '/finance/user_withdrawal',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['withdraw.withdraw/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'withdraw.withdraw/detail',
                    'withdraw.withdraw/pass',
                    'withdraw.withdraw/refuse',
                ]
            ],
        ],
        //余额明细
        'accountlog'   => [
            'page_path'     => '/finance/account_log',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['accountlog/lists','accountlog/getchangetype'],
            ],
        ],
        //积分明细
        'integrallog'  => [
            'page_path'     => '/finance/integral_list',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['accountlog/lists','accountlog/getintegralchangetype'],
            ],
        ],
        //佣金明细
        'commissionlog'  => [
            'page_path'     => '/finance/commission_log',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['accountlog/lists','accountlog/getintegralchangetype'],
            ],
        ],

    ],
    //数据
    'data'      => [
        //流量分析
        'flowanalysis'=> [
            'page_path'     => '/data/flow_analysis',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['data.center/trafficanalysis'],
            ],
        ],
        //用户分析
        'user'    => [
            'page_path'     => '/data/user',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['data.center/userAnalysis'],
            ],
        ],
        //交易分析
        'transaction'    => [
            'page_path'     => '/data/transaction',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['data.center/transactionanalysis'],
            ],
        ],
        //商品分析
        'goods'    => [
            'page_path'     => '/data/goods/goods',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['data.center/goodsanalysis'],
            ],
        ],
        //交易设置
        'goodsrank'  => [
            'page_path'     => '/data/goods/ranking',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['data.center/goodstop'],
            ],
        ],
    ],
    //渠道
    'channel'   => [
        //微信公众号设置
        'oasetting'   => [
            'page_path'     => '/channel/mp_wechat/index',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['wechat.officialaccountsetting/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['wechat.officialaccountsetting/setConfig'],
            ],
        ],
        //微信菜单设置
        'oamenu'   => [
            'page_path'     => '/channel/mp_wechat/menu',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['wechat.officialaccountmenu/detail'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['wechat.officialaccountsetting/saveandpublish'],
            ],
        ],
        //关注回复
        'oafollowreply'   => [
            'page_path'     => ['/channel/mp_wechat/reply/follow_reply','/channel/mp_wechat/reply/keyword_reply','/channel/mp_wechat/reply/default_reply'],
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['wechat.officialaccountreply/lists'],
            ],
            'manage'        => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'wechat.officialaccountreply/add',
                    'wechat.officialaccountreply/edit',
                    'wechat.officialaccountreply/status',
                    'wechat.officialaccountreply/detail',
                    'wechat.officialaccountreply/del',
                ]
            ],
        ],
        //小程序设置
        'mpsetting'   => [
            'page_path'     => '/channel/wechat_app/wechat_app',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['wechat.miniprogramsetting/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['wechat.miniprogramsetting/setconfig'],
            ],
        ],
        //APP设置
        'appsetting'   => [
            'page_path'     => '/channel/app_store/app_store',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.app.appsetting/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.app.appsetting/setconfig'],
            ],
        ],
        //H5设置
        'h5setting'   => [
            'page_path'     => '/channel/h5_store/h5_store',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.h5.hfivesetting/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.h5.appsetting/setconfig'],
            ],
        ],
        //今日头条
        'mptoutiao'   => [
            'page_path'     => '/channel/mp_toutiao/index',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['toutiao.toutiao_setting/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['toutiao.toutiao_setting/setconfig'],
            ],
        ],
        //pc商城-渠道设置
        'pcstore'   => [
            'page_path'     => '/channel/pc_store/index',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['pc.pcsetting/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['pc.pcsetting/setconfig'],
            ],
        ],

    ],
    //设置
    'setting'   => [
        //店铺信息
        'shop'      => [
            'page_path'     => '/setting/shop/shop',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.shop.shopsetting/getshopinfo'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.shop.shopsetting/setconfig'],
            ],
        ],
        //备案信息
        'record'      => [
            'page_path'     => '/setting/shop/record',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.shop.shopsetting/getrecordinfo'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.shop.shopsetting/setrecordinfo'],
            ],
        ],
        //分享信息
        'share'     => [
            'page_path'     => '/setting/shop/share',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.shop.shopsetting/getsharesetting'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.shop.shopsetting/setsharesetting'],
            ],
        ],
        //政策协议
        'protocol'     => [
            'page_path'     => '/setting/shop/protocol',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.shop.shopsetting/getpolicyagreement'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.shop.shopsetting/setpolicyagreement'],
            ],
        ],
        //支付方式
        'paymethod'     => [
            'page_path'     => '/setting/payment/pay_method',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.pay.payway/getpayway'],
            ],
            'save'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.pay.payway/setpayway'],
            ],
        ],
        //支付配置
        'payconfig'     => [
            'page_path'     => '/setting/payment/pay_config',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.pay.payconfig/lists'],
            ],
            'edit'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'settings.pay.payway/getconfig',
                    'settings.pay.payway/setconfig',
                ],
            ],
        ],
        //配送方式
        'delivery'      => [
            'page_path'     => '/setting/delivery/index',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.delivery.deliveryway/getconfig'],
            ],
            'manage'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'settings.delivery.deliveryway/setconfig',
                    'settings.delivery.freight/lists',
                    'settings.delivery.freight/add',
                    'settings.delivery.freight/edit',
                    'settings.delivery.freight/del',
                    'settings.delivery.express/lists',
                    'settings.delivery.express/add',
                    'settings.delivery.express/edit',
                    'settings.delivery.express/del',
                    'settings.delivery.logisticsconfig/getlogisticsconfig',
                    'settings.delivery.logisticsconfig/setlogisticsconfig',
                ],
            ],
        ],
        //管理员
        'permissions'   => [
            'page_path'     => '/setting/permissions/admin',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['auth.admin/lists','auth.role/lists'],
            ],
            'manage'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'auth.admin/add',
                    'auth.admin/edit',
                    'auth.admin/detail',
                    'auth.admin/del',
                ],
            ],

        ],
        //角色
        'role'          => [
            'page_path'     => '/setting/permissions/role',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['auth.role/lists'],
            ],
            'manage'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => [
                    'auth.role/add',
                    'auth.role/edit',
                    'auth.role/detail',
                    'auth.role/getMenu',
                    'auth.role/del',
                ],
            ],
        ],
        //商品设置
        'goods'         => [
            'page_path'     => '/setting/goods/goods',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.goods.goodssettings/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.goods.goodssettings/setconfig'],
            ],
        ],
        //用户设置
        'user'          => [
            'page_path'     => '/setting/user/user_setting',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.user.user/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.user.user/setconfig'],
            ],
        ],
        //登录注册
        'loginregister' => [
            'page_path'     => '/setting/user/login_register',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.user.user/getregisterconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.user.user/setregisterconfig'],
            ],
        ],
        //用户提现
        'withdrawdeposit'   => [
            'page_path'     => '/setting/user/withdraw_deposit',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.user.user/getwithdrawconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.user.user/setwithdrawconfig'],
            ],
        ],
        //订单设置
        'order'             => [
            'page_path'     => '/setting/order/order',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.order.transaction_settings/getconfig'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.order.transaction_settings/setconfig'],
            ],
        ],
        //储存设置
//        'storage'           => [
//            'page_path'     => '/setting/storage/index',
//            'view'      => [
//                'button_auth'   => ['view'],
//                'action_auth'   => ['settings.shop.storage/lists'],
//            ],
//            'manage'      => [
//                'button_auth'   => ['auth_all'],
//                'action_auth'   => [
//                    'settings.order.storage/change',
//                    'settings.shop.storage/index',
//                    'settings.shop.storage/setup'
//                ],
//            ],
//        ],
        //系统日志
        'systemlog'     => [
            'page_path'     => '/setting/system_maintain/journal',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.system.log/lists'],
            ],
        ],
        //系统缓存
        'systemcache'     => [
            'page_path'     => '/setting/system_maintain/cache',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => [],
            ],
            'clear'     => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.system.cache/clear'],
            ],
        ],
        //系统更新
        'systemupdate'     => [
            'page_path'     => '/setting/system_maintain/update',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => [],
            ],
        ],
        //异常日志
        'errorlog'     => [
            'page_path'     => '/setting/system_maintain/error_journal',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => [],
            ],
        ],
        //计划任务
        'task'     => [
            'page_path'     => '/setting/task',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['crontab.crontab/lists'],
            ],
            'manage'    => [
                'button_auth'   => ['view'],
                'action_auth'   => [
                    'crontab.crontab/add',
                    'crontab.crontab/edit',
                    'crontab.crontab/operate',
                    'crontab.crontab/del',
                ],
            ],
        ],
        //地图配置
        'map'             => [
            'page_path'     => '/setting/map',
            'view'      => [
                'button_auth'   => ['view'],
                'action_auth'   => ['settings.shop.shop_setting/getMapKey'],
            ],
            'save'      => [
                'button_auth'   => ['auth_all'],
                'action_auth'   => ['settings.shop.shop_setting/setMapKey'],
            ],
        ],
    ],
];
