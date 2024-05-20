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

namespace app\adminapi\logic\selffetch_shop;


use app\common\enum\AfterSaleEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\VerificationEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\AfterSale;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\OrderLog;
use app\common\model\Verification;
use app\common\service\ConfigService;

class VerificationLogic extends BaseLogic
{
    /**
     * @notes 提货核销
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/12 11:57 上午
     */
    public function verification($params)
    {
        try {
            //校验是否有售后
            if ($params['confirm'] != 1) {
                $order_goods = OrderGoods::where(['order_id'=>$params['id']])->select()->toArray();
                foreach ($order_goods as $goods) {
                    $after_sale = AfterSale::where(['order_goods_id' => $goods['id'], 'order_id' => $goods['order_id'],'status'=>[AfterSaleEnum::STATUS_ING,AfterSaleEnum::STATUS_SUCCESS]])->findOrEmpty();
                    if (!$after_sale->isEmpty()) {
                        return ['msg'=>'有商品处于售后中或已售后成功，请谨慎操作'];
                    }
                }
            }

            $order = Order::find($params['id']);

            //添加核销记录
            $snapshot = [
                'sn' => $params['admin_info']['account'],
                'name' => $params['admin_info']['name']
            ];
            $verification = new Verification;
            $verification->order_id = $params['id'];
            $verification->selffetch_shop_id = $order['selffetch_shop_id'];
            $verification->handle_id = $params['admin_info']['admin_id'];
            $verification->verification_scene = VerificationEnum::TYPE_ADMIN;
            $verification->snapshot = json_encode($snapshot);
            $verification->duokaiSave();

            //更新订单状态
            $order->order_status = OrderEnum::STATUS_FINISH;
            $order->verification_status = OrderEnum::WRITTEN_OFF;
            $order->confirm_take_time = time();
            $order->after_sale_deadline = self::getAfterSaleDeadline();
            $order->duokaiSave();

            //订单日志
            (new OrderLog())->record([
                'type' => OrderLogEnum::TYPE_SHOP,
                'channel' => OrderLogEnum::SHOP_VERIFICATION,
                'order_id' => $params['id'],
                'operator_id' => $params['admin_info']['admin_id'],
            ]);

            return true;

        } catch (\Exception $e) {
            //错误
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 获取当前售后
     * @return float|int
     * @author ljj
     * @date 2021/9/1 3:09 下午
     */
    public static function getAfterSaleDeadline()
    {
        //是否关闭维权
        $afterSale = ConfigService::get('transaction', 'after_sales');
        //可维权时间
        $afterSaleDays = ConfigService::get('transaction', 'after_sales_days');

        if ($afterSale == YesNoEnum::NO) {
            $afterSaleDeadline = time();
        } else {
            $afterSaleDeadline = ($afterSaleDays * 24 * 60 * 60) + time();
        }

        return $afterSaleDeadline;
    }

    /**
     * @notes 核销查询
     * @param $params
     * @return mixed
     * @author ljj
     * @date 2021/8/27 10:27 上午
     */
    public function verificationQuery($params)
    {
        $result = Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('selffetch_shop ss', 'ss.id = o.selffetch_shop_id')
            ->join('verification v', 'o.id = v.order_id')
            ->with(['order_goods' => function($query){
                $query->field('order_id,goods_snap,goods_name,goods_price,goods_num,total_pay_price')->append(['goods_image','spec_value_str'])->hidden(['goods_snap']);
            }])
            ->where('o.id',$params['id'])
            ->field('o.id,o.address,ss.name as shop_name,v.snapshot,v.create_time as verification_time')
            ->json(['snapshot','address'],true)
            ->find()
            ->toArray();

        $result['verification_time'] = date('Y-m-d H:i:s',$result['verification_time']);
        $result['contact'] = $result['address']['contact'];
        $result['mobile'] = $result['address']['mobile'];
        $result['verifier_sn'] = $result['snapshot']['sn'];
        $result['verifier_name'] = $result['snapshot']['name'];
        unset($result['snapshot'],$result['address']);

        return $result;
    }

    /**
     * @notes 查看核销详情
     * @param $params
     * @return mixed
     * @author ljj
     * @date 2021/8/27 10:47 上午
     */
    public function verificationDetail($params)
    {
        $result = Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('selffetch_shop ss', 'ss.id = o.selffetch_shop_id')
            ->with(['order_goods' => function($query){
                $query->field('order_id,goods_snap,goods_name,goods_price,goods_num,total_pay_price')->append(['goods_image','spec_value_str'])->hidden(['goods_snap']);
            }])
            ->where('o.id',$params['id'])
            ->field('o.id,o.address,ss.name as shop_name,o.pickup_code')
            ->json(['address'],true)
            ->find()
            ->toArray();

        $result['contact'] = $result['address']['contact'];
        $result['mobile'] = $result['address']['mobile'];
        unset($result['address']);

        return $result;
    }
}