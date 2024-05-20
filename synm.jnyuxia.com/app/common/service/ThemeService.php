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
namespace app\common\service;
use app\common\{enum\ActivityEnum,
    enum\GoodsEnum,
    enum\LiveEnum,
    enum\SeckillEnum,
    enum\TeamEnum,
    model\Cart,
    model\CouponList,
    model\Goods,
    model\Order,
    model\Coupon,
    enum\CouponEnum,
    model\SeckillActivity,
    model\SeckillGoodsItem,
    model\ShopNotice,
    enum\ThemePageEnum,
    model\GoodsCategoryIndex,
    model\TeamActivity,
    model\TeamGoodsItem};
use function JmesPath\search;


/**
 * 主题功能类
 * Class ThemeService
 * @package app\common\service
 */
class ThemeService
{
    public static array $params = [];
    /**
     * @notes 替换组件内容
     * @param array $content
     * @return array
     * @author cjhao
     * @date 2021/8/19 18:52
     */
    public static function getModuleData(array $content,array $config = []):array
    {
        self::$params = $config;
        $isDistribution = self::$params['is_distribution'] ?? false;  //是否分销
        $isVerifier     = self::$params['is_verifier'] ?? false;      //是否核销员
        $source         = self::$params['source'] ?? 'shop';          //后台组件替换或商城组件替换
        $userId         = self::$params['user_id'] ?? '';

        $moduleList = array_column($content,'name');
        foreach ($moduleList as $moduleKey => $moduleName) {

            //需要拼接数据的组件
            switch ($moduleName) {
                //商品组件
                case ThemePageEnum::GOODS:
                    $goods_type = $content[$moduleKey]['content']['goods_type'] ?? 2;
                    //商品分类
                    $limit = false;
                    if(2 == $goods_type){
                        if('admin' == $source){
                            $content[$moduleKey]['content']['data'] = [];
                            break;
                        }
                        $categoryId = $content[$moduleKey]['content']['category']['id'] ?? 0;
                        $limit = $content[$moduleKey]['content']['category']['num'] ?? false;
                        $goodsIds = GoodsCategoryIndex::where(['category_id'=>$categoryId])->column('goods_id');

                    }else{

                        $goodsIds = array_column($content[$moduleKey]['content']['data'], 'id');
                    }
                    //如果id都是空，直接返回数组
                    if (empty($goodsIds)) {
                        $content[$moduleKey]['content']['data'] = [];
                        break;
                    }

                    //todo 商品需要根据顺序排序
                    $orderField = implode(',', $goodsIds);
                    $goodsList = Goods::where(['id' => $goodsIds,'status'=>GoodsEnum::STATUS_SELL])
                        ->field('id,name,image,virtual_sales_num+sales_num as sales_num,min_price as sell_price,min_lineation_price as lineation_price')
                        ->orderRaw("field(id,$orderField)")
                        ->limit($limit)
                        ->order("sort desc")
                        ->select()
                        ->toArray();
                    //是否显示划线加
                    $showPrice = ConfigService::get('goods_set', 'show_price', 1);
                    if(0 == $showPrice){
                        foreach ($goodsList as $goodsKey => $goodsVal){
                            $goodsList[$goodsKey]['lineation_price'] = 0;
                        }
                    }

                    $content[$moduleKey]['content']['data'] = $goodsList;
                    break;
                //选项卡组件 todo 选项卡的data是多维数据
                case ThemePageEnum::TABS:
                    $dataList = $content[$moduleKey]['content']['data'];

                    foreach ($dataList as $dataKey => $dataVal){
                        $goods_type = $dataVal['goods_type'] ?? 1;
                        $limit = false;
                        if(2 == $goods_type){
                            if('admin' == $source){
                                $content[$moduleKey]['content']['data'][$dataKey]['data'] = [];
                                break;
                            }

                            $categoryId = $dataVal['category']['id'] ?? 0;
                            $limit = $content[$moduleKey]['content']['category']['num'] ?? false;
                            $goodsIds = GoodsCategoryIndex::where(['category_id'=>$categoryId])->column('goods_id');

                        }else{

                            $goodsIds = array_column($dataVal['data'], 'id');
                        }

                        //如果id都是空，直接返回数组
                        if (empty($goodsIds)) {
                            $content[$moduleKey]['content']['data'][$dataKey]['data'] = [];
                            break;
                        }
                        //todo 商品需要根据顺序排序
                        $orderField = implode(',', $goodsIds);

                        $goodsList = Goods::where(['id' => $goodsIds,'status'=>GoodsEnum::STATUS_SELL])
                            ->field('id,name,image,virtual_sales_num+sales_num as sales_num,min_price as sell_price,min_lineation_price as lineation_price')
                            ->orderRaw("field(id,$orderField)")
                            ->limit($limit)
                            ->select()
                            ->toArray();
                        //是否显示划线加
                        $showPrice = ConfigService::get('goods_set', 'show_price', 1);
                        if(0 == $showPrice){
                            foreach ($goodsList as $goodsKey => $goodsVal){
                                $goodsList[$goodsKey]['lineation_price'] = 0;
                            }
                        }

                        $content[$moduleKey]['content']['data'][$dataKey]['data'] = $goodsList;

                    }
                    break;
                //优惠券组件
                case ThemePageEnum::COUPON:
                    $couponIds = array_column($content[$moduleKey]['content']['data'], 'id');
                    //如果id都是空，直接返回数组
                    if (empty($couponIds)) {
                        $content[$moduleKey]['content']['data']  = [];
                        break;
                    }

                    //todo 优惠券需要根据顺序排序
                    $orderField = implode(',', $couponIds);
                    $couponList = Coupon::where(['id' => $couponIds,'status'=>[CouponEnum::COUPON_STATUS_NOT,CouponEnum::COUPON_STATUS_CONDUCT]])
                        ->field('id,name,money,condition_type,get_num_type,condition_money,use_goods_type,get_num,status')
                        ->orderRaw("field(id,$orderField)")
                        ->select();

                    $myCouponIds = [];
                    if($couponList && $userId){
                        $myCouponIds = CouponList::where(['user_id'=>$userId,'status'=>CouponEnum::USE_STATUS_NOT])
                            ->column('coupon_id');
                    }

                    $list = [];
                    foreach ($couponList as $coupon){

                        if(CouponEnum::USE_GOODS_TYPE_NOT == $coupon->use_goods_type){
                            $coupon->use_type = '全场通用';
                        }else{
                            $coupon->use_type = '指定商品可用';
                        }
                        $coupon->discount_content =
                            $coupon->condition_type == CouponEnum::CONDITION_TYPE_NOT
                                ? '无门槛, ' . '减' . $coupon->money . '元'
                                : '订单满' . $coupon->condition_money . '元, ' . '减' . $coupon->money . '元';
                        $coupon->status_desc = CouponEnum::getCouponStatusDesc($coupon->status);
                        $coupon->condition = 1 == $coupon->condition_type ? '无金额门槛' : '满' . $coupon->condition_money . '使用';
                        //是否已领取
                        $isReceive = 0;
                        if(in_array($coupon->id,$myCouponIds)){
                            $isReceive = 1;
                        }
                        $isAvailable = 0;
                        //是否可领取
                        switch ($coupon->get_num_type) {
                            case CouponEnum::GET_NUM_TYPE_LIMIT:
                                $total = CouponList::where(['coupon_id' => $coupon->id])
                                    ->where(['user_id' => $userId])
                                    ->count();
                                $isAvailable = $total >= $coupon->get_num ? 1 : 0;
                                break;
                            case CouponEnum::GET_NUM_TYPE_DAY:
                                $total = CouponList::where(['coupon_id' => $coupon->id])
                                    ->where(['user_id' =>$userId])
                                    ->where('create_time', '>=', TimeService::today()[0])
                                    ->where('create_time', '<=', TimeService::today()[1])
                                    ->count();
                                $isAvailable = $total >= $coupon->get_num ? 1 : 0;
                                break;
                        }

                        $list[]  = [
                            'id'                => $coupon->id,
                            'name'              => $coupon->name,
                            'is_receive'        => $isReceive,
                            'is_available'      => $isAvailable,
                            'use_type'          => $coupon->use_type,
                            'money'             => $coupon->money,
                            'discount_content'  => $coupon->discount_content,
                            'condition'         => $coupon->condition,
                        ];
                    }

                    $content[$moduleKey]['content']['data'] = $list;
                    break;
                //公告组件
                case ThemePageEnum::NOTICE:
                    $limit = $content[$moduleKey]['content']['num'] ?? 1;//默认拿一条
                    $noticeList = ShopNotice::field('id,name')
                                ->limit($limit)
                                ->where(['status'=>1])
                                ->order('sort asc,id desc')
                                ->select()
                                ->toArray();
                    $content[$moduleKey]['content']['data'] = $noticeList;
                    break;
                //会员中心-我的服务组件、微页面-导航组件
                case ThemePageEnum::USERSERVE:
                case ThemePageEnum::NAVIGATION:
                    if('admin' == $source){
                        break;
                    }
                    $linksData = $content[$moduleKey]['content']['data'];
                    foreach ($linksData as $linkKey => $link){
                        // 非分销用户，屏蔽分销菜单，index == 17时分销菜单，
                        if(false === $isDistribution && isset($link['link']['index']) && 17 == $link['link']['index']){
                            unset($linksData[$linkKey]);
                        }
                        //非核销用户，屏蔽分销菜单
                        if(false === $isVerifier && isset($link['link']['index']) && 19 == $link['link']['index']){
                            unset($linksData[$linkKey]);
                        }
                    }
                    $content[$moduleKey]['content']['data'] = array_values($linksData);
                    break;
                //商品拼团
                case ThemePageEnum::SPELLGROUP:
                    if('admin' == $source){
                        break;
                    }
                    $dataType = $content[$moduleKey]['content']['data_type'];
                    $data = $content[$moduleKey]['content']['data'];
                    $limit = false;
                    $where = [];
                    if(1 == $dataType) {
                        //获取数量
                        $limit = $content[$moduleKey]['content']['num'];
                        $orderField = 'TA.id desc';

                    }else{
                        if(empty($data)){
                            break;
                        }
                        $goods_ids = array_column($data,'goods_id');
                        $where = ['goods_id'=>$goods_ids];
                        $goodsIds = implode(',',array_column($data,'goods_id'));
                        $orderField = "field(goods_id,$goodsIds)";
                    }

                    $goodsList = TeamActivity::withoutGlobalScope()
                        ->aliasSid('TA')
                        ->alias('TA')
                        ->join('team_goods TG','TA.id = TG.team_id')
                        ->where([
                            ['status','=',TeamEnum::TEAM_STATUS_CONDUCT],
                            ['start_time', '<=', time()],
                            ['end_time', '>=', time()],
                        ])
                        ->where($where)
                        ->field('TA.id as activity_id,TG.id,goods_id,goods_snap,min_team_price,people_num,TG.virtual_sales_num,TG.virtual_click_num')
                        ->limit($limit)
                        ->orderRaw($orderField)
                        ->select()
                        ->toArray();

                    if(empty($goodsList)){
                        $content[$moduleKey]['content']['data'] = [];
                        break;
                    }

                    $activityIds = array_column($goodsList,'activity_id');
                    $goodsIds = array_column($goodsList,'goods_id');
                    $salesList = TeamGoodsItem::where(['team_id'=>$activityIds,'goods_id'=>$goodsIds])
                        ->group('goods_id')
                        ->column('sum(sales_volume)','goods_id');

                    $data = [];
                    foreach ($goodsList as $goods){
                        $goods_snap = json_decode($goods['goods_snap'],true);
                        $data[] = [
                            'id'                => $goods['id'],
                            'activity_id'       => $goods['activity_id'],
                            'activity_type'     => ActivityEnum::TEAM,
                            'goods_id'          => $goods['goods_id'],
                            'name'              => $goods_snap['name'],
                            'image'             => FileService::getFileUrl($goods_snap['image']),
                            'people_num'        => $goods['people_num'],
                            'sell_price'        => $goods_snap['min_price'],
                            'activity_price'    => $goods['min_team_price'],
                            'activity_sales'    => ($salesList[$goods['goods_id']] ?? 0) + $goods['virtual_sales_num'],
                        ];
                    }
                    $content[$moduleKey]['content']['data'] = $data;

                    break;
                //商品秒杀
                case ThemePageEnum::SECKILL:
                    if('admin' == $source){
                        break;
                    }
                    $dataType = $content[$moduleKey]['content']['data_type'];
                    $data = $content[$moduleKey]['content']['data'];
                    $limit = false;
                    $where = [];
                    //自动获取商品
                    if(1 == $dataType) {
                        //获取数量
                        $limit = $content[$moduleKey]['content']['num'];
                        $orderField = 'SA.id desc';

                    }else{
                        if(empty($data)){
                            break;
                        }
                        $goods_ids = array_column($data,'goods_id');
                        $where = ['goods_id'=>$goods_ids];
                        $goodsIds = implode(',',array_column($data,'goods_id'));
                        $orderField = "field(goods_id,$goodsIds)";
                    }

                    $goodsList = SeckillActivity::withoutGlobalScope()
                        ->aliasSid('SA')
                        ->alias('SA')
                        ->join('seckill_goods SG','SA.id = SG.seckill_id and SG.delete_time is null')
                        ->where([
                            ['status','=',SeckillEnum::SECKILL_STATUS_CONDUCT],
                            ['start_time', '<=', time()],
                            ['end_time', '>=', time()],
                        ])
                        ->where($where)
                        ->field('SA.id as activity_id,SG.id,goods_id,goods_snap,min_seckill_price,SG.virtual_sales_num,SG.virtual_click_num')
                        ->limit($limit)
                        ->orderRaw($orderField)
                        ->select()
                        ->toArray();
                    if(empty($goodsList)){
                        $content[$moduleKey]['content']['data'] = [];
                        break;
                    }

                    //销量
                    $activityIds = array_column($goodsList,'activity_id');
                    $goodsIds = array_column($goodsList,'goods_id');
                    $salesList = SeckillGoodsItem::where(['seckill_id'=>$activityIds,'goods_id'=>$goodsIds])
                                ->group('goods_id')
                                ->column('sum(sales_volume)','goods_id');

                    $data = [];
                    foreach ($goodsList as $goods){
                        $goodsSnap = json_decode($goods['goods_snap'],true);
                        $data[] = [
                            'id'                => $goods['id'],
                            'activity_id'       => $goods['activity_id'],
                            'activity_type'     => ActivityEnum::SECKILL,
                            'goods_id'          => $goods['goods_id'],
                            'name'              => $goodsSnap['name'],
                            'image'             => FileService::getFileUrl($goodsSnap['image']),
                            'sell_price'        => $goodsSnap['min_price'],
                            'activity_price'    => $goods['min_seckill_price'],
                            'activity_sales'    => ($salesList[$goods['goods_id']] ?? 0) + $goods['virtual_sales_num'],
                        ];
                    }
                    $content[$moduleKey]['content']['data'] = $data;
                    break;
                //商品推荐
                case ThemePageEnum::GOODSRECOM:
                    $show = $content[$moduleKey]['show'];

                    if('admin' == $source || 0 == $show){
                        break;
                    }
                    $content[$moduleKey]['content']['data'] = self::recommend();
                    break;
                //小程序直播
                case ThemePageEnum::MNPLIVE:
                    if('admin' == $source){
                        break;
                    }
                    $num = $content[$moduleKey]['content']['num'] ?? 1;
                    $result = WeChatService::getLiveRoom(0,$num);
                    if (!is_array($result)) {
                        break;
                    }
                    $data = [];
                    foreach ($result['room_info'] as $item) {
                        $data[] = [
                            'name' => $item['name'],
                            'room_id' => $item['roomid'],
                            'cover_img' => $item['cover_img'],
                            'anchor_name' => $item['anchor_name'],
                            'status' => $item['live_status'],
                            'live_status' => LiveEnum::getLiveStatus($item['live_status']),
                            'goods' => count($item['goods']),
                            'start_time' => date('Y-m-d H:i:s', $item['start_time']),
                            'end_time' => date('Y-m-d H:i:s', $item['end_time'])
                        ];
                    }
                    $content[$moduleKey]['content']['data'] = $data;
                    break;
            }


        }

        return $content;

    }




    public static function getPCModuleData(array $content,array $config = []):array
    {
        self::$params = $config;
        $isDistribution = self::$params['is_distribution'] ?? false;  //是否分销
        $isVerifier     = self::$params['is_verifier'] ?? false;      //是否核销员
        $source         = self::$params['source'] ?? 'shop';          //后台组件替换或商城组件替换
        $userId         = self::$params['user_id'] ?? '';

        $moduleList = array_column($content,'name');

        foreach ($moduleList as $moduleKey => $moduleName){
            //需要拼接数据的组件
            switch ($moduleName) {
                case ThemePageEnum::GOODS:
                    $goods_type = $content[$moduleKey]['content']['goods_type'] ?? 2;
                    //商品分类
                    $limit = false;
                    if(2 == $goods_type){
                        if('admin' == $source){
                            $content[$moduleKey]['content']['data'] = [];
                            break;
                        }
                        $categoryId = $content[$moduleKey]['content']['category']['id'] ?? 0;
                        $limit = $content[$moduleKey]['content']['category']['num'] ?? false;
                        $goodsIds = GoodsCategoryIndex::where(['category_id'=>$categoryId])->column('goods_id');

                    }else{

                        $goodsIds = array_column($content[$moduleKey]['content']['data'], 'id');
                    }
                    //如果id都是空，直接返回数组
                    if (empty($goodsIds)) {
                        $content[$moduleKey]['content']['data'] = [];
                        break;
                    }

                    //todo 商品需要根据顺序排序
                    $orderField = implode(',', $goodsIds);
                    $goodsList = Goods::where(['id' => $goodsIds,'status'=>GoodsEnum::STATUS_SELL])
                        ->field('id,name,image,virtual_sales_num+sales_num as sales_num,min_price as sell_price,min_lineation_price as lineation_price')
                        ->orderRaw("field(id,$orderField)")
                        ->limit($limit)
                        ->select()
                        ->toArray();
                    //是否显示划线加
                    $showPrice = ConfigService::get('goods_set', 'show_price', 1);
                    if(0 == $showPrice){
                        foreach ($goodsList as $goodsKey => $goodsVal){
                            $goodsList[$goodsKey]['lineation_price'] = 0;
                        }
                    }


                    $content[$moduleKey]['content']['data'] = $goodsList;
                    break;
                case ThemePageEnum::SECKILL:
                    if('admin' == $source){
                        break;
                    }
                    $dataType = $content[$moduleKey]['content']['data_type'];
                    $data = $content[$moduleKey]['content']['data'];
                    $limit = false;
                    $where = [];
                    //自动获取商品
                    if(1 == $dataType) {
                        //获取数量
                        $limit = $content[$moduleKey]['content']['num'];
                        $orderField = 'SA.id desc';

                    }else{
                        if(empty($data)){
                            break;
                        }
                        $goods_ids = array_column($data,'goods_id');
                        $where = ['goods_id'=>$goods_ids];
                        $goodsIds = implode(',',array_column($data,'goods_id'));
                        $orderField = "field(goods_id,$goodsIds)";
                    }

                    $goodsList = SeckillActivity::withoutGlobalScope()
                        ->aliasSid('SA')
                        ->alias('SA')
                        ->join('seckill_goods SG','SA.id = SG.seckill_id  and SG.delete_time is null')
                        ->where([
                            ['status','=',SeckillEnum::SECKILL_STATUS_CONDUCT],
                            ['start_time', '<=', time()],
                            ['end_time', '>=', time()],
                        ])
                        ->where($where)
                        ->field('SA.id as activity_id,SG.id,goods_id,goods_snap,min_seckill_price')
                        ->limit($limit)
                        ->orderRaw($orderField)
                        ->select()
                        ->toArray();

                    if(empty($goodsList)){
                        $content[$moduleKey]['content']['data'] = [];
                        break;
                    }

                    //销量
                    $activityIds = array_column($goodsList,'activity_id');
                    $goodsIds = array_column($goodsList,'goods_id');
                    $salesList = SeckillGoodsItem::where(['seckill_id'=>$activityIds,'goods_id'=>$goodsIds])
                        ->group('goods_id')
                        ->column('sum(sales_volume)','goods_id');

                    $data = [];
                    foreach ($goodsList as $goods){
                        $goodsSnap = json_decode($goods['goods_snap'],true);
                        $data[] = [
                            'id'                => $goods['id'],
                            'activity_id'       => $goods['activity_id'],
                            'activity_type'     => ActivityEnum::SECKILL,
                            'goods_id'          => $goods['goods_id'],
                            'name'              => $goodsSnap['name'],
                            'image'             => FileService::getFileUrl($goodsSnap['image']),
                            'sell_price'        => $goodsSnap['min_price'],
                            'activity_price'    => $goods['min_seckill_price'],
                            'activity_sales'    => $salesList[$goods['goods_id']] ?? 0,
                        ];
                    }
                    $content[$moduleKey]['content']['data'] = $data;
                    break;
            }
        }
        return $content;
    }


    /**
     * @notes 获取推荐商品
     * @param string $orderRaw 推荐商品排序；默认按销量和排序，传空则随机排序
     * @return array $type 页面类型
     * @return array $goodsIds 排除的商品id
     * @author cjhao
     * @date 2021/8/23 11:07
     */
    public static function recommend(string $orderRaw = 'virtual_sales_num+sales_num desc,sort desc',int $type=0,array $goodsIds = []):array
    {
        $pageType       = self::$params['page_type'] ?? $type;           //页面类型
        $goodsList      = [];
        $categoryIds    = [];
        $notGoodsIds    = self::$params['goods_id'] ?? $goodsIds;
        //如果传空，则使用随机排序
        if(empty($orderRaw)){
            $orderRaw = 'rand()';
        }
        //获取推荐商品：
        switch ($pageType){
            case ThemePageEnum::TYPE_GOODS_DETAIL://商品详情推荐商品

                $goodsId = self::$params['goods_id'] ?? '';
                $categoryIds = GoodsCategoryIndex::where(['goods_id'=>$goodsId])->column('category_id');

                break;
            case ThemePageEnum::TYPE_CART: //购物车推荐商品

                $userId = self::$params['user_id'] ?? '';
                //购物车商品的分类
                $cartList = Cart::withoutGlobalScope()
                    ->aliasSid('C')
                    ->alias('C')
                    ->join('goods_category_index GCI','C.goods_id = GCI.goods_id')
                    ->where(['user_id'=>$userId])
                    ->column('C.goods_id,category_id');

                $notGoodsIds = array_unique(array_column($cartList,'goods_id'));
                $categoryIds = array_unique(array_column($cartList,'category_id'));

                break;
            case ThemePageEnum::TYPE_MEMBER_CENTRE://个人中心推荐商品

                $userId = self::$params['user_id'] ?? '';
                //订单商品的分类
                $orderList = Order::withoutGlobalScope()
                    ->aliasSid('O')
                    ->alias('O')
                    ->join('order_goods OG','O.id = OG.order_id')
                    ->join('goods_category_index GCI','OG.goods_id = GCI.goods_id')
                    ->where(['user_id'=>$userId])
                    ->column('OG.goods_id,category_id');
                $notGoodsIds = array_unique(array_column($orderList,'goods_id'));
                $categoryIds = array_unique(array_column($orderList,'category_id'));
                break;
        }

        if($categoryIds){
            $where[] = ['status','=',GoodsEnum::STATUS_SELL];
            $where[] = ['category_id','in',$categoryIds];
            if($notGoodsIds){
                $where[] = ['G.id','not in',$notGoodsIds];
            }
            //找到推荐商品
            $goodsList = Goods::withoutGlobalScope()
                ->aliasSid('G')
                ->alias('G')
                ->join('goods_category_index GCI','G.id = GCI.goods_id')
                ->where($where)
                ->field('G.id,name,image,min_price as sell_price,min_lineation_price as lineation_price')
                ->group("G.id")
                ->orderRaw($orderRaw)
                ->limit(9)
                ->select()->toarray();
            //是否显示划线加
            $showPrice = ConfigService::get('goods_set', 'show_price', 1);
            if(0 == $showPrice){
                foreach ($goodsList as $goodsKey => $goodsVal){
                    $goodsList[$goodsKey]['lineation_price'] = 0;
                }
            }
        }
        return $goodsList;
    }
}