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

namespace app\platformapi\logic\shop;

use app\common\logic\BaseLogic;
use app\common\model\DecorateTheme;
use app\common\model\DecorateThemeConfig;
use app\common\model\DecorateThemePage;
use app\common\model\DistributionLevel;
use app\common\model\Footprint;
use app\common\model\Goods;
use app\common\model\GoodsCategory;
use app\common\model\GoodsSpec;
use app\common\model\GoodsSpecValue;
use app\common\model\NoticeSetting;
use app\common\model\PayConfig;
use app\common\model\PayWay;
use app\common\model\PcDecorateTheme;
use app\common\model\PcDecorateThemePage;
use app\common\model\SystemTheme;
use app\common\model\SystemThemeConfig;
use app\common\model\SystemThemePage;
use app\common\model\UserLevel;
use think\facade\Db;

/**
 * 商家初始化数据
 * Class ShopDefaultDataLogic
 * @package app\platformapi\logic\shop
 */
class ShopDefaultDataLogic extends BaseLogic
{
    protected $sid;             //商家
    protected $isDemo;          //是否需要演示数据
    protected $themeId;         //移动端主题
    protected $payWayArr;       //支付配置
    protected $pcThemeId;       //pc端主题
    protected $systemThemeId;   //系统主题
    protected $categoryId;      //商品分类



    public function __construct($sid,$isDemo = true)
    {
        $this->sid = $sid;
        $this->isDemo = $isDemo;
        $this->tablePrefix = env('DATABASE.PREFIX');
    }

    /**
     * @notes 生成默认数据
     * @author Tab
     * @date 2021/12/17 17:29
     */
    public function generateDefaultData()
    {
        //创建演示商品、商品分类
        $this->initDemo();
        // 装修主题
        $this->decorateThemeData();
        // 装修配置
        $this->decorateThemeConfigData();
        // 装修页面
        $this->decorateThemePageData();
        // 足迹配置
        $this->footprintData();
        // 消息通知配置
        $this->noticeSettingData();
        // 支付配置
        $this->payData();
        // 支付列表
        $this->payWayData();
        // 分销等级
        $this->distributionLevelData();
        // PC装修主题
        $this->pcDecorateThemeData();
        // PC装修页面
        $this->pcDecorateThemePageData();
        // 系统主题
        $this->systemThemeData();
        // 系统主题配置
        $this->systemThemeConfigData();
        // 系统主题页面
        $this->systemThemePageData();
        // 用户等级
        $this->userLevelData();
    }

    /**
     * @notes 默认装修主题
     * @author Tab
     * @date 2021/12/20 14:24
     */
    public function decorateThemeData()
    {
        $data = [
            'name' => '默认主题',
            'sid' => $this->sid
        ];
        $model = new DecorateTheme();
        $model->duokaiSave($data, true, true, false);
        $this->themeId = $model->id;
    }

    /**
     * @notes 装修配置数据
     * @throws \Exception
     * @author Tab
     * @date 2021/12/17 18:31
     */
    public function decorateThemeConfigData()
    {
        $data = [ // 商城风格
            [
                'theme_id' => $this->themeId,
                'type' => 1,
                'content' => '{"theme":"gold_theme"}',
                'sid' => $this->sid,
            ], // 底部导航
            [
                'theme_id' => $this->themeId,
                'type' => 2,
                'content' => '{
                    "tabbar":{
                        "content":{
                            "style":"1",
                            "data":[
                                {
                                    "icon":"resource/image/adminapi/theme/navigation/home.png",
                                    "select_icon":"resource/image/adminapi/theme/navigation/select_home.png",
                                    "name":"\u9996\u9875",
                                    "link":{"index":"1","name":"\u5546\u57ce\u9996\u9875","path":"\/pages\/index\/index","params":[],"type":"shop"}
                                },
                                {
                                    "icon":"resource/image/adminapi/theme/navigation/category.png",
                                    "select_icon":"resource/image/adminapi/theme/navigation/select_category.png",
                                    "name":"\u5206\u7c7b",
                                    "link":{"name":"\u5546\u54c1\u5206\u7c7b","path":"\/pages\/category\/category","params":[],"type":"shop"}
                                },
                                {
                                    "icon":"resource/image/adminapi/theme/navigation/cart.png",
                                    "select_icon":"resource/image/adminapi/theme/navigation/select_cart.png",
                                    "name":"\u8d2d\u7269\u8f66",
                                    "link":{"name":"\u8d2d\u7269\u8f66","path":"\/pages\/shop_cart\/shop_cart","params":[],"type":"shop"}
                                },
                                {
                                    "icon":"resource/image/adminapi/theme/navigation/centre.png",
                                    "select_icon":"resource/image/adminapi/theme/navigation/select_centre.png",
                                    "name":"\u4e2a\u4eba\u4e2d\u5fc3",
                                    "link":{"index":"4","name":"\u4e2a\u4eba\u4e2d\u5fc3","path":"\/pages\/user\/user","params":[],"type":"shop"}
                                }
                            ],
                            "color_type":"2"
                        },
                        "styles":{"background_color":"#FFFFFF","text_color":"#666666","text_select_color":"#FF2C3C","bg_color":"#FFFFFF","color":"#666666","select_color":"#FF2C3C"}
                    }
                }',
                'sid' => $this->sid,
            ]
        ];
        $model = new DecorateThemeConfig();
        $model->duokaiSaveAll($data, true, true, false);
    }

    /**
     * @notes 装修页面数据
     * @author Tab
     * @date 2021/12/17 18:31
     */
    public function decorateThemePageData()
    {
        $data = [
            [
                'theme_id' => $this->themeId,
                'name' => '商城首页',
                'is_home' => 1,
                'type' => 1,
                'content' => '[{"title":"搜索框","name":"search","show":1,"content":{"text":"请输入关键字搜索"},"styles":{"text_align":"left","border_radius":40,"root_bg_color":"","bg_color":"#FFFFFF","icon_color":"#999999","color":"#999999","padding_top":12,"padding_horizontal":15,"padding_bottom":12}},{"title":"轮播图","name":"banner","show":1,"content":{"data":[{"url":"resource/image/adminapi/theme/home/image1.png","link":[]},{"url":"resource/image/adminapi/theme/home/image2.png","link":[]}]},"styles":{"root_bg_color":"rgba(0,0,0,0)","border_radius":6,"indicator_style":2,"indicator_align":"center","indicator_color":"#FF2C3C","padding_top":0,"padding_horizontal":10,"padding_bottom":0}},{"title":"图片魔方","name":"rubik","show":1,"content":{"style":3,"data":[{"url":"resource/image/adminapi/theme/home/cube1.png","link":[]},{"url":"resource/image/adminapi/theme/home/cube2.png","link":[]},{"url":"resource/image/adminapi/theme/home/cube3.png","link":[]}]},"styles":{"border_radius":0,"root_bg_color":"","line_color":"#e5e5e5","font_color":"#333","padding_top":10,"padding_horizontal":10,"padding_bottom":10,"margin":10}}]',
                'common' => '{"title":"\u5546\u54c1\u8be6\u60c5","background_type":"0","bg_color":"#F5F5F5","background_image":""}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->themeId,
                'name' => '商品分类',
                'is_home' => 0,
                'type' => 2,
                'content' => '[{"title":"搜索框","name":"search","show":1,"operate":["hidden"],"content":{"text":"请输入关键字搜索"},"styles":{"text_align":"left","border_radius":40,"root_background_color":"rgba(255, 255, 255, 1)","background_color":"rgba(245, 245, 245, 1)","icon_color":"#999999","text_color":"#999999","padding_top":10,"padding_horizontal":15,"padding_bottom":10,"root_bg_color":"#FFFFFF","bg_color":"#F5F5F5","color":"#999999"}},{"title":"商品分类","name":"category","show":1,"operate":["hidden"],"content":{"data":[],"style":7},"styles":{"border_radius":10}}]',
                'common' => '{"title":"\u5206\u7c7b","background_type":"0","background_color":"#F5F5F5","background_image":"","bg_color":"rgba(63, 245, 215, 1)"}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->themeId,
                'name' => '个人中心',
                'is_home' => 0,
                'type' => 3,
                'content' => '[{"title":"会员信息","name":"userinfo","show":1,"operate":["hidden"],"content":{"style":1,"background_image":"","background_type":1,"avatar_type":2,"avatar":"","assets":[2,1,4,3],"show_user_sn":1,"show_member":1},"styles":[]},{"title":"我的订单","name":"userorder","show":1,"operate":["hidden"],"content":{"text":"我的订单","pay_icon":"resource/image/adminapi/theme/centre/pay.png","pay_name":"待付款","delivery_icon":"resource/image/adminapi/theme/centre/deliver.png","delivery_name":"待发货","take_icon":"resource/image/adminapi/theme/centre/take.png","take_name":"待收货","comment_icon":"resource/image/adminapi/theme/centre/evaluate.png","comment_name":"商品评价","sale_icon":"resource/image/adminapi/theme/centre/aftersale.png","sale_name":"售后"},"styles":{"root_bg_color":"","bg_color":"#FFFFFF","border_radius_top":0,"border_radius_bottom":0,"padding_top":10,"padding_horizontal":0,"padding_bottom":0}},{"title":"我的服务","name":"userserve","show":1,"operate":["hidden"],"content":{"text":"我的服务","data":[{"url":"resource/image/adminapi/theme/centre/distribution.png","name":"分销推广","link":{"index":17,"name":"分销推广","path":"/bundle/pages/user_spread/user_spread","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/address.png","name":"收货地址","link":{"index":7,"name":"收货地址","path":"/pages/address/address","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/service.png","name":"联系客服","link":{"index":18,"name":"在线客服","path":"/bundle/pages/service/service","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/notice.png","name":"消息中心","link":{"index":14,"name":"消息中心","path":"/bundle/pages/message_center/message_center","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/distribution.png","name":"邀请海报","link":{"index":21,"name":"分销海报","path":"/bundle/pages/invite_poster/invite_poster","params":[],"type":"shop"}}]},"styles":{"root_bg_color":"","bg_color":"#FFFFFF","border_radius_top":0,"border_radius_bottom":0,"padding_top":10,"padding_horizontal":0,"padding_bottom":0}},{"title":"为您推荐","name":"goodsrecom","show":1,"operate":["hidden"],"content":{"style":2,"header_title":"为您推荐","show_title":1,"show_price":1,"show_scribing_price":1,"show_btn":1,"btn_text":"购买","btn_bg_type":1,"data":[],"tips":"根据系统算法，推荐用户购买商品的同分类10款商品。优先推荐高销量且排序在前的商品。 如果用户没有购买商品，则按商品销量和排序进行推荐。"},"styles":{"header_title_color":"#333333","header_title_size":20,"title_color":"#101010","scribing_price_color":"#999999","price_color":"#FF2C3C","btn_bg_color":"#FF2C3C","btn_color":"#FFFFFF","btn_border_radius":30,"btn_border_color":"","root_bg_color":"","bg_color":"#FFFFFF","margin":10,"padding_top":10,"padding_horizontal":10,"padding_bottom":10,"border_radius_top":4,"border_radius_bottom":4,"margin_top":0}}]',
                'common' => '{"title":"\u4e2a\u4eba\u4e2d\u5fc3","background_type":"0","bg_color":"#F5F5F5","background_image":""}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->themeId,
                'name' => '购物车',
                'is_home' => 0,
                'type' => 4,
                'content' => '[{"title":"为您推荐","name":"goodsrecom","show":1,"operate":["hidden"],"content":{"style":2,"header_title":"为您推荐","show_title":1,"show_price":1,"show_scribing_price":1,"show_btn":1,"btn_text":"购买","btn_bg_type":1,"data":[],"tips":"根据系统算法，推荐购物车内同分类的10款商品。优先推荐高销量且排序在前的商品。"},"styles":{"header_title_color":"#333333","header_title_size":18,"title_color":"#101010","scribing_price_color":"#999999","price_color":"#FF2C3C","btn_bg_color":"#FF2C3C","btn_color":"#FFFFFF","btn_border_radius":30,"btn_border_color":"rgba(0, 0, 0, 0)","root_bg_color":"rgba(0, 0, 0, 0)","bg_color":"rgba(255, 255, 255, 1)","margin":7,"padding_top":0,"padding_horizontal":10,"padding_bottom":10,"border_radius_top":4,"border_radius_bottom":4,"content_bg_color":"rgba(255, 255, 255, 0)","margin_top":0}}]',
                'common' => '{"title":"\u8d2d\u7269\u8f66","background_type":"0","bg_color":"#F5F5F5","background_image":""}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->themeId,
                'name' => '商品详情',
                'is_home' => 0,
                'type' => 5,
                'content' => '[{"title":"商品评价","name":"reviews","show":1,"operate":["hidden"],"content":[],"styles":[]},{"title":"猜你喜欢","name":"goodsrecom","show":1,"operate":["hidden"],"content":{"style":3,"header_title":"猜你喜欢","show_title":1,"show_price":1,"show_scribing_price":1,"show_btn":1,"btn_text":"购买","btn_bg_type":2,"data":[],"tips":"根据系统算法，推荐同分类的9款商品。优先推荐高销量且排序在前的商品。"},"styles":{"header_title_color":"#333333","header_title_size":18,"title_color":"#101010","scribing_price_color":"#999999","price_color":"#FF2C3C","btn_bg_color":"#FF2C3C","btn_color":"#FFFFFF","btn_border_radius":30,"btn_border_color":"rgba(0, 0, 0, 0)","root_bg_color":"#FFFFFF","bg_color":"#FFFFFF","content_bg_color":"#FFFFFF","margin":10,"padding_top":10,"padding_horizontal":10,"padding_bottom":0,"border_radius_top":4,"border_radius_bottom":4,"margin_top":10}}]',
                'common' => '{"title":"\u5546\u54c1\u8be6\u60c5","background_type":"0","bg_color":"#F5F5F5","background_image":""}',
                'sid' => $this->sid
            ]
        ];
        $model = new DecorateThemePage();
        $model->duokaiSaveAll($data, true, true, false);
    }


    /**
     * @notes 足迹数据
     * @author Tab
     * @date 2021/12/17 18:45
     */
    public function footprintData()
    {
        $data = [
            [
                'type' => 1,
                'name' => '访问商城',
                'template' => '欢迎${user_name}访问商城',
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'type' => 2,
                'name' => '浏览商品',
                'template' => '${user_name}正在浏览${goods_name}',
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'type' => 3,
                'name' => '加入购物车',
                'template' => '${user_name}正在购买${goods_name}',
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'type' => 4,
                'name' => '领取优惠券',
                'template' => '${user_name}正在领取优惠券',
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'type' => 5,
                'name' => '下单结算',
                'template' => '${user_name}成功下单',
                'status' => 1,
                'sid' => $this->sid,
            ]
        ];
        $model = new Footprint();
        $model->duokaiSaveAll($data, true, true, false);

    }

    /**
     * @notes 消息配置
     * @author Tab
     * @date 2021/12/17 18:51
     */
    public function noticeSettingData()
    {
        $data = [
            [
                'scene_id' => 100,
                'scene_name' => '注册验证码',
                'scene_desc' => '用户注册时发送',
                'recipient' => 1,
                'type' => 2,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 101,
                'scene_name' => '登录验证码',
                'scene_desc' => '用户手机号码登录时发送',
                'recipient' => 1,
                'type' => 2,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 102,
                'scene_name' => '绑定手机验证码',
                'scene_desc' => '用户绑定手机号码时发送',
                'recipient' => 1,
                'type' => 2,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 103,
                'scene_name' => '变更手机验证码',
                'scene_desc' => '用户变更手机号码时发送',
                'recipient' => 1,
                'type' => 2,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 104,
                'scene_name' => '找回登录密码验证码',
                'scene_desc' => '用户找回登录密码号码时发送',
                'recipient' => 1,
                'type' => 2,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 105,
                'scene_name' => '找回支付密码验证码',
                'scene_desc' => '用户找回支付密码时发送',
                'recipient' => 1,
                'type' => 2,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 106,
                'scene_name' => '订单付款通知',
                'scene_desc' => '订单付款成功时通知买家',
                'recipient' => 1,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '1,2,3,4',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 107,
                'scene_name' => '订单发货通知',
                'scene_desc' => '卖家发货时通知买家',
                'recipient' => 1,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '1,2,3,4',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 108,
                'scene_name' => '售后退款拒绝通知',
                'scene_desc' => '卖家拒绝售后退款时通知买家',
                'recipient' => 1,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '1,2,3,4',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 109,
                'scene_name' => '售后退款成功通知',
                'scene_desc' => '售后退款金额到账时通知买家',
                'recipient' => 1,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '1,2,3,4',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 110,
                'scene_name' => '佣金入账通知',
                'scene_desc' => '佣金结算入账时通知用户',
                'recipient' => 1,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '1,2,3,4',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 200,
                'scene_name' => '订单付款通知',
                'scene_desc' => '买家订单付款成功时通知卖家',
                'recipient' => 2,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ],
            [
                'scene_id' => 201,
                'scene_name' => '售后退款申请通知',
                'scene_desc' => '买家发起售后退款申请时通知卖家',
                'recipient' => 2,
                'type' => 1,
                'system_notice' => '',
                'sms_notice' => '',
                'oa_notice' => '',
                'mnp_notice' => '',
                'support' => '2',
                'sid' => $this->sid,
            ]
        ];
        $model = new NoticeSetting();
        $model->duokaiSaveAll($data, true, true, false);
        return $this;
    }

    /**
     * @notes 支付配置
     * @throws \Exception
     * @author Tab
     * @date 2021/12/20 10:07
     */
    public function payData()
    {
        $data = [
            [
                'name' => '余额支付',
                'pay_way' => 1,
                'config' => '',
                'icon' => 'resource/image/adminapi/pay/balance.png',
                'sort' => 50,
                'remark' => '',
                'sid' => $this->sid,
            ],
            [
                'name' => '微信支付',
                'pay_way' => 2,
                'config' => '',
                'icon' => 'resource/image/adminapi/pay/wechat.png',
                'sort' => 100,
                'remark' => '',
                'sid' => $this->sid,
            ],
            [
                'name' => '支付宝支付',
                'pay_way' => 3,
                'config' => '',
                'icon' => 'resource/image/adminapi/pay/alipay.png',
                'sort' => 150,
                'remark' => '',
                'sid' => $this->sid,
            ],
            [
                'name' => '字节支付',
                'pay_way' => 4,
                'config' => '',
                'icon' => 'resource/image/adminapi/pay/bytepay.png',
                'sort' => 200,
                'remark' => '',
                'sid' => $this->sid,
            ],

        ];
        $model = new PayConfig();
        $result = $model->duokaiSaveAll($data, true, true, false);
        $result = array_column($result->toArray(), 'id', 'pay_way');
        $this->payWayArr = $result;
    }

    /**
     * @notes 支付方式列表
     * @author Tab
     * @date 2021/12/20 11:06
     */
    public function payWayData()
    {
        $data = [
            [
                'scene' => 1,
                'dev_pay_id' => $this->payWayArr[1],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 1,
                'dev_pay_id' => $this->payWayArr[2],
                'is_default' => 1,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 2,
                'dev_pay_id' => $this->payWayArr[1],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 2,
                'dev_pay_id' => $this->payWayArr[2],
                'is_default' => 1,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 3,
                'dev_pay_id' => $this->payWayArr[1],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 3,
                'dev_pay_id' => $this->payWayArr[2],
                'is_default' => 1,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 3,
                'dev_pay_id' => $this->payWayArr[3],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 4,
                'dev_pay_id' => $this->payWayArr[1],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 4,
                'dev_pay_id' => $this->payWayArr[2],
                'is_default' => 1,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 4,
                'dev_pay_id' => $this->payWayArr[3],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 5,
                'dev_pay_id' => $this->payWayArr[1],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 5,
                'dev_pay_id' => $this->payWayArr[2],
                'is_default' => 1,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 5,
                'dev_pay_id' => $this->payWayArr[3],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 7,
                'dev_pay_id' => $this->payWayArr[1],
                'is_default' => 1,
                'status' => 1,
                'sid' => $this->sid,
            ],
            [
                'scene' => 7,
                'dev_pay_id' => $this->payWayArr[4],
                'is_default' => 0,
                'status' => 1,
                'sid' => $this->sid,
            ],
        ];
        $model = new PayWay();
        $model->duokaiSaveAll($data, true, true, false);
    }

    /**
     * @notes 分销等级
     * @author Tab
     * @date 2021/12/20 11:13
     */
    public function distributionLevelData()
    {
        $data = [
            'name' => '默认等级',
            'weights' => 1,
            'first_ratio' => 0,
            'second_ratio' => 0,
            'third_ratio' => 0,
            'self_ratio' => 0,
            'is_default' => 1,
            'remark' => '',
            'update_relation' => 1,
            'sid' => $this->sid,
        ];
        $model = new DistributionLevel();
        $model->duokaiCreate($data, [], true, '', false);
    }

    /**
     * @notes PC装修主题
     * @author Tab
     * @date 2021/12/20 11:50
     */
    public function pcDecorateThemeData()
    {
        $data = [
            'name' => '默认主题',
            'sid' => $this->sid,
        ];
        $model = new PcDecorateTheme();
        $model->duokaiSave($data, null, true, false);
        return $this->pcThemeId = $model->id;
    }

    /**
     * @notes PC装修页面
     * @throws \Exception
     * @author Tab
     * @date 2021/12/20 12:00
     */
    public function pcDecorateThemePageData()
    {
        $data = [
            [
                'theme_id' => $this->pcThemeId,
                'name' => '首页',
                'type' => 1,
                'content' => '[{"title":"头部","name":"header","show":1,"operate":["hidden"],"forbid":true,"content":{"data":[{"url":"","name":"首页","link":{"index":1,"name":"商城首页","path":"/","params":[],"type":"shop"}},{"url":"","name":"限时秒杀","link":{"index":1,"name":"限时秒杀","path":"/seckill","params":[],"type":"marking"}},{"url":"","name":"领券中心","link":{"index":1,"name":"领券中心","path":"/get_coupons","params":[],"type":"marking"}},{"url":"","name":"商城公告","link":{"index":1,"name":"商城公告","path":"/news_list","params":[],"type":"shop"}}]},"style":[]},{"title":"轮播","name":"banner","show":1,"operate":["hidden"],"forbid":true,"content":{"data":[{"url":"resource/image/adminapi/theme/pc/banner1.png","link":[]},{"url":"resource/image/adminapi/theme/pc/banner2.png","link":[]}]},"style":[]},{"title":"底部","name":"footer","show":1,"operate":["hidden"],"forbid":true,"content":{"data":[{"url":"resource/image/adminapi/theme/pc/shop.png","name":"自营商城"},{"url":"resource/image/adminapi/theme/pc/delivery.png","name":"极速配送"},{"url":"resource/image/adminapi/theme/pc/exclusive_service.png","name":"专属客服"},{"url":"resource/image/adminapi/theme/pc/after_sale.png","name":"售后无忧"},{"url":"resource/image/adminapi/theme/pc/guarantee.png","name":"品质保障"}]},"style":[]},{"title":"固定导航","name":"fixed","show":1,"operate":["hidden"],"forbid":true,"content":{"style":1,"data":[{"type":"nav","icon":"resource/image/adminapi/theme/pc/cart.png","select_icon":"resource/image/adminapi/theme/pc/select_cart.png","name":"购物车","link":{"index":1,"name":"购物车","path":"/shop_cart","params":[],"type":"shop"}},{"icon":"resource/image/adminapi/theme/pc/order.png","select_icon":"resource/image/adminapi/theme/pc/select_order.png","name":"优惠券","type":"nav","link":{"index":1,"name":"我的优惠券","path":"/user/coupons","params":[],"type":"shop"}},{"icon":"resource/image/adminapi/theme/pc/collection.png","select_icon":"resource/image/adminapi/theme/pc/select_collection.png","name":"我的收藏","type":"nav","link":{"index":1,"name":"我的收藏","path":"/user/collection","params":[],"type":"shop"}},{"icon":"resource/image/adminapi/theme/pc/service.png","select_icon":"resource/image/adminapi/theme/pc/select_service.png","name":"联系客服","type":"server","link":[]}]},"style":[]},{"title":"广告位","name":"adv","show":1,"content":{"data":[{"url":"resource/image/adminapi/theme/pc/ad1.png","link":[]},{"url":"resource/image/adminapi/theme/pc/ad2.png","link":[]},{"url":"resource/image/adminapi/theme/pc/ad3.png","link":[]},{"url":"resource/image/adminapi/theme/pc/ad4.png ","link":[]}],"style":4},"styles":[]},{"title":"限时秒杀","name":"seckill","show":1,"content":{"data":[],"title":"限时秒杀","show_more":1,"data_type":1,"num":10},"styles":[]}]',
                'common' => [],
                'sid' => $this->sid,
            ],
            [
                'theme_id' => $this->pcThemeId,
                'name' => '登录',
                'type' => 2,
                'content' => '{"url":"resource/image/adminapi/theme/pc/ad_login.png","link":{},"name":""}',
                'common' => [],
                'sid' => $this->sid,
            ],
            [
                'theme_id' => $this->pcThemeId,
                'name' => '限时秒杀',
                'type' => 3,
                'content' => '{"url":"resource/image/adminapi/theme/pc/ad_seckill.png","link":{},"name":""}',
                'common' => [],
                'sid' => $this->sid,
            ],
            [
                'theme_id' => $this->pcThemeId,
                'name' => '领券中心',
                'type' => 4,
                'content' => '{"url":"resource/image/adminapi/theme/pc/ad_coupon.png","link":{},"name":""}',
                'common' => [],
                'sid' => $this->sid,
            ],
            [
                'theme_id' => $this->pcThemeId,
                'name' => '商城公告',
                'type' => 5,
                'content' => '{"url":"resource/image/adminapi/theme/pc/ad_notice.png","link":{},"name":""}',
                'common' => [],
                'sid' => $this->sid,
            ]
        ];
        $model = new PcDecorateThemePage();
        $model->duokaiSaveAll($data, true, true, false);
    }

    /**
     * @notes 系统主题
     * @author Tab
     * @date 2021/12/20 12:02
     */
    public function systemThemeData()
    {
        $data = [
            'name' => '系统模板',
            'image' => 'resource/image/adminapi/theme/system/default_home.png',
            'sid' => $this->sid,
        ];
        $model = new SystemTheme();
        $model->duokaiSave($data, null, true, false);
        $this->systemThemeId = $model->id;
    }

    /**
     * @notes 系统主题配置
     * @author Tab
     * @date 2021/12/20 12:06
     */
    public function systemThemeConfigData()
    {
        $data = [
            [
                'theme_id' => $this->systemThemeId,
                'type' => 1,
                'content' => '{"theme":"red_theme"}',
                'sid' => $this->sid,
            ]
        ];
        $model = new SystemThemeConfig();
        $model->duokaiSaveAll($data, true, true, false);
    }

    /**
     * @notes 系统主题页面
     * @author Tab
     * @date 2021/12/20 12:11
     */
    public function systemThemePageData()
    {
        $data = [
            [
                'theme_id' => $this->systemThemeId,
                'name' => '系统模板',
                'is_home' => 0,
                'type' => 1,
                'content' => '[
                    {
                        "title":"搜索框",
                        "name":"search",
                        "show":1,
                        "content":{"text":"请输入关键字搜索"},
                        "styles":{"text_align":"left","border_radius":40,"root_bg_color":"","bg_color":"#FFFFFF","icon_color":"#999999","color":"#999999","padding_top":12,"padding_horizontal":15,"padding_bottom":12}
                    },
                    {
                        "title":"轮播图",
                        "name":"banner",
                        "show":1,
                        "content":{
                            "data":[{"url":"resource/image/adminapi/theme/home/image1.png","link":[]},{"url":"resource/image/adminapi/theme/home/image2.png","link":[]}]
                        },
                        "styles":{"root_bg_color":"rgba(0,0,0,0)","border_radius":6,"indicator_style":2,"indicator_align":"center","indicator_color":"#FF2C3C","padding_top":0,"padding_horizontal":10,"padding_bottom":0}
                    },
                    {
                        "title":"图片魔方",
                        "name":"rubik",
                        "show":1,
                        "content":{"style":3,"data":[{"url":"resource/image/adminapi/theme/home/cube1.png","link":[]},{"url":"resource/image/adminapi/theme/home/cube2.png","link":[]},{"url":"resource/image/adminapi/theme/home/cube3.png","link":[]}]},
                        "styles":{"border_radius":0,"root_bg_color":"","line_color":"#e5e5e5","font_color":"#333","padding_top":10,"padding_horizontal":10,"padding_bottom":10,"margin":10}
                    }
                ]',
                'common' => '{"title":"\u9996\u9875","background_type":"2","bg_color":"#F5F5F5","background_image":"","background_color":"#F5F5F5"}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->systemThemeId,
                'name' => '商品分类',
                'is_home' => 0,
                'type' => 2,
                'content' => '[{"title":"搜索框","name":"search","show":1,"operate":["hidden"],"content":{"text":"请输入关键字搜索"},"styles":{"text_align":"left","border_radius":40,"root_background_color":"rgba(255, 255, 255, 1)","background_color":"rgba(245, 245, 245, 1)","icon_color":"#999999","text_color":"#999999","padding_top":10,"padding_horizontal":15,"padding_bottom":10,"root_bg_color":"#FFFFFF","bg_color":"#F5F5F5","color":"#999999"}},{"title":"商品分类","name":"category","show":1,"operate":["hidden"],"content":{"data":[],"style":7},"styles":{"border_radius":10}}]',
                'common' => '{"title":"\u5206\u7c7b","background_type":"0","background_color":"#F5F5F5","background_image":"","bg_color":"rgba(63, 245, 215, 1)"}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->systemThemeId,
                'name' => '个人中心',
                'is_home' => 0,
                'type' => 3,
                'content' => '[{"title":"会员信息","name":"userinfo","show":1,"operate":["hidden"],"content":{"style":1,"background_image":"","background_type":1,"avatar_type":2,"avatar":"","assets":[2,1,4,3],"show_user_sn":1,"show_member":1},"styles":[]},{"title":"我的订单","name":"userorder","show":1,"operate":["hidden"],"content":{"text":"我的订单","pay_icon":"resource/image/adminapi/theme/centre/pay.png","pay_name":"待付款","delivery_icon":"resource/image/adminapi/theme/centre/deliver.png","delivery_name":"待发货","take_icon":"resource/image/adminapi/theme/centre/take.png","take_name":"待收货","comment_icon":"resource/image/adminapi/theme/centre/evaluate.png","comment_name":"商品评价","sale_icon":"resource/image/adminapi/theme/centre/aftersale.png","sale_name":"售后"},"styles":{"root_bg_color":"","bg_color":"#FFFFFF","border_radius_top":0,"border_radius_bottom":0,"padding_top":10,"padding_horizontal":0,"padding_bottom":0}},{"title":"我的服务","name":"userserve","show":1,"operate":["hidden"],"content":{"text":"我的服务","data":[{"url":"resource/image/adminapi/theme/centre/distribution.png","name":"分销推广","link":{"index":17,"name":"分销推广","path":"/bundle/pages/user_spread/user_spread","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/address.png","name":"收货地址","link":{"index":7,"name":"收货地址","path":"/pages/address/address","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/service.png","name":"联系客服","link":{"index":18,"name":"在线客服","path":"/bundle/pages/service/service","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/notice.png","name":"消息中心","link":{"index":14,"name":"消息中心","path":"/bundle/pages/message_center/message_center","params":[],"type":"shop"}},{"url":"resource/image/adminapi/theme/centre/distribution.png","name":"邀请海报","link":{"index":21,"name":"分销海报","path":"/bundle/pages/invite_poster/invite_poster","params":[],"type":"shop"}}]},"styles":{"root_bg_color":"","bg_color":"#FFFFFF","border_radius_top":0,"border_radius_bottom":0,"padding_top":10,"padding_horizontal":0,"padding_bottom":0}},{"title":"为您推荐","name":"goodsrecom","show":1,"operate":["hidden"],"content":{"style":2,"header_title":"为您推荐","show_title":1,"show_price":1,"show_scribing_price":1,"show_btn":1,"btn_text":"购买","btn_bg_type":1,"data":[],"tips":"根据系统算法，推荐用户购买商品的同分类10款商品。优先推荐高销量且排序在前的商品。 如果用户没有购买商品，则按商品销量和排序进行推荐。"},"styles":{"header_title_color":"#333333","header_title_size":20,"title_color":"#101010","scribing_price_color":"#999999","price_color":"#FF2C3C","btn_bg_color":"#FF2C3C","btn_color":"#FFFFFF","btn_border_radius":30,"btn_border_color":"","root_bg_color":"","bg_color":"#FFFFFF","margin":10,"padding_top":10,"padding_horizontal":10,"padding_bottom":10,"border_radius_top":4,"border_radius_bottom":4,"margin_top":0}}]',
                'common' => '{"title":"\u4e2a\u4eba\u4e2d\u5fc3","background_type":"0","bg_color":"#F5F5F5","background_image":""}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->systemThemeId,
                'name' => '购物车',
                'is_home' => 0,
                'type' => 4,
                'content' => '[{"title":"为您推荐","name":"goodsrecom","show":1,"operate":["hidden"],"content":{"style":2,"header_title":"为您推荐","show_title":1,"show_price":1,"show_scribing_price":1,"show_btn":1,"btn_text":"购买","btn_bg_type":1,"data":[],"tips":"根据系统算法，推荐购物车内同分类的10款商品。优先推荐高销量且排序在前的商品。"},"styles":{"header_title_color":"#333333","header_title_size":18,"title_color":"#101010","scribing_price_color":"#999999","price_color":"#FF2C3C","btn_bg_color":"#FF2C3C","btn_color":"#FFFFFF","btn_border_radius":30,"btn_border_color":"rgba(0, 0, 0, 0)","root_bg_color":"rgba(0, 0, 0, 0)","bg_color":"rgba(255, 255, 255, 1)","margin":7,"padding_top":0,"padding_horizontal":10,"padding_bottom":10,"border_radius_top":4,"border_radius_bottom":4,"content_bg_color":"rgba(255, 255, 255, 0)","margin_top":0}}]',
                'common' => '{"title":"\u8d2d\u7269\u8f66","background_type":"0","bg_color":"#F5F5F5","background_image":""}',
                'sid' => $this->sid
            ],
            [
                'theme_id' => $this->systemThemeId,
                'name' => '商品详情',
                'is_home' => 0,
                'type' => 5,
                'content' => '[{"title":"商品评价","name":"reviews","show":1,"operate":["hidden"],"content":[],"styles":[]},{"title":"猜你喜欢","name":"goodsrecom","show":1,"operate":["hidden"],"content":{"style":3,"header_title":"猜你喜欢","show_title":1,"show_price":1,"show_scribing_price":1,"show_btn":1,"btn_text":"购买","btn_bg_type":2,"data":[],"tips":"根据系统算法，推荐同分类的9款商品。优先推荐高销量且排序在前的商品。"},"styles":{"header_title_color":"#333333","header_title_size":18,"title_color":"#101010","scribing_price_color":"#999999","price_color":"#FF2C3C","btn_bg_color":"#FF2C3C","btn_color":"#FFFFFF","btn_border_radius":30,"btn_border_color":"rgba(0, 0, 0, 0)","root_bg_color":"#FFFFFF","bg_color":"#FFFFFF","content_bg_color":"#FFFFFF","margin":10,"padding_top":10,"padding_horizontal":10,"padding_bottom":0,"border_radius_top":4,"border_radius_bottom":4,"margin_top":10}}]',
                'common' => '{"title":"\u9996\u9875","background_type":"2","bg_color":"#F5F5F5","background_image":"","background_color":"#F5F5F5"}',
                'sid' => $this->sid
            ]
        ];
        $model = new SystemThemePage();
        $model->duokaiSaveAll($data, true, true, false);
    }

    /**
     * @notes 用户等级
     * @author Tab
     * @date 2021/12/20 14:08
     */
    public function userLevelData()
    {
        $data = [
            [
                'name' => '默认等级',
                'rank' => 1,
                'image' => '/resource/image/adminapi/user/user_level_icon.png',
                'background_image' => '/resource/image/adminapi/user/user_level_bg.png',
                'remark' => '',
                'discount' => 0,
                'condition' => '',
                'sid' => $this->sid
            ]
        ];
        $model = new UserLevel();
        $model->duokaiSaveAll($data, true, true, false);
    }

    /**
     * @notes 初始化演示数据
     * @author cjhao
     * @date 2022/3/10 15:04
     */
    public function initDemo()
    {
        if (true !== $this->isDemo) {
            return false;
        }
        //初始化商品分类
        $this->initGoodsCategory();
        //初始化商品
        $this->initGoods();


    }


    /**
     * @notes 初始化商品分类
     * @author cjhao
     * @date 2022/3/10 17:50
     */
    public function initGoodsCategory()
    {

    }

    /**
     * @notes 初始化商品
     * @author cjhao
     * @date 2022/3/11 14:53
     */
    public function initGoods(){

    }


}