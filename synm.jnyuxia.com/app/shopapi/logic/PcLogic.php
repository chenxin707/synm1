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
namespace app\shopapi\logic;

use app\common\enum\AfterSaleEnum;
use app\common\enum\CouponEnum;
use app\common\enum\GoodsEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PcDecorateThemePageEnum;
use app\common\enum\ShopPageEnum;
use app\common\logic\BaseLogic;
use app\common\model\Cart;
use app\common\model\CouponList;
use app\common\model\Goods;
use app\common\model\GoodsCollect;
use app\common\model\GoodsComment;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\PcDecorateThemePage;
use app\common\model\SeckillActivity;
use app\common\model\SeckillGoodsItem;
use app\common\model\ShopNotice;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\ThemeService;
use app\common\service\WeChatService;
use app\shopapi\lists\GoodsCommentLists;

/**
 * Pc商城
 */
class PcLogic extends BaseLogic
{
    /**
     * @notes 公共数据
     * @author Tab
     * @date 2021/11/29 16:18
     */
    public static function commonData($params)
    {
        $res = WeChatService::makeMpQrCode(['page' =>substr(ShopPageEnum::HOME_PAGE['mobile'],1)],'base64');
        $mpBase64 = '';
        if(true == $res){
            $mpBase64 = WeChatService::getReturnData();
        }
        $data['shop'] = [
            'name'                      => ConfigService::get('shop', 'name'),
            'logo'                      => ConfigService::get('shop', 'pc_logo'),
            'copyright'                 => ConfigService::get('shop', 'copyright', ''),
            'record_number'             => ConfigService::get('shop', 'record_number', ''),
            'record_system_link'        => ConfigService::get('shop', 'record_system_link', ''),
            'service_agreement_content' => ConfigService::get('shop', 'service_agreement_content', ''),
            'is_mobile_register_code'   => ConfigService::get('config', 'is_mobile_register_code',  config('project.login.is_mobile_register_code')),
            //oss域名
            'oss_domain'                => FileService::getFileUrl(),
            'oa_uri'                    => request()->domain().'/mobile'.ShopPageEnum::HOME_PAGE['mobile'],
            'mp_base64'                 => $mpBase64,
            'login_ad' => '',
            'seckill_ad' => '',
            'notice_ad' => '',
            // 小程序商城关闭状态
            'mnp_status'                => ConfigService::get('shop', 'status', 1),
            // H5商城关闭状态
            'h5_status'                 => ConfigService::get('h5', 'status', 1),
            // PC商城关闭状态
            'pc_status'                 => ConfigService::get('pc', 'status', 1),
            // 腾讯地图秘钥
            'tencent_map_key' => ConfigService::get('map', 'tencent_map_key',''),
        ];
        $data['shop']['logo'] = FileService::getFileUrl($data['shop']['logo']);
        $data['shop']['login_ad'] = FileService::getFileUrl($data['shop']['login_ad']);
        $data['shop']['seckill_ad'] = FileService::getFileUrl($data['shop']['seckill_ad']);
        $data['shop']['notice_ad'] = FileService::getFileUrl($data['shop']['notice_ad']);

        // pc
        $shopName = ConfigService::get('shop', 'name');
        $data['pc'] = [
            'title' => ConfigService::get('pc', 'title', $shopName),
            'ico' => ConfigService::get('pc', 'ico'),
            'description' => ConfigService::get('pc', 'description', ''),
            'keywords' => ConfigService::get('pc', 'keywords', ''),
            'tools_code' => ConfigService::get('pc', 'tools_code', ''),
        ];
        $data['pc']['ico'] = empty($data['pc']['ico']) ? '' : FileService::getFileUrl($data['pc']['ico']);
        $data['my'] = [
            'cart' => 0,
            'order' => 0,
            'collect' => 0,
            'user_id' => 0,
            'nickname' => '',
            'avatar' => '',
            'coupon' => '',
        ];
        if ($params['user_id']) {
            $data['my']['user_id'] = $params['user_id'];
            $data['my']['cart'] = Cart::where('user_id', $params['user_id'])->count('item_id');
            $data['my']['order'] = Order::where('user_id',$params['user_id'])->count('id');
            $data['my']['collect'] = GoodsCollect::where('user_id',$params['user_id'])->count('goods_id');
            $data['my']['nickname'] = $params['user_info']['nickname'];
            $data['my']['avatar'] = empty($params['user_info']['avatar']) ? '' : FileService::getFileUrl($params['user_info']['avatar']);
            $data['my']['coupon'] = CouponList::where([
                ['user_id', '=', $params['user_id']],
                ['status', '=', CouponEnum::USE_STATUS_NOT],
                ['invalid_time', '>=', time()]
            ])->count();
        }

        // 公告(最近3条)
        $notices = ShopNotice::field('id, name')->order([
            'sort' => 'desc',
            'id' => 'desc',
        ])->limit(3)->select()->toArray();
        $data['notices'] = $notices;

        //装修数据
        $page = PcDecorateThemePage::where(['type'=>PcDecorateThemePageEnum::TYPE_HOME])
            ->field('content,common')->find()->toArray();

        $moduleList = array_column($page['content'],'name');
        $headerIndex = array_search('header',$moduleList);
        $footerIndex = array_search('footer',$moduleList);
        $fixedIndex = array_search('fixed',$moduleList);

        $data['decoration']['header'] = $page['content'][$headerIndex] ?? [];
        $data['decoration']['footer'] = $page['content'][$footerIndex] ?? [];
        $data['decoration']['fixed']  = $page['content'][$fixedIndex] ?? [];

        return $data;
    }

    /**
     * @notes 获取商品详情
     * @author Tab
     * @date 2021/11/30 11:15
     */
    public static function goodsDetail($params)
    {
        $params['id'] = $params['goods_id'];
        $goodsDetail = (new GoodsLogic())->detail($params);

        // 秒杀商品处理
        if (isset($params['seckill_id']) && $params['seckill_id']) {
            self::seckillHandler($goodsDetail, $params);
        }

        // 添加店铺推荐商品
        $goodsDetail['recommend'] = self::recommendGoods();

        $goodsDetail['buy_num'] = 0;//已购买数量
        $goodsDetail['cart_goods_num'] = 0;
        if($params['user_id']) {
            //用户下单数量
            $goodsDetail['buy_num'] = OrderGoods::withoutGlobalScope()
                ->aliasSid('og')
                ->alias('og')
                ->join('order o', 'o.id = og.order_id')
                ->where(['og.goods_id'=>$params['goods_id'],'o.order_status'=>[OrderEnum::STATUS_WAIT_PAY,OrderEnum::STATUS_WAIT_DELIVERY,OrderEnum::STATUS_WAIT_RECEIVE,OrderEnum::STATUS_FINISH],'o.user_id'=>$params['user_id'],'o.order_type'=>[OrderEnum::NORMAL_ORDER,OrderEnum::VIRTUAL_ORDER]])
                ->sum('og.goods_num');

            //购物车商品数量
            $goodsDetail['cart_goods_num'] = Cart::where(['goods_id'=>$params['goods_id'],'user_id'=>$params['user_id']])->sum('goods_num');
        }

        return $goodsDetail;
    }

    /**
     * @notes 秒杀处理
     * @param $goodsDetail
     * @param $params
     * @author Tab
     * @date 2021/12/1 16:25
     */
    public static function seckillHandler(&$goodsDetail, $params)
    {
        // 秒杀活动信息
        $activity = SeckillActivity::findOrEmpty($params['seckill_id']);
        if ($activity->isEmpty()) {
            return false;
        }
        $goodsDetail['seckill'] = [
            'id' => $activity->id,
            'name' => $activity->name,
            'end_time' => $activity->end_time,
        ];

        // 替换秒杀价
        $items = SeckillGoodsItem::where([
            'seckill_id' => $params['seckill_id'],
            'goods_id' => $params['goods_id'],
        ])->column('seckill_id,goods_id,item_id,sell_price,seckill_price', 'item_id');

        foreach ($goodsDetail['spec_value_list'] as $key => $value) {
            $goodsDetail['spec_value_list'][$key]['sell_price'] = isset($items[$value['id']]) ? $items[$value['id']]['seckill_price'] : $goodsDetail['spec_value_list'][$key]['sell_price'];
        }
    }

    /**
     * @notes 店铺推荐商品
     * @author Tab
     * @date 2021/11/30 11:43
     */
    public static function recommendGoods()
    {
        $field = [
            'id',
            'name',
            'image',
            'min_price as sell_price',
            'min_lineation_price as lineation_price',
        ];
        $where = [
            'status' => GoodsEnum::STATUS_SELL
        ];
        $order = [
            'sales_num' => 'desc',
            'click_num' => 'desc',
            'id' => 'desc',
        ];
        $goodsLists = Goods::field($field)
            ->where($where)
            ->order($order)
            ->limit(5)
            ->select()
            ->toArray();
        
        $showPrice = ConfigService::get('goods_set', 'show_price', 1);
        if(0 == $showPrice){
            foreach ($goodsLists as $key => $goods){
                $goodsLists[$key]['lineation_price'] = 0;
            }
        }

        return $goodsLists;
    }

    /**
     * @notes 获取商品评论
     * @param $params
     * @author Tab
     * @date 2021/11/30 11:57
     */
    public static function goodsCommentCategory($params)
    {
        $data =  (new GoodsCommentLogic())->commentCategory($params);
        return $data;
    }

    /**
     * @notes 设置用户信息
     * @param $params
     * @author Tab
     * @date 2021/12/2 16:49
     */
    public static function setUserInfo($params)
    {
        try {
            $user = User::findOrEmpty($params['user_id']);
            if ($user->isEmpty()) {
                throw new \Exception('用户不存在');
            }
            $user->nickname = $params['nickname'];
            $user->sex = $params['sex'];
            $user->duokaiSave();
            return true;
        } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 售后申请页信息
     * @param $params
     * @author Tab
     * @date 2021/12/3 15:12
     */
    public static function afterSaleApplyPage($params)
    {
        $field = [
            'id',
            'goods_snap',
            'goods_price',
            'goods_num',
            'total_pay_price',
        ];
        $detail = OrderGoods::field($field)
            ->where('id', $params['order_goods_id'])
            ->findOrEmpty();
        if ($detail->isEmpty()) {
            return [];
        }
        $detail = $detail->toArray();
        $detail['reason'] = AfterSaleEnum::getReason('', true);
        return $detail;
    }


    /**
     * @notes 获取页面
     * @param int $type
     * @return PcDecorateThemePage|array|\think\Model|null
     * @author cjhao
     * @date 2021/12/6 18:53
     */
    public static function getPage(int $type)
    {

        $page = PcDecorateThemePage::where(['type'=>$type])->field('content,common')->find()->toArray();
        if(PcDecorateThemePageEnum::TYPE_HOME == $type){
            $page['content'] = ThemeService::getPCModuleData($page['content']);
        }
        return $page;

    }
}