<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\shopapi\logic;


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
use app\common\model\SelffetchVerifier;
use app\common\model\Verification;
use app\common\service\ConfigService;

class VerificationLogic extends BaseLogic
{
    /**
     * @notes 提货核销
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/27 5:38 下午
     */
    public static function verification($params)
    {
        $result = Order::where('pickup_code',$params['pickup_code'])
            ->with(['order_goods' => function ($query) {
                $query->field('order_id,goods_snap,goods_name,goods_num')
                    ->append(['goods_image', 'spec_value_str'])
                    ->hidden(['goods_snap']);
            }])
            ->append(['verification_status_desc'])
            ->hidden(['verification_status'])
            ->field('id,address,verification_status')
            ->find()
            ->toArray();

        //校验是否有售后
        if ($params['confirm'] != 1) {
            $order_goods = OrderGoods::where(['order_id'=>$result['id']])->select()->toArray();
            foreach ($order_goods as $goods) {
                $after_sale = AfterSale::where(['order_goods_id' => $goods['id'], 'order_id' => $goods['order_id'],'status'=>[AfterSaleEnum::STATUS_ING,AfterSaleEnum::STATUS_SUCCESS]])->findOrEmpty();
                if (!$after_sale->isEmpty()) {
                    return ['code'=>10,'msg'=>'有商品处于售后中或已售后成功，请谨慎操作'];
                }
            }
        }

        $result['contact'] = $result['address']->contact;
        unset($result['address']);

        return $result;
    }

    /**
     * @notes 确认提货
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/27 6:05 下午
     */
    public static function verificationConfirm($params)
    {
        try {
            $order = Order::find($params['id']);
            $selffetch_verifier = SelffetchVerifier::where(['user_id'=>$params['user_id'],'selffetch_shop_id'=>$order['selffetch_shop_id'],'status'=>1])->find();

            //添加核销记录
            $snapshot = [
                'sn' => $selffetch_verifier['sn'],
                'name' => $selffetch_verifier['name']
            ];
            $verification = new Verification;
            $verification->order_id = $order['id'];
            $verification->selffetch_shop_id = $order['selffetch_shop_id'];
            $verification->handle_id = $params['user_id'];
            $verification->verification_scene = VerificationEnum::TYPE_USER;
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
                'type' => OrderLogEnum::TYPE_USER,
                'channel' => OrderLogEnum::USER_VERIFICATION,
                'order_id' => $order['id'],
                'operator_id' => $params['user_id'],
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
}