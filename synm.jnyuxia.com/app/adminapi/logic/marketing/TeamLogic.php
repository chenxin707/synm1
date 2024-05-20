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

namespace app\adminapi\logic\marketing;


use app\common\enum\ActivityEnum;
use app\common\enum\OrderEnum;
use app\common\enum\TeamEnum;
use app\common\logic\RefundLogic;
use app\common\model\AfterSale;
use app\common\model\AfterSaleGoods;
use app\common\model\Goods;
use app\common\model\GoodsActivity;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\TeamActivity;
use app\common\model\TeamFound;
use app\common\model\TeamGoods;
use app\common\model\TeamGoodsItem;
use \Exception;
use think\facade\Db;

class TeamLogic
{
    /**
     * @notes 拼团概况
     * @author 张无忌
     * @date 2021/8/2 19:13
     */
    public static function survey()
    {

        $detail['base'] = [
            //累计活动场次
            'total_activity_num' => (new TeamActivity())->count(),
            //累计浏览量
            'total_browse_volume' => (new TeamGoods())->duokaiWithoutGlobalScope()
                ->aliasSid('TG')
                ->alias('TG')->join('team_activity TA', 'TA.id = TG.team_id')->sum('TG.browse_volume'),
            //累计销售金额
            'total_sales_amount' => (new TeamGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('TGI')
                ->alias('TGI')->join('team_activity TA', 'TA.id = TGI.team_id')->sum('TGI.sales_amount'),
            //累计销售量
            'total_sales_volume' => (new TeamGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('TGI')
                ->alias('TGI')->join('team_activity TA', 'TA.id = TGI.team_id')->sum('TGI.sales_volume'),
            //累计成交订单
            'total_closing_order' => (new TeamGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('TGI')
                ->alias('TGI')->join('team_activity TA', 'TA.id = TGI.team_id')->sum('TGI.closing_order'),
            //累计成团数
            'total_success_team' => (new TeamFound())->duokaiWithoutGlobalScope()
                ->aliasSid('TF')
                ->alias('TF')->join('team_activity TA', 'TA.id = TF.team_id')->where(['TF.status' => TeamEnum::TEAM_FOUND_SUCCESS])->count()
        ];

        $detail['sort_browse_volume'] = (new TeamGoods())->duokaiWithoutGlobalScope()
            ->aliasSid('TG')
            ->alias('TG')
            ->field(['TA.id,TA.name,sum(TG.browse_volume) as sum_browse_volume'])
            ->join('team_activity TA', 'TA.id = TG.team_id')
            ->group('TG.team_id')
            ->order('sum_browse_volume desc, TG.id desc')
            ->limit(10)
            ->select()->toArray();

        $detail['sort_sales_amount'] = (new TeamGoodsItem())->duokaiWithoutGlobalScope()
            ->aliasSid('TGI')
            ->alias('TGI')
            ->field(['TA.id,TA.name,sum(TGI.sales_amount) as sum_sales_amount'])
            ->join('team_activity TA', 'TA.id = TGI.team_id')
            ->group('TGI.team_id')
            ->order('sum_sales_amount desc, TGI.id desc')
            ->limit(10)
            ->select()->toArray();

        return $detail;
    }

    /**
     * @notes 拼团详细信息
     * @param $params
     * @return array
     * @author 张无忌
     * @date 2021/8/2 19:13
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @throws @\think\db\exception\DataNotFoundException
     */
    public static function detail($params)
    {

        $detail = (new TeamActivity())
            ->withoutField(['create_time', 'update_time', 'delete_time'])
            ->with(['goods'])
            ->findOrEmpty($params['id'])
            ->toArray();

        $goodsItem = (new TeamGoodsItem())->duokaiWithoutGlobalScope()
            ->aliasSid('TGI')
            ->alias('TGI')
            ->field('TGI.*, GI.stock')
            ->join('goods_item GI', 'GI.id = TGI.item_id')
            ->where(['TGI.team_id' => $params['id']])
            ->select()->toArray();

        $detail['start_time'] = date('Y-m-d H:i:s', $detail['start_time']);
        $detail['end_time'] = date('Y-m-d H:i:s', $detail['end_time']);

        foreach ($detail['goods'] as &$goods) {
            $g = (new Goods())->field('total_stock,spec_type,min_price')
                ->where(['id'=>$goods['goods_id']])
                ->findOrEmpty();
            $snap = $goods['goods_snap'];
            $goods['id'] = $goods['goods_id'];
            $goods['name']  = $snap['name'];
            $goods['image'] = $snap['image'];
            $goods['total_stock'] = $g['total_stock'];
            $goods['spec_type'] = $g['spec_type'];
            $goods['sell_price'] = $g['min_price'];

            $goods['item'] = [];
            foreach ($goodsItem as $item) {
                if ($item['goods_id'] == $goods['goods_id']) {
                    $goods['item'][] = [
                        'id'             => $item['item_id'],
                        'goods_id'       => $item['goods_id'],
                        'item_id' => $item['item_id'],
                        'spec_value_str' => $item['spec_value_str'],
                        'sell_price'     => $item['sell_price'],
                        'team_price'     => $item['team_price'],
                        'stock'          => $item['stock'],
                    ];
                    unset($item);
                }
            }
            unset($goods['team_id']);
            unset($goods['goods_snap']);
            unset($goods['goods_id']);
        }

        return $detail;
    }

    /**
     * @notes 拼团数据信息
     * @param $params
     * @return array
     * @author 张无忌
     * @date 2021/8/2 19:14
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @throws @\think\db\exception\DataNotFoundException
     */
    public static function info($params)
    {

        $detail['base'] = (new TeamActivity())
            ->field('id,sn,name,start_time,end_time,status,create_time')
            ->with(['goods'])
            ->findOrEmpty($params['id'])
            ->toArray();

        $detail['base']['start_time'] = date('Y-m-d H:i:s', $detail['base']['start_time']);
        $detail['base']['end_time'] = date('Y-m-d H:i:s', $detail['base']['end_time']);
        $detail['base']['activity_time'] = $detail['base']['start_time'] . ' 至 ' . $detail['base']['end_time'];
        $detail['base']['status_text'] = TeamEnum::getActivityStatusDesc($detail['base']['status']);

        $detail['data'] = [
            'browse_volume' => (new TeamGoods)->where(['team_id' => $params['id']])->sum('browse_volume'),
            'sales_amount' => (new TeamGoodsItem())->where(['team_id' => $params['id']])->sum('sales_amount'),
            'sales_volume' => (new TeamGoodsItem())->where(['team_id' => $params['id']])->sum('sales_volume'),
            'closing_order' => (new TeamGoodsItem())->where(['team_id' => $params['id']])->sum('closing_order'),
            'team_success' => (new TeamFound())->where(['team_id' => $params['id'], 'status' => TeamEnum::TEAM_FOUND_SUCCESS])->count()
        ];

        $goodsItem = (new TeamGoodsItem())->where(['team_id' => $params['id']])->select()->toArray();
        foreach ($detail['base']['goods'] as &$goods) {
            $snap = $goods['goods_snap'];
            $goods['name'] = $snap['name'];
            $goods['image'] = $snap['image'];
            $goods['items'] = [];
            foreach ($goodsItem as $item) {
                if ($item['goods_id'] == $goods['goods_id']) {
                    $goods['items'][] = [
                        'item_id' => $item['item_id'],
                        'spec_value_str' => $item['spec_value_str'],
                        'sell_price' => $item['sell_price'],
                        'team_price' => $item['team_price'],
                        'sales_amount' => $item['sales_amount'],
                        'browse_volume' => $goods['browse_volume'],
                        'sales_volume' => $item['sales_volume'],
                        'closing_order' => $item['closing_order']
                    ];
                    unset($item);
                }
            }
            unset($goods['team_id']);
            unset($goods['goods_snap']);
        }

        return $detail;
    }

    /**
     * @notes 新增拼团活动
     * @param $params
     * @return string
     * @author 张无忌
     * @date 2021/8/2 14:40
     */
    public static function add($params)
    {

        Db::startTrans();
        try {
            // 验证商品是否正在参与活动
            $result = self::checkGoods($params);
            if ($result !== false) {
                throw new Exception($result . '：正在参与活动中,不可重复添加');
            }

            // 1、创建拼团活动信息
            $team = TeamActivity::duokaiCreate([
                'sn' => create_code(4),
                'name' => $params['name'],
                'people_num' => $params['people_num'],
                'explain' => $params['explain'] ?? '',
//                'min_buy' => $params['min_buy'],
                'max_buy' => $params['max_buy'],
                //'is_coupon' => $params['is_coupon'],
                //'is_distribution' => $params['is_distribution'],
                'is_automatic' => $params['is_automatic'],
                'effective_time' => $params['effective_time'],
                'start_time' => strtotime($params['start_time']),
                'end_time' => strtotime($params['end_time']),
            ]);

            # 2、创建活动商品信息
            $goodsItemData = [];
            $goodsActivityData = [];
            foreach ($params['goods'] as $item) {
                $goods = Goods::field([
                    'id,name,code,image,video,poster,express_type',
                    'express_money,express_template_id,spec_type',
                    'min_price,max_price,content'
                ])->findOrEmpty(intval($item['goods_id']))->toArray();

                if (!$goods) {
                    throw new Exception('商品不存在');
                }

                $teamGoods = TeamGoods::duokaiCreate([
                    'team_id'        => $team['id'],
                    'goods_id'       => $goods['id'],
                    'min_team_price' => min(array_column($item['items'], 'team_price')),
                    'max_team_price' => max(array_column($item['items'], 'team_price')),
                    'goods_snap'     => json_encode($goods, JSON_UNESCAPED_UNICODE),
                    'virtual_sales_num'   => $item['virtual_sales_num'] ?? 0,
                    'virtual_click_num'   => $item['virtual_click_num'] ?? 0,
                ]);

                foreach ($item['items'] as $val) {
                    $goodsItem = GoodsItem::where(['goods_id' => $goods['id'], 'id' => intval($val['item_id'])])->findOrEmpty()->toArray();

                    if (!$goodsItem) {
                        throw new Exception('商品规格不存在');
                    }

                    $goodsItemData[] = [
                        'team_gid' => $teamGoods['id'],
                        'team_id' => $team['id'],
                        'goods_id' => $goods['id'],
                        'item_id' => $goodsItem['id'],
                        'spec_value_str' => $goodsItem['spec_value_str'],
                        'sell_price' => $goodsItem['sell_price'],
                        'team_price' => $val['team_price'],
                        'item_snap' => json_encode($goodsItem, JSON_UNESCAPED_UNICODE)
                    ];

                    $goodsActivityData[] = [
                        'activity_type' => ActivityEnum::TEAM,
                        'activity_id' => $team['id'],
                        'goods_id' => $goods['id'],
                        'item_id' => $goodsItem['id'],
                    ];
                }
            }

            if (!empty($goodsItemData)) {
                (new TeamGoodsItem())->duokaiSaveAll($goodsItemData);
            }

            // 商品参与活动的信息
            if (!empty($goodsActivityData)) {
                (new GoodsActivity())->duokaiSaveAll($goodsActivityData);
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 编辑拼团活动
     * @param $params
     * @return bool|string
     * @author 张无忌
     * @date 2021/8/2 16:12
     */
    public static function edit($params)
    {

        Db::startTrans();
        try {

            $teamActivity = (new TeamActivity())->findOrEmpty($params['id'])->toArray();

            if (!$teamActivity) {
                throw new Exception('拼团活动不存在,请刷新列表');
            }

            // 活动已开启: 只允许修改部分内容
            if ($teamActivity['status'] != TeamEnum::TEAM_STATUS_NOT) {
                TeamActivity::duokaiUpdate([
                    'sn' => '',
                    'name' => $params['name'],
                    'explain' => $params['explain'] ?? '',
                    'end_time' => strtotime($params['end_time']),
                ], ['id' => $params['id']]);
            }

            // 获取未开启: 可以修改任意内容
            if ($teamActivity['status'] == TeamEnum::TEAM_STATUS_NOT) {
                // 删除旧活动商品
                (new TeamGoods())->where(['team_id' => $params['id']])->useSoftDelete('delete_time', time())->delete();
                (new TeamGoodsItem())->where(['team_id' => $params['id']])->useSoftDelete('delete_time', time())->delete();
                (new GoodsActivity())->where(['activity_id' => $params['id']])->useSoftDelete('delete_time', time())->delete();

                // 验证商品是否正在参与活动
                $result = self::checkGoods($params);
                if ($result !== false) {
                    throw new Exception($result . '：正在参与活动中,不可重复添加');
                }

                // 创建拼团活动
                TeamActivity::duokaiUpdate([
                    'name' => $params['name'],
                    'people_num' => $params['people_num'],
                    'explain' => $params['explain'] ?? '',
//                    'min_buy' => $params['min_buy'],
                    'max_buy' => $params['max_buy'],
                    //'is_coupon' => $params['is_coupon'],
                    //'is_distribution' => $params['is_distribution'],
                    'is_automatic' => $params['is_automatic'],
                    'effective_time' => $params['effective_time'],
                    'start_time' => strtotime($params['start_time']),
                    'end_time' => strtotime($params['end_time']),
                ], ['id' => $params['id']]);

                // 置换新活动商品
                $goodsItemData = [];
                $goodsActivityData = [];
                foreach ($params['goods'] as $item) {
                    $goods = Goods::field([
                        'id,name,code,image,video,poster,express_type',
                        'express_money,express_template_id,spec_type',
                        'min_price,max_price,content'
                    ])->findOrEmpty(intval($item['goods_id']))->toArray();

                    if (!$goods) {
                        throw new Exception('商品不存在');
                    }

                    $teamGoods = TeamGoods::duokaiCreate([
                        'team_id' => $params['id'],
                        'goods_id' => $goods['id'],
                        'min_team_price' => min(array_column($item['items'], 'team_price')),
                        'max_team_price' => max(array_column($item['items'], 'team_price')),
                        'goods_snap' => json_encode($goods, JSON_UNESCAPED_UNICODE),
                        'virtual_sales_num'   => $item['virtual_sales_num'] ?? 0,
                        'virtual_click_num'   => $item['virtual_click_num'] ?? 0,
                    ]);

                    foreach ($item['items'] as $val) {
                        $goodsItem = GoodsItem::where(['goods_id' => $goods['id'], 'id' => intval($val['item_id'])])->findOrEmpty()->toArray();

                        if (!$goodsItem) {
                            throw new Exception('商品规格不存在');
                        }

                        $goodsItemData[] = [
                            'team_gid' => $teamGoods['id'],
                            'team_id' => $params['id'],
                            'goods_id' => $goods['id'],
                            'item_id' => $goodsItem['id'],
                            'spec_value_str' => $goodsItem['spec_value_str'],
                            'sell_price' => $goodsItem['sell_price'],
                            'team_price' => $val['team_price'],
                            'item_snap' => json_encode($goodsItem, JSON_UNESCAPED_UNICODE)
                        ];

                        $goodsActivityData[] = [
                            'activity_type' => ActivityEnum::TEAM,
                            'activity_id' => $params['id'],
                            'goods_id' => $goods['id'],
                            'item_id' => $goodsItem['id'],
                        ];
                    }
                }

                if (!empty($goodsItemData)) {
                    (new TeamGoodsItem())->duokaiSaveAll($goodsItemData);
                }

                if (!empty($goodsActivityData)) {
                    (new GoodsActivity())->duokaiSaveAll($goodsActivityData);
                }
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除拼团活动
     * @param $params
     * @return bool
     * @author 张无忌
     * @date 2021/8/2 17:00
     */
    public static function delete($params)
    {
        Db::startTrans();
        try {
            //1、拼团活动状态设置成已结束
            TeamActivity::duokaiUpdate([
                'status' => TeamEnum::TEAM_STATUS_END,
                'update_time' => time()
            ], ['id'=>$params['id']]);

            //2、删除拼团活动
            TeamActivity::duokaiDestroy($params['id']);

            //3、查找所有待成团
            $teamFoundIds = (new TeamFound())
                ->where([
                    'team_id' => $params['id'],
                    'status'  => TeamEnum::TEAM_FOUND_CONDUCT
                ])->column('id');

            //4、关闭未支付的订单
            (new Order())
                ->where([['team_found_id', 'in', $teamFoundIds], ['pay_status', '=', 0]])
                ->update(['order_status' => OrderEnum::STATUS_CLOSE]);

            //5、退款已支付的订单
            $orders = (new Order())
                ->where([
                    ['team_found_id', 'in', $teamFoundIds],
                    ['pay_status', '=', 1],
                    ['order_status', '=', 1]
                ])
                ->select()->toArray();

            foreach ($orders as $order) {
                $orderGoods = (new OrderGoods())->where(['order_id'=>$order['id']])->findOrEmpty()->toArray();
                $afterSale = AfterSale::duokaiCreate([
                    'sn'              => generate_sn((new AfterSale()), 'sn'),
                    'user_id'         => $order['user_id'],
                    'order_id'        => $order['id'],
                    'order_goods_id'  => $orderGoods['id'],
                    'refund_reason'   => '拼团失败',
                    'refund_remark'   => '系统发起整单退款',
                    'refund_type'     => 1,
                    'refund_method'   => 1,
                    'refund_total_amount' => $order['order_amount'],
                    'refund_way'      => 1,
                    'refund_status'   => 3,
                    'status'          => 1,
                    'sub_status'      => 12,
                ]);

                AfterSaleGoods::duokaiCreate([
                    'after_sale_id'  => $afterSale->id,
                    'order_goods_id' => $orderGoods['id'],
                    'goods_id'       => $orderGoods['goods_id'],
                    'item_id'        => $orderGoods['item_id'],
                    'goods_num'      => $orderGoods['goods_num'],
                    'goods_price'    => $orderGoods['goods_price'],
                    'refund_amount'  => $order['order_amount'],
                ]);

                RefundLogic::refund(1, $order, $afterSale->id, $order['order_amount']);
                Order::duokaiUpdate([
                    'id' => $order['id'],
                    'order_status' => OrderEnum::STATUS_CLOSE,
                    'update_time'  => time()
                ], [], [], '', false);
            }

            // 删除商品参与活动的信息
            $goodsActivityIds = GoodsActivity::where([
                'activity_type' => ActivityEnum::TEAM,
                'activity_id' => $params['id'],
            ])->column('id');
            if (count($goodsActivityIds)) {
                GoodsActivity::duokaiDestroy($goodsActivityIds);
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 启动活动
     * @param $params
     * @return string
     * @throws Exception
     * @author 张无忌
     * @date 2021/8/2 20:41
     */
    public static function open($params)
    {

        $id = $params['id'];
        $teamActivity = (new TeamActivity())->findOrEmpty($id)->toArray();
        if (!$teamActivity) {
            throw new Exception('拼团活动不存在,请刷新列表');
        }

        if ($teamActivity['status'] == TeamEnum::TEAM_STATUS_END) {
            throw new Exception('结束的拼团不允许再次启动');
        }

        if ($teamActivity['status'] == TeamEnum::TEAM_STATUS_CONDUCT) {
            throw new Exception('此拼团活动已是在进行中,无需重复操作');
        }

        TeamActivity::where(['id' => $id])->update([
            'status' => TeamEnum::TEAM_STATUS_CONDUCT,
            'update_time' => time()
        ]);
        return true;

    }

    /**
     * @notes 停止活动
     * @author 张无忌
     * @date 2021/8/2 20:41
     */
    public static function stop($params)
    {
        Db::startTrans();
        try {

            //1、拼团活动状态设置成已结束
            TeamActivity::duokaiUpdate([
                'status' => TeamEnum::TEAM_STATUS_END,
                'update_time' => time()
            ], ['id'=>$params['id']]);

            //2、查找所有待成团
            $teamFounds = (new TeamFound())
                ->where([
                    'team_id' => $params['id'],
                    'status'  => TeamEnum::TEAM_FOUND_CONDUCT
                ])->select()->toArray();

            foreach ($teamFounds as $item) {
                \app\shopapi\logic\TeamLogic::signFailTeam($item['order_id']);
            }

            // 3、删除商品参与活动的信息
            $goodsActivityIds = GoodsActivity::where([
                'activity_type' => ActivityEnum::TEAM,
                'activity_id' => $params['id'],
            ])->column('id');
            if (count($goodsActivityIds)) {
                GoodsActivity::duokaiDestroy($goodsActivityIds);
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 验证新增的商品是否已参与活动
     * @param $params
     * @return bool
     * @author 张无忌
     * @date 2021/8/4 19:46
     */
    private static function checkGoods($params)
    {
        // 获取正在进行的或者未开始的活动
        $teamIds = (new TeamActivity())->whereIn('status', [0, 1])->column('id');

        if ($teamIds) {
            $goodsId = array_column($params['goods'], 'goods_id');
            $teamGoods = (new TeamGoods())
                ->whereIn('team_id', $teamIds)
                ->whereIn('goods_id', $goodsId)
                ->findOrEmpty()->toArray();

            if ($teamGoods) {
                $goods = (new Goods())->findOrEmpty($teamGoods['goods_id'])->toArray();
                return $goods['name'];
            }
        }

        return false;
    }

    /**
     * @notes 结束拼团记录
     * @param $params
     * @return bool
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/9/30 11:30 上午
     */
    public static function cancel($params)
    {
        $teamFound = (new TeamFound())->where(['id'=>intval($params['id'])])->findOrEmpty()->toArray();
        \app\shopapi\logic\TeamLogic::signFailTeam($teamFound['order_id']);
        return true;
    }
}
