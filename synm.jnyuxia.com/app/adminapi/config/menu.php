<?php
/**
 * todo
 * name:菜单、权限名称
 * type：类型：1-菜单；2-权限
 * sons:子级菜单
 * ----auth_key：权限key(必须唯一)
 */
return [
    //首页
    [
        'name'  => '首页',
        'type'  => 1,
        'sons'  => [
            [
                'name'    => '首页',
                'type'    => 1,
                'sons'    => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'index/index.view'
                    ]
                ],
            ]
        ]

    ],
    //店铺
    [
        'name'  => '店铺',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '店铺主页',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/shopindex.view'
                    ],
                    [
                        'name'      => '编辑首页',
                        'type'      => 2,
                        'auth_key'  => 'theme/shopindex.edit'
                    ],
                ],
            ],
            [
                'name'  => '商品分类、商品详情、购物车、个人中心',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/decoratepages.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'theme/decoratepages.manage'
                    ]
                ],
            ],
            [
                'name'  => '页面管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/pages.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'theme/pages.manage'
                    ]
                ],
            ],
            [
                'name'  => '页面模板',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/template.view'
                    ],
                    [
                        'name'      => '启用模板',
                        'type'      => 2,
                        'auth_key'  => 'theme/template.open'
                    ]

                ],
            ],
            [
                'name'  => '商城风格、底部导航',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/content.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'theme/content.save'
                    ]

                ],
            ],
            [
                'name'  => '素材管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/material.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'theme/material.manage'
                    ]

                ],
            ],
            [
                'name'  => 'PC商城-首页装修/广告管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'theme/pcdecorate.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'theme/pcdecorate.manage'
                    ]

                ],
            ],
        ]
    ],
    //商品
    [
        'name'  => '商品',
        'type'  => 1,
        'sons'  => [
            [
                'name'    => '商品管理',
                'type'    => 1,
                'sons'    => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'goods/goods.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/goods.manage'
                    ]

                ],
            ],
            [
                'name'  => '分类管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'   => 'goods/category.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/category.manage'
                    ]
                ]
            ],
            [
                'name'  => '品牌管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'goods/brand.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/brand.manage'
                    ]
                ],
            ],
            [
                'name'  => '商品单位',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'goods/unit.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/unit.manage'
                    ]

                ],
            ],
            [
                'name'  => '供应商管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'goods/supplier.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/supplier.manage'
                    ]

                ],
            ],
            [
                'name'  => '供应商分类',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'goods/suppliercategory.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/suppliercategory.manage'
                    ]
                ],
            ],
            [
                'name'  => '评价管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'goods/comment.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'goods/comment.manage'
                    ]
                ],
            ],
        ],
    ],
    //订单
    [
        'name'  => '订单',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '订单管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'order/order.view'
                    ],
                    [
                        'name'      => '取消订单',
                        'type'      => 2,
                        'auth_key'  => 'order/order.cancel',
                    ],
                    [
                        'name'      => '修改价格',
                        'type'      => 2,
                        'auth_key'  => 'order/order.changeprice',
                    ],
                    [
                        'name'      => '修改运费',
                        'type'      => 2,
                        'auth_key'  => 'order/order.changeexpressprice',
                    ],
                    [
                        'name'      => '修改地址、发货',
                        'type'      => 2,
                        'auth_key'  => 'order/order.addressdelivery',
                    ],
                    [
                        'name'      => '确认收货',
                        'type'      => 2,
                        'auth_key'  => 'order/order.confirm',
                    ],
                    [
                        'name'      => '其他管理',
                        'type'      => 2,
                        'auth_key'  => 'order/order.manage'
                    ]

                ],
            ],
            [
                'name'  => '售后订单',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'order/aftersales.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'order/aftersales.manage'
                    ]

                ],
            ],
        ]
    ],
    //用户
    [
        'name'  => '用户',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '用户概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'user/profile.view'
                    ]
                ],
            ],
            [
                'name'  => '用户管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'user/list.view'
                    ],
                    [
                        'name'      => '调整用户账户',
                        'type'      => 2,
                        'auth_key'  => 'user/list.adjustaccount'
                    ],
                    [
                        'name'      => '其他管理',
                        'type'      => 2,
                        'auth_key'  => 'user/list.manage'
                    ],
                ],
            ],
            [
                'name'  => '用户等级',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'user/userlevel.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'user/userlevel.manage'
                    ]

                ],
            ],
            [
                'name'  => '用户标签',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'user/userlabel.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'user/userlabel.manage'
                    ]
                ],
            ],
        ]
    ],
    //营销
    [
        'name'  => '营销',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '营销中心',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/marketing.view'
                    ],
                ],
            ],
            [
                'name'  => '应用中心',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/marketingapp.view'
                    ],
                ],
            ],
            //优惠券模块
            [
                'name'  => '优惠券概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/couponsurvey.view'
                    ],
                ],
            ],
            [
                'name'  => '优惠券',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/couponlists.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/couponlists.manage'
                    ],
                ],
            ],
            [
                'name'  => '领取记录',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/couponrecord.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/couponrecord.manage'
                    ],
                ],
            ],
            //限时秒杀
            [
                'name'  => '秒杀活动',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/seckilllists.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/seckilllists.manage'
                    ],
                ],
            ],
            //拼团模块
            [
                'name'  => '拼团概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/combinationsurvey.view'
                    ],
                ],
            ],
            [
                'name'  => '拼团活动',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/combinationlists.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/combinationlists.manage'
                    ],
                ],
            ],
            //砍价模块
            [
                'name'  => '砍价活动',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/bargainlists.view'
                    ],
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/bargainlists.manage'
                    ],
                ],
            ],
            [
                'name'  => '砍价记录',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/bargainrecord.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/bargainrecord.stop'
                    ],
                ],
            ],
            [
                'name'  => '会员折扣',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/discountlevel.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/discountlevel.manage'
                    ],
                ],
            ],
            //分销模块
            [
                'name'  => '分销概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionsurvey.view'
                    ],
                ],
            ],
            [
                'name'  => '分销商',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionstore.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionstore.manage'
                    ],
                ],
            ],
            [
                'name'  => '分销商申请',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionapplystore.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionapplystore.manage'
                    ],
                ],
            ],
            [
                'name'  => '分销商品',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributiongoods.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributiongoods.manage'
                    ],
                ],
            ],
            [
                'name'  => '分销订单',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionorder.view'
                    ],
//                    [
//                        'name'      => '管理',
//                        'type'      => 2,
//                        'auth_key'  => 'marketing/distributionorder.manage'
//                    ],
                ],
            ],
            [
                'name'  => '分销等级',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributiongrade.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributiongrade.manage'
                    ],
                ],
            ],
            [
                'name'  => '分销设置-基础设置、结算设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionconfig.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/distributionconfig.save'
                    ],
                ],
            ],
            //用户储值
            [
                'name'  => '充值概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/rechargesurvey.view'
                    ],
                ],
            ],
            [
                'name'  => '充值规则',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/rechargerule.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/rechargerule.save'
                    ],
                ],
            ],
            [
                'name'  => '充值记录',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/rechargerecord.view'
                    ]
                ],
            ],
            //商城公告
            [
                'name'  => '商城公告',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/shopnotice.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/shopnotice.manage'
                    ],
                ],
            ],
            //每日签到
            [
                'name'  => '签到概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/calendarsurvey.view'
                    ],
                ],
            ],
            [
                'name'  => '签到规则',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/calendarrule.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/calendarrule.save'
                    ],
                ],
            ],
            [
                'name'  => '签到记录',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/calendarrecord.view'
                    ]
                ],
            ],
            //幸运抽奖
            [

                'name'  => '幸运抽奖',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/luckydraw.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/luckydraw.manage'
                    ],
                ],
            ],
            //门店自提
            [
                'name'  => '核销订单',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/selffetchorder.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/selffetchorder.manage'
                    ],
                ],
            ],
            [
                'name'  => '核销门店',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/selffetchshop.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/selffetchshop.manage'
                    ],
                ],
            ],
            [
                'name'  => '核销员',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/selffetchverifier.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/selffetchverifier.manage'
                    ],
                ],
            ],
            //小程序直播
            [
                'name'  => '直播间',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/livebroadcastlists.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/livebroadcastlists.manage'
                    ],
                ],
            ],
            [
                'name'  => '直播商品',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/livebroadcastgoods.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/livebroadcastgoods.manage'
                    ],
                ],
            ],
            //信息通知
            [
                'name'  => '业务通知、验证码、卖家通知',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/notice.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/notice.manage'
                    ],
                ],
            ],
            [
                'name'  => '短信设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/smssetting.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/smssetting.manage'
                    ],
                ],
            ],
            //在线客服
            [
                'name'  => '在线客服',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/service.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/service.save'
                    ],
                ],
            ],
            //客服列表
            [
                'name'  => '客服列表',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/servicelists.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/servicelists.manage'
                    ],
                ],
            ],
            //客服话术
            [
                'name'  => '客服话术',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/speechlists.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/speechlists.manage'
                    ],
                ],
            ],
            //快递助手
            [
                'name'  => '批量打印',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/expressassistant.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/expressassistant.manage'
                    ],
                ],
            ],
            [
                'name'  => '发件人模板',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/facesheetsender.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/facesheetsender.manage'
                    ],
                ],
            ],
            [
                'name'  => '面单模板',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/facesheettemplate.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/facesheettemplate.manage'
                    ],
                ],
            ],
            [
                'name'  => '面单设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/facesheetsetting.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/facesheetsetting.manage'
                    ],
                ],
            ],
            //小票打印
            [
                'name'  => '打印机管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/print.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/print.manage'
                    ],
                ],
            ],
            [
                'name'  => '模板管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/printtemplate.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/printtemplate.manage'
                    ],
                ],
            ],
            //评价助手
            [
                'name'  => '评价助手',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/evaluation.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/evaluation.manage'
                    ],
                ],
            ],
            //商城资讯
            [
                'name'  => '资讯管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/article.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/article.manage'
                    ],
                ],
            ],
            [
                'name'  => '资讯分类',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/articlecategory.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/articlecategory.manage'
                    ],
                ],
            ],
            // 积分商城
            [
                'name'  => '积分商品',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/integralgoods.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/integralgoods.save'
                    ],
                ],
            ],
            [
                'name'  => '兑换订单',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/integralorder.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/integralorder.save'
                    ],
                ],
            ],
            // 消费奖励
            [
                'name'  => '消费奖励',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/awardintegral.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'marketing/awardintegral.save'
                    ],
                ],
            ],
            // 包邮活动
            [
                'name'  => '包邮活动',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/freeshipping.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/freeshipping.manage'
                    ],
                ],
            ],
            // 足迹气泡
            [
                'name'  => '足迹气泡',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/footprint.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/footprint.manage'
                    ],
                ],
            ],
            // 自定义海报
            [
                'name'  => '自定义海报',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/poster.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/poster.manage'
                    ],
                ],
            ],
            // 商品采集
            [
                'name'  => '商品采集',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'marketing/gather.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'marketing/gather.manage'
                    ],
                ],
            ],
        ]
    ],
    //财务
    [
        'name'  => '财务',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '财务概览',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'finance/profile.view'
                    ],
                ],
            ],
            [
                'name'  => '提现记录',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'finance/userwithdrawal.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'finance/userwithdrawal.manage'
                    ],
                ],
            ],
            [
                'name'  => '余额明细',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'finance/accountlog.view'
                    ]
                ],
            ],
            [
                'name'  => '积分明细',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'finance/integrallog.view'
                    ]
                ],
            ],
            [
                'name'  => '佣金明细',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'finance/commissionlog.view'
                    ]
                ],
            ],

        ]
    ],
    //数据
    [
        'name'  => '数据',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '流量分析',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'data/flowanalysis.view'
                    ],
                ],
            ],
            [
                'name'  => '用户分析',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'data/user.view'
                    ],
                ],
            ],
            [
                'name'  => '交易分析',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'data/transaction.view'
                    ],
                ],
            ],
            [
                'name'  => '商品分析',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'data/goods.view'
                    ],
                ],
            ],
            [
                'name'  => '商品排行',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'data/goodsrank.view'
                    ],
                ],
            ],
        ]
    ],
    //渠道
    [
        'name'  => '渠道',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '微信公众号-渠道设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/oasetting.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/oasetting.save'
                    ],
                ],
            ],
            [
                'name'  => '微信公众号-菜单管理',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/oamenu.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/oamenu.save'
                    ],
                ],
            ],
            [
                'name'  => '微信公众号-关注回复、关键词回复、默认回复',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/oafollowreply.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/oafollowreply.manage'
                    ],
                ],
            ],
            [
                'name'  => '小程序-设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/mpsetting.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/mpsetting.save'
                    ],

                ],
            ],
            [
                'name'  => 'APP-设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/appsetting.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/appsetting.save'
                    ],

                ],
            ],
            [
                'name'  => 'H5-设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/h5setting.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/h5setting.save'
                    ],

                ],
            ],
            [
                'name'  => '头条小程序-小程序设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/mptoutiao.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/mptoutiao.save'
                    ],

                ],
            ],
            [
                'name'  => 'PC商城-渠道设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'channel/pcstore.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'channel/pcstore.save'
                    ],

                ],
            ]
        ]
    ],
    //设置
    [
        'name'  => '设置',
        'type'  => 1,
        'sons'=>[
            [
                'name'  => '店铺信息',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/shop.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/shop.save'
                    ],
                ],
            ],
            [
                'name'  => '版权资质',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/record.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/record.save'
                    ],
                ],
            ],
            [
                'name'  => '分享设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/share.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/share.save'
                    ],
                ],
            ],
            [
                'name'  => '政策协议',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/protocol.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/protocol.save'
                    ],
                ],
            ],
            [
                'name'  => '支付方式',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/paymethod.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'setting/paymethod.save'
                    ],
                ],
            ],
            [
                'name'  => '支付配置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/payconfig.view'
                    ],
                    [
                        'name'      => '编辑',
                        'type'      => 2,
                        'auth_key'  => 'setting/payconfig.edit'
                    ],
                ],
            ],
            [
                'name'  => '配送方式',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/delivery.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'setting/delivery.manage'
                    ],
                ],
            ],
            [
                'name'  => '管理员',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/permissions.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'setting/permissions.manage'
                    ],
                ],
            ],
            [
                'name'  => '角色',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/role.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'setting/role.manage'
                    ],
                ]
            ],
            [
                'name'  => '商品设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/goods.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/goods.save'
                    ],
                ],
            ],
            [
                'name'  => '用户设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/user.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/user.save'
                    ],
                ],
            ],
            [
                'name'  => '登录注册',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/loginregister.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/loginregister.save'
                    ],
                ],
            ],
            [
                'name'  => '用户提现',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/withdrawdeposit.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/withdrawdeposit.save'
                    ],
                ],
            ],
            [
                'name'  => '交易设置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/order.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/order.save'
                    ],
                ],
            ],
//            [
//                'name'  => '储存设置',
//                'type'  => 1,
//                'sons'  => [
//                    [
//                        'name'      => '查看',
//                        'type'      => 2,
//                        'auth_key'  => 'setting/storage.view'
//                    ],
//                    [
//                        'name'      => '管理',
//                        'type'      => 2,
//                        'auth_key'  => 'setting/storage.manage'
//                    ],
//                ],
//            ],
            [
                'name'  => '系统日志',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/systemlog.view'
                    ],
                ],
            ],
            [
                'name'  => '系统缓存',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/systemcache.view'
                    ],
                    [
                        'name'      => '清除系统缓存',
                        'type'      => 2,
                        'auth_key'  => 'setting/systemcache.clear'
                    ],
                ],
            ],
//            [
//                'name'  => '系统更新',
//                'type'  => 1,
//                'sons'  => [
//                    [
//                        'name'      => '查看',
//                        'type'      => 2,
//                        'auth_key'  => 'setting/systemupdate.view'
//                    ],
//                ],
//            ],
//            [
//                'name'  => '异常日志',
//                'type'  => 1,
//                'sons'  => [
//                    [
//                        'name'      => '查看',
//                        'type'      => 2,
//                        'auth_key'  => 'setting/errorlog.view'
//                    ],
//                ],
//            ],
            [
                'name'  => '定时任务',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/task.view'
                    ],
                    [
                        'name'      => '管理',
                        'type'      => 2,
                        'auth_key'  => 'setting/task.manage'
                    ],
                ],
            ],
            [
                'name'  => '地图配置',
                'type'  => 1,
                'sons'  => [
                    [
                        'name'      => '查看',
                        'type'      => 2,
                        'auth_key'  => 'setting/map.view'
                    ],
                    [
                        'name'      => '保存',
                        'type'      => 2,
                        'auth_key'  => 'setting/map.save'
                    ],
                ],
            ],
        ]
    ]
];



