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
use app\common\enum\SeckillEnum;
use app\common\model\Goods;
use app\common\model\GoodsActivity;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\SeckillActivity;
use app\common\model\SeckillGoods;
use app\common\model\SeckillGoodsItem;
use app\common\service\FileService;
use Exception;
use think\facade\Db;

class SeckillLogic
{
    /**
     * @notes 优惠券概况
     * @author 张无忌
     * @date 2021/7/26 16:45
     */
    public static function survey()
    {
        $detail['base'] = [
            'total_activity_num' => (new SeckillActivity())->count(),
            'total_browse_volume' => (new SeckillGoods())->duokaiWithoutGlobalScope()
                    ->aliasSid('SG')
                    ->alias('SG')
                    ->join('seckill_activity SA', 'SA.id = SG.seckill_id')
                    ->sum('SG.browse_volume'),
            'total_sales_amount' => (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('SGI')
                ->alias('SGI')
                ->join('seckill_activity SA', 'SA.id = SGI.seckill_id')
                ->sum('SGI.sales_amount'),
            'total_sales_volume' => (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('SGI')
                ->alias('SGI')
                ->join('seckill_activity SA', 'SA.id = SGI.seckill_id')
                ->sum('SGI.sales_volume'),
            'total_closing_order' => (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
                ->aliasSid('SGI')
                ->alias('SGI')
                ->join('seckill_activity SA', 'SA.id = SGI.seckill_id')
                ->sum('SGI.closing_order')
        ];

        $detail['sort_browse_volume'] = (new SeckillGoods())->duokaiWithoutGlobalScope()
            ->aliasSid('SG')
            ->alias('SG')
            ->field(['SA.id,SA.name,SG.browse_volume'])
            ->join('seckill_activity SA', 'SA.id = SG.seckill_id')
            ->group('SG.seckill_id')
            ->order('SG.browse_volume desc, SG.id desc')
            ->limit(10)
            ->select()->toArray();

        $detail['sort_sales_volume'] = (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
            ->aliasSid('SGI')
            ->alias('SGI')
            ->field(['SA.id,SA.name,SGI.sales_volume'])
            ->join('seckill_activity SA', 'SA.id = SGI.seckill_id')
            ->group('SGI.seckill_id')
            ->order('SGI.sales_volume desc, SGI.id desc')
            ->limit(10)
            ->select()->toArray();

        return $detail;
    }

    /**
     * @notes 获取秒杀活动详细
     * @param $params
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/26 15:20
     */
    public static function detail($params)
    {
        $detail = (new SeckillActivity())
                ->withoutField(['create_time', 'update_time', 'delete_time'])
                ->with(['goods'])
                ->findOrEmpty($params['id'])
                ->toArray();

        if (!$detail) {
            return [];
        }

        $goodsItem = (new SeckillGoodsItem())->duokaiWithoutGlobalScope()
            ->aliasSid('SGI')
            ->alias('SGI')
            ->field('SGI.*, GI.stock')
            ->join('goods_item GI', 'GI.id = SGI.item_id')
            ->where(['seckill_id'=>$params['id']])
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

            $goods['item']  = [];
            foreach ($goodsItem as $item) {
                if ($item['goods_id'] == $goods['goods_id']) {
                    $goods['item'][] = [
                        'id'             => $item['item_id'],
                        'goods_id'       => $item['goods_id'],
                        'spec_value_str' => $item['spec_value_str'],
                        'sell_price'     => $item['sell_price'],
                        'seckill_price'  => $item['seckill_price'],
                        'stock'          => $item['stock'],
                    ];
                    unset($item);
                }
            }
            unset($goods['seckill_id']);
            unset($goods['goods_snap']);
            unset($goods['goods_id']);
        }

        return $detail;
    }

    /**
     * @notes 秒杀数据信息
     * @param $params
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/26 15:31
     */
    public static function info($params)
    {
        $detail = (new SeckillActivity())
            ->withoutField(['create_time', 'update_time', 'delete_time'])
            ->with(['goods'])
            ->findOrEmpty($params['id'])
            ->toArray();

        if (!$detail) {
            return [];
        }

        $goodsItem = (new SeckillGoodsItem())
            ->field(true)
            ->where(['seckill_id'=>$params['id']])
            ->select()->toArray();

        $detail['start_time'] = date('Y-m-d H:i:s', $detail['start_time']);
        $detail['end_time'] = date('Y-m-d H:i:s', $detail['end_time']);
        $detail['status_text'] = SeckillEnum::getSeckillStatusDesc($detail['status']);

        $detail['data'] = [
            'browse_volume' => (new SeckillGoods())->where(['seckill_id'=>$params['id']])->sum('browse_volume'),
            'sales_amount'  => (new SeckillGoodsItem())->where(['seckill_id'=>$params['id']])->sum('sales_amount'),
            'sales_volume'  => (new SeckillGoodsItem())->where(['seckill_id'=>$params['id']])->sum('sales_volume'),
            'closing_order' => (new SeckillGoodsItem())->where(['seckill_id'=>$params['id']])->sum('closing_order')
        ];

        foreach ($detail['goods'] as &$goods) {
            $goods['name']  = $goods['goods_snap']['name'];
            $goods['image'] = FileService::getFileUrl($goods['goods_snap']['image']);
            $goods['item']  = [];
            foreach ($goodsItem as $item) {
                if ($item['goods_id'] == $goods['goods_id']) {
                    $goods['item'][] = [
                        'item_id'        => $item['item_id'],
                        'spec_value_str' => $item['spec_value_str'],
                        'sell_price'     => $item['sell_price'],
                        'seckill_price'  => $item['seckill_price'],
                        'sales_amount'   => $item['sales_amount'],
                        'browse_volume'  => $goods['browse_volume'],
                        'sales_volume'   => $item['sales_volume'],
                        'closing_order'  => $item['closing_order']
                    ];
                    unset($item);
                }
            }
            unset($goods['seckill_id']);
            unset($goods['goods_snap']);
        }

        return $detail;
    }


    /**
     * @notes 添加秒杀活动
     * @param $params
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/26 11:22
     */
    public static function add($params)
    {
        Db::startTrans();
        try {
            // 验证商品是否正在参与活动
            $result = self::checkGoods($params);
            if ($result !== false) {
                throw new Exception($result.'：正在参与活动中,不可重复添加');
            }

            # 创建活动信息
            $seckill = SeckillActivity::duokaiCreate([
                'sn'         => create_code(8),
                'name'       => $params['name'],
//                'min_buy'    => $params['min_buy'],
                'max_buy'    => $params['max_buy'],
                //'is_coupon'  => $params['is_coupon'],
                //'is_distribution' => $params['is_distribution'],
                'explain'    => $params['explain'] ?? '',
                'start_time' => strtotime($params['start_time']),
                'end_time'   => strtotime($params['end_time'])
            ]);

            # 创建活动商品信息
            $goodsItemData = [];
            $goodsActivityData = [];
            foreach ($params['goods'] as $item) {

                $goods = (new Goods())->field([
                    'id,name,code,image,video,poster,express_type',
                    'express_money,express_template_id,spec_type',
                    'min_price,max_price,content'
                ])->findOrEmpty(intval($item['goods_id']))->toArray();

                if (!$goods) {
                    throw new Exception('商品不存在');
                }

                $seckillGoods = SeckillGoods::duokaiCreate([
                    'seckill_id' => $seckill['id'],
                    'goods_id'   => $goods['id'],
                    'min_seckill_price' => min(array_column($item['items'], 'seckill_price')),
                    'max_seckill_price' => max(array_column($item['items'], 'seckill_price')),
                    'goods_snap' => json_encode($goods, JSON_UNESCAPED_UNICODE),
                    'virtual_sales_num'   => $item['virtual_sales_num'] ?? 0,
                    'virtual_click_num'   => $item['virtual_click_num'] ?? 0,
                ]);

                foreach ($item['items'] as $val) {
                    $goodsItem = (new GoodsItem())
                        ->where(['goods_id'=>$goods['id'], 'id'=>intval($val['item_id'])])
                        ->findOrEmpty()->toArray();

                    if (!$goodsItem) {
                        throw new Exception('商品规格不存在');
                    }

                    $goodsItemData[] = [
                        'seckill_gid'    => $seckillGoods['id'],
                        'seckill_id'     => $seckill['id'],
                        'goods_id'       => $goods['id'],
                        'item_id'        => $goodsItem['id'],
                        'spec_value_str' => $goodsItem['spec_value_str'],
                        'sell_price'     => $goodsItem['sell_price'],
                        'seckill_price'  => $val['seckill_price'],
                        'item_snap'      => json_encode($goodsItem, JSON_UNESCAPED_UNICODE)
                    ];

                    $goodsActivityData[] = [
                        'activity_type' => ActivityEnum::SECKILL,
                        'activity_id' => $seckill['id'],
                        'goods_id' => $goods['id'],
                        'item_id' => $goodsItem['id'],
                    ];
                }
            }

            if (!empty($goodsItemData)) {
                (new SeckillGoodsItem())->duokaiSaveAll($goodsItemData);
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
     * @notes 编辑秒杀活动
     * @param $params
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/26 11:32
     */
    public static function edit($params)
    {
        Db::startTrans();
        try {
            $seckillActivity = (new SeckillActivity())->findOrEmpty($params['id'])->toArray();

            if (!$seckillActivity) {
                throw new Exception('秒杀活动不存在,请刷新列表');
            }

            // 活动已开启: 只允许编辑部分内容
            if ($seckillActivity['status'] != SeckillEnum::SECKILL_STATUS_NOT) {
                SeckillActivity::duokaiUpdate([
                    'name'       => $params['name'],
                    'explain'    => $params['explain'] ?? '',
                    'end_time'   => strtotime($params['end_time'])
                ], ['id'=>$params['id']]);
            }

            // 活动未开启: 可编辑所有
            if ($seckillActivity['status'] == SeckillEnum::SECKILL_STATUS_NOT) {
                // 删除旧的活动商品
                (new SeckillGoods())->where(['seckill_id'=>$params['id']])->useSoftDelete('delete_time', time())->delete();
                (new SeckillGoodsItem())->where(['seckill_id'=>$params['id']])->useSoftDelete('delete_time', time())->delete();
                // 删除旧的商品参与活动的信息
                (new GoodsActivity())->where([
                    'activity_type' => ActivityEnum::SECKILL,
                    'activity_id' => $params['id']
                ])
                    ->useSoftDelete('delete_time',time())
                    ->delete();


                // 验证商品是否正在参与活动
                $result = self::checkGoods($params);
                if ($result !== false) {
                    throw new Exception($result.'：正在参与活动中,不可重复添加');
                }

                // 更新活动基本信息
                SeckillActivity::duokaiUpdate([
                    'name'       => $params['name'],
//                    'min_buy'    => $params['min_buy'],
                    'max_buy'    => $params['max_buy'],
                    //'is_coupon'  => $params['is_coupon'],
                    //'is_distribution' => $params['is_distribution'],
                    'explain'    => $params['explain'] ?? '',
                    'start_time' => strtotime($params['start_time']),
                    'end_time'   => strtotime($params['end_time'])
                ], ['id'=>$params['id']]);

                // 添加新的活动商品
                $goodsItemData = [];
                $goodsActivityData = [];
                foreach ($params['goods'] as $item) {
                    $goods = (new Goods())->field([
                        'id,name,code,image,video,poster,express_type',
                        'express_money,express_template_id,spec_type',
                        'min_price,max_price,content'
                    ])->findOrEmpty(intval($item['goods_id']))->toArray();

                    if (!$goods) {
                        throw new Exception('商品不存在');
                    }

                    $seckillGoods = SeckillGoods::duokaiCreate([
                        'seckill_id' => $params['id'],
                        'goods_id' => $goods['id'],
                        'min_seckill_price' => min(array_column($item['items'], 'seckill_price')),
                        'max_seckill_price' => max(array_column($item['items'], 'seckill_price')),
                        'goods_snap' => json_encode($goods, JSON_UNESCAPED_UNICODE),
                        'virtual_sales_num'   => $item['virtual_sales_num'] ?? 0,
                        'virtual_click_num'   => $item['virtual_click_num'] ?? 0,
                    ]);

                    foreach ($item['items'] as $val) {
                        $goodsItem = (new GoodsItem())
                            ->where(['goods_id' => $goods['id'], 'id' => intval($val['item_id'])])
                            ->findOrEmpty()->toArray();

                        if (!$goodsItem) {
                            throw new Exception('商品规格不存在');
                        }

                        $goodsItemData[] = [
                            'seckill_gid'    => $seckillGoods['id'],
                            'seckill_id'     => $params['id'],
                            'goods_id'       => $goods['id'],
                            'item_id'        => $goodsItem['id'],
                            'spec_value_str' => $goodsItem['spec_value_str'],
                            'sell_price'     => $goodsItem['sell_price'],
                            'seckill_price'  => $val['seckill_price'],
                            'item_snap'      => json_encode($goodsItem, JSON_UNESCAPED_UNICODE)
                        ];

                        $goodsActivityData[] = [
                            'activity_type' => ActivityEnum::SECKILL,
                            'activity_id' => $params['id'],
                            'goods_id'       => $goods['id'],
                            'item_id'        => $goodsItem['id'],
                        ];
                    }
                }

                if (!empty($goodsItemData)) {
                    (new SeckillGoodsItem())->duokaiSaveAll($goodsItemData);
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
     * @notes 删除秒杀活动
     * @param $params
     * @return bool
     * @author 张无忌
     * @date 2021/7/26 18:00
     */
    public static function delete($params)
    {
        Db::startTrans();
        try {
            $order = (new Order())
                ->where(['pay_status'=>1, 'order_type'=>OrderEnum::SECKILL_ORDER])
                ->where(['seckill_id'=>$params['id']])
                ->findOrEmpty();

            if (!$order->isEmpty()) {
                throw new Exception('该秒杀活动已产生支付订单,不可删除');
            }

            // 对本秒杀活动的未支付订单进行关闭
            (new Order())
                ->where(['seckill_id' => $params['id']])
                ->where(['pay_status' => 0])
                ->update([
                    'order_status' => OrderEnum::STATUS_CLOSE,
                    'update_time'  => time()
                ]);

            SeckillActivity::duokaiDestroy($params['id']);

            // 删除商品参与活动的信息
            $goodsActivityIds = GoodsActivity::where([
                    'activity_type' => ActivityEnum::SECKILL,
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
     * @notes 确认开启秒杀活动
     * @param $params
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/26 18:05
     */
    public static function open($params)
    {
        try {
            $seckillActivity = (new SeckillActivity())->findOrEmpty($params['id'])->toArray();
            if (!$seckillActivity) {
                throw new Exception("活动不存在,请刷新页面再试");
            }

            if ($seckillActivity['status'] == SeckillEnum::SECKILL_STATUS_END) {
                throw new Exception('活动已结束,不可重新开启');
            }

            SeckillActivity::duokaiUpdate([
                'status' => SeckillEnum::SECKILL_STATUS_CONDUCT,
                'update_time' => time()
            ], ['id' => $params['id']]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 停止关闭活动
     * @param $params
     * @author 张无忌
     * @date 2021/7/23 16:58
     */
    public static function stop($params)
    {
        Db::startTrans();
        try {
            SeckillActivity::duokaiUpdate([
                'status'      => SeckillEnum::SECKILL_STATUS_END,
                'update_time' => time()
            ], ['id'=>$params['id']]);

            // 删除商品参与活动的信息
            $goodsActivityIds = GoodsActivity::where([
                'activity_type' => ActivityEnum::SECKILL,
                'activity_id' => $params['id'],
            ])->column('id');
            if (count($goodsActivityIds)) {
                GoodsActivity::duokaiDestroy($goodsActivityIds);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
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
        $seckillIds = (new SeckillActivity())->whereIn('status',  [0, 1])->column('id');

        if ($seckillIds) {
            $goodsId = array_column($params['goods'], 'goods_id');
            $seckillGoods = (new SeckillGoods())
                ->whereIn('seckill_id', $seckillIds)
                ->whereIn('goods_id', $goodsId)
                ->findOrEmpty()->toArray();

            if ($seckillGoods) {
                $goods = (new Goods())->findOrEmpty($seckillGoods['goods_id'])->toArray();
                return $goods['name'];
            }
        }

        return false;
    }
}
