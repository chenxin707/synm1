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



use app\common\enum\AfterSaleLogEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderLog;
use app\common\model\TeamActivity;
use app\common\model\TeamFound;
use app\common\model\TeamGoods;
use app\common\model\TeamGoodsItem;
use app\common\model\TeamJoin;
use app\common\model\User;
use app\common\model\UserAddress;
use app\common\service\after_sale\AfterSaleService;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\shopapi\service\CouponService;
use Exception;
use think\facade\Db;

class TeamLogic
{
    /**
     * @notes 拼团商品详细
     * @param $id
     * @return array|string
     * @author 张无忌
     * @date 2021/8/3 15:53
     */
    public static function detail($id, $userId)
    {
        try {
            // 查询校验商品获取活动
            $teamGoods = (new TeamGoods())->duokaiWithoutGlobalScope()
                ->aliasSid('TG')
                ->alias('TG')
                ->field(['TG.*,TA.name,TA.people_num,TA.min_buy,TA.max_buy,TG.min_team_price',
                    'TA.is_coupon,TA.is_distribution,TA.start_time,TA.end_time,TG.browse_volume,TG.virtual_sales_num,TG.virtual_click_num'])
                ->join('team_activity TA', 'TA.id = TG.team_id')
                ->where('TA.start_time', '<=', time())
                ->where('TA.end_time', '>=', time())
                ->where('TA.status', '>=', TeamEnum::TEAM_STATUS_CONDUCT)
                ->findOrEmpty($id)->toArray();

            if (!$teamGoods) {
                throw new Exception('当前拼团活动已结束');
            }

            // 查询活动商品的规格
            $teamGoodsItem = (new TeamGoodsItem())->withoutField('item_snap')
                ->where('team_id', '=', $teamGoods['team_id'])
                ->where('team_gid', '=', $teamGoods['id'])
                ->select()->toArray();

            // 查询基础商品信息
            $goods = (new Goods())
                ->field([
                    'id,name,code,image,video,video_cover,min_price',
                    'min_lineation_price,total_stock,sales_num,spec_type,content,unit_id,express_type'
                ])->with(['spec_value.spec_list', 'spec_value_list'])
                ->append(['goods_image'])
                ->findOrEmpty($teamGoods['goods_id']);

            $stockShow = ConfigService::get('goods_set', 'is_show', 0);
            $goods['stock_show'] = $stockShow ? true : false;

            // 替换基础商品信息
            foreach ($goods['spec_value_list'] as &$item) {
                $item['image'] = $item['image'] ? $item['image'] : $goods['image'];
                foreach ($teamGoodsItem as $value) {
                    if ($item['goods_id'] == $value['goods_id']
                        and $item['id'] == $value['item_id'])
                    {
                        $item['cost_price'] = $value['sell_price'];
                        $item['sell_price'] = $value['team_price'];
                        unset($value);
                    }
                }
            }

            // 商品信息上扩展活动信息
            $goods['activity'] = [
                'id'              => $teamGoods['team_id'],
                'name'            => $teamGoods['name'],
                'people_num'      => $teamGoods['people_num'],
                'min_buy'         => $teamGoods['min_buy'],
                'max_buy'         => $teamGoods['max_buy'],
                'min_team_price'  => $teamGoods['min_team_price'],
                'is_coupon'       => $teamGoods['is_coupon'],
                'is_distribution' => $teamGoods['is_distribution'],
                'start_time'      => $teamGoods['start_time'],
                'end_time'        => $teamGoods['end_time'],
                'surplus_time'    => $teamGoods['end_time'] - time(),
                'browse_volume'   => $teamGoods['browse_volume'] + $teamGoods['virtual_click_num']
            ];

            //销量
            $goods['sales_num'] = (new TeamGoodsItem())->where(['team_gid'=>$teamGoods['id']])->sum('sales_volume') + $teamGoods['virtual_sales_num'];

            // 获取正在进行中的团
            $teamFound = (new TeamFound())->duokaiWithoutGlobalScope()
                ->aliasSid('TF')
                ->alias('TF')
                ->field(['TF.*', 'U.nickname,U.avatar'])
                ->limit(8)
                ->order('id desc')
                ->where('TF.team_id', '=', $teamGoods['team_id'])
                ->where('TF.people','exp',' > TF.join ')
                ->where([
                    ['status', '=', 0],
                    ['invalid_time', '>=', time()]
                ])->join('user U', 'U.id=TF.user_id')
                ->select()->toArray();

            foreach ($teamFound as &$found) {
                unset($found['shop_id']);
                unset($found['team_sn']);
                unset($found['goods_snap']);
                unset($found['team_end_time']);
                $found['avatar'] = FileService::getFileUrl($found['avatar']);
                $found['surplus_time'] = intval($found['invalid_time'] - time());
            }

            $goods['found'] = $teamFound;
            //商品评价
            $goods['goods_comment'] = GoodsLogic::getComment($goods['id']);

            // 包邮信息
            if ($goods['express_type'] == 1) {
                $goods['free_shipping_tips'] = '免运费';

            } else {
                $goods['free_shipping_tips'] = GoodsLogic::getFreeShippingTips($userId);
            }

            // 记录浏览数
            TeamGoods::duokaiUpdate(['browse_volume'=>['inc', 1]], ['id'=>$id]);

            $goods['unit_name'] = '';
            $hidden[] = 'unit_id';
            if($goods['unit_id']){
                $goods['unit_name'] = $goods->unit->name;
                $hidden[] ='unit';
            }

            return $goods->hidden($hidden)->toArray();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 验证团是否成功(支付回调时调用)
     * @param $found_id
     * @author 张无忌
     * @date 2021/8/4 11:56
     */
    public static function checkTeamSuccess($found_id)
    {
        Db::startTrans();
        try {
            $time = time();

            $teamFound = (new TeamFound())->findOrEmpty($found_id)->toArray();
            if ($teamFound['people'] == $teamFound['join']) {
                // 获取参团记录数据
                $teamJoin = (new TeamJoin())->duokaiWithoutGlobalScope()
                    ->aliasSid('TJ')
                    ->alias('TJ')
                    ->field('TJ.*,O.order_status,O.pay_status')
                    ->join('order O', 'O.id = TJ.order_id')
                    ->where(['TJ.found_id' => $found_id])
                    ->select()->toArray();

                // 获取参团已支付数量
                $payTeamCount = 0;
                foreach ($teamJoin as $item) {
                    if ($item['order_status'] == 1 and $item['pay_status'] == 1) {
                        $payTeamCount += 1;
                    }
                }

                // 满足条件: 拼团成功
                if ($payTeamCount == $teamFound['people']) {
                    TeamFound::duokaiUpdate([
                        'status' => TeamEnum::TEAM_FOUND_SUCCESS,
                        'team_end_time' => $time
                    ], ['id' => $found_id]);

                    foreach ($teamJoin as $item) {
                        // 标记拼团状态为成功
                        TeamJoin::duokaiUpdate([
                            'status' => TeamEnum::TEAM_FOUND_SUCCESS,
                            'team_end_time' => time(),
                            'update_time' => $time
                        ], ['id' => $item['id']]);

                        // 标记拼团订单状态成功
                        Order::duokaiUpdate([
                            'is_team_success' => 1,
                            'update_time' => $time
                        ], ['id' => $item['order_id']]);
                    }
                }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }
    }


    /**
     * @notes 标记失败团
     * @param $order_id
     * @return bool
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/10/12 15:52
     */
    public static function signFailTeam($order_id)
    {
        // 获取拼团订单信息
        $order = (new Order())->where(['id'=>$order_id])->findOrEmpty()->toArray();
        if ($order['order_type'] != OrderEnum::TEAM_ORDER) {
            return false;
        }

        // 获取开团信息
        $teamFound = (new TeamFound())->where(['id'=>$order['team_found_id']])->findOrEmpty()->toArray();

        // 如果团不是成功的团,则结束团,并且退款, 否则是成功的团,只给当前订单退款
        if ($teamFound['status'] != TeamEnum::TEAM_FOUND_SUCCESS) {
            // 结束拼团标记失败
            TeamFound::duokaiUpdate(['status' => TeamEnum::TEAM_FOUND_FAIL, 'team_end_time' => time()], ['id' => $order['team_found_id']]);
            // 参团的人标记失败
            $teamJoin = (new TeamJoin())->where(['found_id' => $teamFound['id']])->select()->toArray();
            foreach ($teamJoin as $item) {
                // 标记参团失败
                TeamJoin::duokaiUpdate([
                    'status' => TeamEnum::TEAM_FOUND_FAIL,
                    'team_end_time' => time()
                ], ['id' => $item['id']]);

                // 获取团的订单
                $teamOrder = (new Order())->where(['id' => $item['order_id']])->findOrEmpty()->toArray();

                // 处于已支付状态的发起整单售后
                if ($teamOrder['pay_status'] == PayEnum::ISPAID) {
                    AfterSaleService::orderRefund([
                        'order_id' => $teamOrder['id'],
                        'scene' => AfterSaleLogEnum::BUYER_CANCEL_ORDER
                    ]);
                }

                //更新订单为已关闭
                Order::duokaiUpdate([
                    'is_team_success' => 2,
                    'order_status' => OrderEnum::STATUS_CLOSE,
                    'cancel_time' => time()
                ], ['id' => $teamOrder['id']]);

                // 订单日志
                (new OrderLog())->record([
                    'type' => OrderLogEnum::TYPE_USER,
                    'channel' => OrderLogEnum::USER_CANCEL_ORDER,
                    'order_id' => $teamOrder['id'],
                    'operator_id' => $teamOrder['user_id'],
                ]);
            }

        } else {

            // 处于已支付状态的发起整单售后
            if ($order['pay_status'] == PayEnum::ISPAID) {
                AfterSaleService::orderRefund([
                    'order_id' => $order['id'],
                    'scene'    => AfterSaleLogEnum::BUYER_CANCEL_ORDER
                ]);
            }

            //更新订单为已关闭
            Order::duokaiUpdate([
                'is_team_success' => 2,
                'order_status' => OrderEnum::STATUS_CLOSE,
                'cancel_time' => time()
            ], ['id' => $order['id']]);

            // 订单日志
            (new OrderLog())->record([
                'type'        => OrderLogEnum::TYPE_USER,
                'channel'     => OrderLogEnum::USER_CANCEL_ORDER,
                'order_id'    => $order['id'],
                'operator_id' => $order['user_id'],
            ]);

        }

        return true;
    }
}
