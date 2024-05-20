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

namespace app\adminapi\logic\bargain;

use app\adminapi\validate\bargain\BargainActivityValidate;
use app\common\enum\ActivityEnum;
use app\common\enum\BargainEnum;
use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\BargainActivity;
use app\common\model\BargainGoods;
use app\common\model\BargainHelp;
use app\common\model\BargainInitiate;
use app\common\model\GoodsActivity;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\service\FileService;
use think\facade\Db;

/**
 * 砍价逻辑层
 * Class BargainActivityLogic
 * @package app\adminapi\logic\bargain
 */
class BargainActivityLogic extends BaseLogic
{
    /**
     * @notes 选择商品
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/8/26 16:55
     */
    public static function chooseGoods($params)
    {
        $where = [
            ['g.id', 'in', $params['goods_ids']]
        ];
        $field = [
            'g.id' => 'goods_id',
            'g.image' => 'goods_image',
            'g.name' => 'goods_name',
            'gi.id' => 'item_id',
            'gi.spec_value_str',
            'gi.sell_price'
        ];
        $lists = GoodsItem::withoutGlobalScope()
            ->aliasSid('gi')
            ->alias('gi')
            ->leftJoin('goods g', 'g.id = gi.goods_id')
            ->field($field)
            ->where($where)
            ->order('g.id', 'desc')
            ->select()
            ->toArray();

        // 格式化数据
        $data = [];
        foreach($lists as &$item) {
            $item['goods_image'] = FileService::getFileUrl($item['goods_image']);
            $data[$item['goods_id']]['goods_id'] = $item['goods_id'];
            $data[$item['goods_id']]['goods_name'] = $item['goods_name'];
            $data[$item['goods_id']]['goods_image'] = $item['goods_image'];
            $item['first_knife'] = 0;
            $item['floor_price'] = 0;
            unset($item['goods_name']);
            unset($item['goods_image']);
            $data[$item['goods_id']]['items'][] = $item;
        }
        $data = array_values($data);
        return $data;
    }

    /**
     * @notes 添加砍价活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/27 11:24
     */
    public static function add($params)
    {
        Db::startTrans();
        try {
            // 添加砍价活动记录
            $activityId = self::addBarginActivity($params);

            // 添加砍价商品
            $params['activity_id'] = $activityId;
            self::addBarginGoods($params);

            // 添加商品活动信息
            self::addGoodsActivity($params);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            Db::rollback();
            return false;
        }
    }

    /**
     * @notes 添加砍价活动记录
     * @param $params
     * @author Tab
     * @date 2021/8/27 11:24
     */
    public static function addBarginActivity($params)
    {
        $bargainActivity = new BargainActivity();
        $params['sn'] = generate_sn($bargainActivity, 'sn');
        $bargainActivity->duokaiSave($params);
        return $bargainActivity->id;
    }

    /**
     * @notes 添加砍价商品
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/8/27 11:30
     */
    public static function addBarginGoods($params)
    {
        $goods = self::formatData($params);
        $bargainGoods = new BargainGoods();
        $bargainGoods->duokaiSaveAll($goods);
    }

    /**
     * @notes 添加商品活动信息
     * @param $params
     * @author Tab
     * @date 2021/10/11 16:53
     */
    public static function addGoodsActivity($params)
    {
        $goods = self::formatData($params);
        foreach ($goods as &$good) {
            unset($good['first_knife']);
            unset($good['floor_price']);
            $good['activity_type'] = ActivityEnum::BARGAIN;
        }
        $goodsActivity = new GoodsActivity();
        $goodsActivity->duokaiSaveAll($goods);
    }

    /**
     * @notes 格式化数据
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/8/27 11:27
     */
    public static function formatData($params)
    {
        $data = [];
        foreach($params['goods'] as $item) {
            foreach ($item['items'] as $subItem) {
                if (empty($subItem['first_knife']) || empty($subItem['floor_price']) || $subItem['first_knife'] <=0 || $subItem['floor_price'] <= 0) {
                    throw new \Exception('首刀金额和底价均不能为空且须大于0');
                }
                // 获取规格原价
                $itemPrice = GoodsItem::where('id', $subItem['item_id'])->value('sell_price');
                if ($itemPrice <= $subItem['floor_price']) {
                    throw new \Exception('底价须低于原价');
                }
                $subItem['goods_id'] = $item['goods_id'];
                $subItem['activity_id'] = $params['activity_id'];
                $subItem['virtual_click_num'] = $item['virtual_click_num'] ?? 0;
                $subItem['virtual_sales_num'] = $item['virtual_sales_num'] ?? 0;
                $data[] = $subItem;
            }
        }

        return $data;
    }

    /**
     * @notes 查看砍价活动详情
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/8/27 15:02
     */
    public static function detail($params)
    {
        // 砍价活动规则数据
        $bargainActivity = BargainActivity::withoutField('sn,create_time,update_time,delete_time')
            ->append(['status_desc'])
            ->findOrEmpty($params['id'])->toArray();
        if (empty($bargainActivity)) {
            return [];
        }

        $field = [
            'bg.activity_id',
            'bg.goods_id',
            'bg.item_id',
            'bg.first_knife',
            'bg.floor_price',
            'bg.virtual_sales_num',
            'bg.virtual_click_num',
            'g.name' => 'goods_name',
            'g.image' => 'goods_image',
            'g.spec_type',
            'g.total_stock',
            'gi.id',
            'gi.spec_value_str',
            'gi.sell_price',
            'gi.stock',
        ];
        // 砍价活动商品数据
        $bargainGoods = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods g', 'g.id = bg.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = bg.item_id')
            ->field($field)
            ->where('activity_id', $params['id'])->select()->toArray();

        // 格式化数据
        $data = [];
        foreach($bargainGoods as &$item) {
            $item['goods_image'] = FileService::getFileUrl($item['goods_image']);
            $data[$item['goods_id']]['id'] = $item['goods_id'];
            $data[$item['goods_id']]['name'] = $item['goods_name'];
            $data[$item['goods_id']]['image'] = $item['goods_image'];
            $data[$item['goods_id']]['spec_type'] = $item['spec_type'];
            $data[$item['goods_id']]['total_stock'] = $item['total_stock'];
            $data[$item['goods_id']]['virtual_sales_num'] = $item['virtual_sales_num'];
            $data[$item['goods_id']]['virtual_click_num'] = $item['virtual_click_num'];
            if (isset($data[$item['goods_id']]['sell_price'])) {
                $data[$item['goods_id']]['sell_price'] = min($data[$item['goods_id']]['sell_price'] = $item['sell_price'], $item['sell_price']);
            } else {
                $data[$item['goods_id']]['sell_price'] = $item['sell_price'];
            }
            unset($item['goods_name']);
            unset($item['goods_image']);
            unset($item['total_stock']);
            $data[$item['goods_id']]['item'][] = $item;
        }
        $bargainActivity['goods'] = array_values($data);
        return $bargainActivity;
    }

    /**
     * @notes 编辑砍价活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/27 16:16
     */
    public static function edit($params)
    {
        Db::startTrans();
        try {
            $bargainActivity = BargainActivity::findOrEmpty($params['id'])->toArray();
            if (empty($bargainActivity)) {
                throw new \Exception('砍价活动不存在');
            }
            if ($bargainActivity['status'] == BargainEnum::ACTIVITY_STATUS_END) {
                throw new \Exception('砍价活动已结束');
            }
            $params = request()->post();
            // 未开始的砍价活动
            if ($bargainActivity['status'] == BargainEnum::ACTIVITY_STATUS_WAIT) {
                validate(BargainActivityValidate::class)
                    ->scene('editWait')
                    ->check($params);

                self::editWait($params);
            }
            // 进行中的砍价活动
            if ($bargainActivity['status'] == BargainEnum::ACTIVITY_STATUS_ING) {
                validate(BargainActivityValidate::class)
                    ->scene('editIng')
                    ->check($params);

                self::editIng($params);
            }

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 编辑未开始的砍价活动
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/8/27 16:41
     */
    public static function editWait($params)
    {
        // 更新砍价活动表
        $allowField = ['name','start_time','end_time','remark','is_distribution','buy_condition','valid_period','help_num','knife_amount_type','self','count','buy_limit','order_limit','use_coupon'];
        self::updateBargainActivity($allowField, $params);
        // 更新砍价商品表
        self::updateBargainGoods($params);
    }

    /**
     * @notes 编辑进行中的砍价活动
     * @author Tab
     * @date 2021/8/27 16:43
     */
    public static function editIng($params)
    {
        $allowField = ['name', 'end_time', 'remark'];
        self::updateBargainActivity($allowField, $params);
    }

    /**
     * @notes 更新砍价活动表
     * @param $allowField
     * @param $params
     * @author Tab
     * @date 2021/8/27 16:36
     */
    public static function updateBargainActivity($allowField,$params)
    {
        BargainActivity::duokaiUpdate($params, [], $allowField, '', false);
    }

    /**
     * @notes 更新砍价商品
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/8/27 16:40
     */
    public static function updateBargainGoods($params)
    {
        // 删除旧数据
        $destroyIds = BargainGoods::where('activity_id', $params['id'])->column('id');
        // 删除商品参与活动的信息
        $goodsActivityIds = GoodsActivity::where([
            'activity_type' => ActivityEnum::BARGAIN,
            'activity_id' => $params['id'],
        ])->column('id');
        if (count($goodsActivityIds)) {
            GoodsActivity::duokaiDestroy($goodsActivityIds);
        }
        BargainGoods::duokaiDestroy($destroyIds);
        // 添加新数据
        $params['activity_id'] = $params['id'];
        self::addBarginGoods($params);
        // 添加新的商品参与活动数据
        self::addGoodsActivity($params);
    }

    /**
     * @notes 确认砍价活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/27 17:12
     */
    public static function confirm($params)
    {
        try {
            $bargainActivity = BargainActivity::findOrEmpty($params['id']);
            if ($bargainActivity->isEmpty()) {
                throw new \Exception('砍价活动不存在');
            }
            if ($bargainActivity->status != BargainEnum::ACTIVITY_STATUS_WAIT) {
                throw new \Exception('不是未开始状态的砍价活动不允许确认');
            }
            $bargainActivity->status = BargainEnum::ACTIVITY_STATUS_ING;
            $bargainActivity->duokaiSave();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 结束砍价活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/27 17:29
     */
    public static function stop($params)
    {
        Db::startTrans();
        try {
            $bargainActivity = BargainActivity::findOrEmpty($params['id']);
            if ($bargainActivity->isEmpty()) {
                throw new \Exception('砍价活动不存在');
            }
            if ($bargainActivity->status != BargainEnum::ACTIVITY_STATUS_ING) {
                throw new \Exception('不是进行中状态的砍价活动不允许结束');
            }
            // 更新砍价活动状态
            $bargainActivity->status = BargainEnum::ACTIVITY_STATUS_END;
            $bargainActivity->close_time = time();
            $bargainActivity->duokaiSave();

            // 更新砍价中的记录为砍价失败
            $bargainInitiate = BargainInitiate::where([
                'activity_id' => $bargainActivity->id,
                'status' =>  BargainEnum::STATUS_ING
            ])->select()->toArray();
            $updateData = [];
            foreach($bargainInitiate as $item) {
                $updateData[] = [
                    'id' => $item['id'],
                    'status' => BargainEnum::STATUS_FAIL
                ];
            }
            (new BargainInitiate)->duokaiSaveAll($updateData, true, false, false);

            // 删除商品参与活动的信息
            $goodsActivityIds = GoodsActivity::where([
                'activity_type' => ActivityEnum::BARGAIN,
                'activity_id' => $params['id'],
            ])->column('id');
            if (count($goodsActivityIds)) {
                GoodsActivity::duokaiDestroy($goodsActivityIds);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 删除砍价活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/27 17:35
     */
    public static function delete($params)
    {
        Db::startTrans();
        try {
            $bargainActivity = BargainActivity::findOrEmpty($params['id']);
            if ($bargainActivity->isEmpty()) {
                throw new \Exception('砍价活动不存在');
            }
            // 删除砍价商品
            $destroyIds = BargainGoods::where('activity_id', $bargainActivity->id)->column('id');
            BargainGoods::duokaiDestroy($destroyIds);

            // 删除砍价活动
            BargainActivity::duokaiDestroy($bargainActivity->id);

            // 更新砍价中的记录为砍价失败
            $bargainInitiate = BargainInitiate::where([
                'activity_id' => $bargainActivity->id,
                'status' =>  BargainEnum::STATUS_ING
            ])->select()->toArray();
            $updateData = [];
            foreach($bargainInitiate as $item) {
                $updateData[] = [
                    'id' => $item['id'],
                    'status' => BargainEnum::STATUS_FAIL
                ];
            }
            (new BargainInitiate)->duokaiSaveAll($updateData, true, false, false);

            // 删除商品参与活动的信息
            $goodsActivityIds = GoodsActivity::where([
                'activity_type' => ActivityEnum::BARGAIN,
                'activity_id' => $params['id'],
            ])->column('id');
            if (count($goodsActivityIds)) {
                GoodsActivity::duokaiDestroy($goodsActivityIds);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 活动数据
     * @param $params
     * @author Tab
     * @date 2021/9/23 18:56
     */
    public static function activityData($params)
    {
        return [
            'activity_info' => self::activityInfo($params),
            'activity_data' => self::activityDataInfo($params),
            'activity_goods' => self::activityGoods($params)
        ];
    }

    /**
     * @notes 活动信息
     * @param $params
     * @author Tab
     * @date 2021/9/24 10:43
     */
    public static function activityInfo($params)
    {
        $field =  [
            'sn',
            'name',
            'start_time' => 'start_time_desc',
            'end_time' => 'end_time_desc',
            'status',
            'status' => 'status_desc',
            'close_time'
        ];
        $info = BargainActivity::field($field)->findOrEmpty($params['id'])->toArray();
        return $info;
    }

    /**
     * @notes 活动数据
     * @param $params
     * @author Tab
     * @date 2021/9/24 11:57
     */
    public static function activityDataInfo($params)
    {
        // 浏览量
        $visited = BargainActivity::where('id', $params['id'])->value('visited');
        // 活动对应的订单id
        $orderIds = BargainInitiate::where('activity_id', $params['id'])->whereNotNull('order_id')->column('order_id');
        // 成交订单数
        $orderCount = Order::where([
            ['order_type', '=' ,OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->count();
        // 销量
        $totalNum = Order::where([
            ['order_type', '=' ,OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->sum('total_num');
        // 销售额
        $totalAmount = Order::where([
            ['order_type', '=' ,OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->sum('order_amount');
        // 发起砍价次数
        $initiateCount = BargainInitiate::where('activity_id', $params['id'])->count();
        // 砍价成功次数
        $initiateSuccessCount = BargainInitiate::where([
            'activity_id' => $params['id'],
            'status' => BargainEnum::STATUS_SUCCESS
        ])->count();
        // 帮砍人数
        $initiateIds = BargainInitiate::where('activity_id', $params['id'])->column('id');
        $helpCount = BargainHelp::where('initiate_id', 'in', $initiateIds)->count();

        return [
            'visited' => $visited,
            'total_amount' => $totalAmount,
            'total_num' => $totalNum,
            'order_count' => $orderCount,
            'initiate_count' => $initiateCount,
            'initiate_success_count' => $initiateSuccessCount,
            'help_count' => $helpCount,
        ];
    }

    /**
     * @notes 活动商品
     * @param $params
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/24 14:48
     */
    public static function activityGoods($params)
    {
        $field = [
            'g.id' => 'goods_id',
            'g.image' => 'goods_image',
            'g.name' => 'goods_name',
            'gi.id' => 'item_id',
            'gi.spec_value_str',
            'gi.sell_price',
            'bg.visited',
            'bg.visited',
            'bg.activity_id',
        ];

        $lists = BargainGoods::withoutGlobalScope()
            ->aliasSid('bg')
            ->alias('bg')
            ->leftJoin('goods g', 'g.id = bg.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = bg.item_id')
            ->field($field)
            ->where('bg.activity_id', $params['id'])
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['goods_image'] = FileService::getFileUrl($item['goods_image']);
            $initiateInfo = self::initiateInfo($item);
            // 发起砍价次数
            $item['initiate_count'] = $initiateInfo['initiate_count'];
            // 帮砍次数
            $item['help_count'] = $initiateInfo['help_count'];
            // 发起砍价成功次数
            $item['initiate_success_count'] = $initiateInfo['initiate_success_count'];
            // 成交订单
            $item['order_count'] = $initiateInfo['order_count'];
            // 销售量
            $item['total_num'] = $initiateInfo['total_num'];
            // 销售额
            $item['total_amount'] = $initiateInfo['total_amount'];
        }

        return $lists;
    }

    /**
     * @notes 砍价信息
     * @param $item
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/24 14:41
     */
    public static function initiateInfo($item)
    {
        $bargainInitiate =  BargainInitiate::where('goods_snapshot->goods_id', $item['goods_id'])
            ->where('goods_snapshot->item_id', $item['item_id'])
            ->select()->toArray();
        // 砍价次数
        $initiateCount = count($bargainInitiate);
        // 帮砍人数
        $helpCount = 0;
        // 砍价成功
        $initiateSuccessCount = 0;
        // 订单id
        $orderIds = [];
        foreach ($bargainInitiate as $item) {
            $helpCount = $item['help_num'];
            if ($item['status'] == BargainEnum::STATUS_SUCCESS) {
                $initiateSuccessCount++;
            }
            if (!empty($item['order_id'])) {
                $orderIds[] = $item['order_id'];
            }
        }
        // 成交订单
        $orderCount = Order::where([
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->count();
        // 销售量
        $totalNum = Order::where([
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->sum('total_num');
        // 销售额
        $totalAmount = Order::where([
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds]
        ])->sum('order_amount');

        return [
            'initiate_count' => $initiateCount,
            'help_count' => $helpCount,
            'initiate_success_count' => $initiateSuccessCount,
            'order_count' => $orderCount,
            'total_num' => $totalNum,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * @notes 结束砍价记录
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/9/24 18:17
     */
    public static function stopInitiate($params)
    {
        try {
            $bargainInitiate = BargainInitiate::findOrEmpty($params['id']);
            if ($bargainInitiate->isEmpty()) {
                throw new \Exception('砍价记录不存在');
            }
            $bargainInitiate->status = BargainEnum::STATUS_FAIL;
            $bargainInitiate->end_time = time();
            $bargainInitiate->duokaiSave();

            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 数据中心
     * @return array
     * @author Tab
     * @date 2021/9/24 18:27
     */
    public static function dataCenter()
    {
        return [
            'center_data' => self::centerData(),
            'top_visited' => self::topVisited(),
            'top_amount' => self::topAmount(),
        ];
    }

    /**
     * @notes 数据概览 - 活动数据
     * @author Tab
     * @date 2021/9/24 18:28
     */
    public static function centerData()
    {
        // 累计活动场次(已结束的)
        $activityCount = BargainActivity::where('status', BargainEnum::ACTIVITY_STATUS_END)->count();
        // 累计浏览量
        $activityVisited = BargainActivity::sum('visited');
        // 累计销售额
        $totalOrderAmount = Order::where([
            'order_type' => OrderEnum::BARGAIN_ORDER,
            'pay_status' => YesNoEnum::YES
        ])->sum('order_amount');
        // 累计销量
        $totalNum = Order::where([
            'order_type' => OrderEnum::BARGAIN_ORDER,
            'pay_status' => YesNoEnum::YES
        ])->sum('total_num');
        // 累计成交订单
        $orderCount = Order::where([
            'order_type' => OrderEnum::BARGAIN_ORDER,
            'pay_status' => YesNoEnum::YES
        ])->count();
        // 累计发起砍价次数
        $initiateCount = BargainInitiate::count();
        // 累计发起砍价成功次数
        $initiateSuccessCount = BargainInitiate::where('status', BargainEnum::STATUS_SUCCESS)->count();

        return [
            'activity_count' => $activityCount,
            'activity_visited' => $activityVisited,
            'total_order_amount' => $totalOrderAmount,
            'total_num' => $totalNum,
            'order_count' => $orderCount,
            'initiate_count' => $initiateCount,
            'initiate_success_count' => $initiateSuccessCount,
        ];
    }

    /**
     * @notes 活动浏览量排行
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/24 18:40
     */
    public static function topVisited()
    {
        $lists =BargainActivity::field('name,visited')
            ->order([
                'visited' => 'desc',
                'id' => 'desc'
            ])
            ->limit(10)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 活动销售额排行
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/24 18:47
     */
    public static function topAmount()
    {
        $lists = BargainActivity::field('id,name')->select()->toArray();
        foreach ($lists as &$item) {
            $item['total_order_amount'] = self::totalOrderAmount($item);
        }
        $sortArr = array_column($lists, 'total_order_amount');

        array_multisort($sortArr, SORT_DESC , SORT_NUMERIC , $lists);

        return array_slice($lists, 0, 10);
    }

    /**
     * @notes 活动销售额
     * @param $params
     * @author Tab
     * @date 2021/9/24 18:47
     */
    public static function totalOrderAmount($params)
    {
        $orderIds = BargainInitiate::where('activity_id', $params['id'])
            ->whereNotNull('order_id')
            ->column('order_id');
        $totalOrderAmount = Order::where([
            ['order_type', '=', OrderEnum::BARGAIN_ORDER],
            ['pay_status', '=', YesNoEnum::YES],
            ['id', 'in', $orderIds],
        ])->sum('order_amount');
        return $totalOrderAmount;
    }
}
