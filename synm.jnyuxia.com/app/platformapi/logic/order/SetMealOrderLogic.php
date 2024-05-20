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

namespace app\platformapi\logic\order;

use app\common\enum\PayEnum;
use app\common\enum\SetMealLogEnum;
use app\common\logic\BaseLogic;
use app\common\model\PlatformShop;
use app\common\model\SetMeal;
use app\common\model\SetMealLog;
use app\common\model\SetMealOrder;
use app\common\service\FileService;
use think\Exception;
use think\facade\Db;


class SetMealOrderLogic extends BaseLogic
{
    public static function searchParam() {
        $setMeal = SetMeal::field('id, name')->order('id', 'desc')->select()->toArray();
        $setMealData = [];
        foreach($setMeal as $item) {
            $setMealData[''.$item['id']] = $item['name'];
        }
        return [
            'set_meal' => $setMealData,
            'pay_status' => [
                '0' => '未支付',
                '1' => '已支付',
            ],
            'pay_way' => [
                '2' => '微信支付',
                '3' => '支付宝支付',
                '5' => '对公转账'
            ],
        ];
    }

    /**
     * @notes 确认支付
     */
    public static function confirmPay($params) {
        Db::startTrans();
        try {
            $setMealOrder = SetMealOrder::withoutGlobalScope()->findOrEmpty($params['set_meal_order_id'])->toArray();
            if ($setMealOrder['pay_status'] == PayEnum::ISPAID) {
                throw new Exception('订单已支付');
            }
            if ($setMealOrder['order_status'] == 2) {
                throw new Exception('订单已关闭');
            }
            foreach($params['voucher'] as $key => $value) {
                $params['voucher'][$key] = FileService::setFileUrl($value);
            }
            SetMealOrder::duokaiUpdate([
                'id' => $params['set_meal_order_id'],
                'voucher' => json_encode($params['voucher']),
                'order_status' => 1,
                'pay_status' => PayEnum::ISPAID,
                'pay_time' => time()
            ], [], [], '', false);

            self::payHandle($setMealOrder);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 续费订单详情
     */
    public static function detail($params) {
        $data  = SetMealOrder::withoutGlobalScope()
            ->alias('smo')
            ->leftJoin('platform_shop ps', 'smo.sid = ps.id')
            ->field('smo.sid,smo.id,smo.pay_status,smo.order_status,smo.sn,smo.pay_way,smo.create_time,smo.pay_time,smo.voucher,smo.remark,smo.set_meal_price_snapshot,ps.name as shop_name, ps.expires_time')
            ->append(['pay_status_desc', 'order_status_desc', 'pay_way_desc', 'pay_time_desc', 'set_meal_name', 'time_desc', 'price', 'shop_logo', 'expires_time_desc'])
            ->where('smo.id', $params['set_meal_order_id'])
            ->findOrEmpty()
            ->toArray();
        return $data;
    }

    /**
     * @notes 确认支付
     */
    public static function remark($params) {
        try {
            SetMealOrder::duokaiUpdate([
                'id' => $params['set_meal_order_id'],
                'remark' => $params['remark']
            ], [], [], '', false);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 支付后操作
     */
    public static function payHandle($setMealOrder) {
        $setMealOrder['set_meal_price_snapshot'] = json_decode($setMealOrder['set_meal_price_snapshot'], true);
        $newSetMeal = SetMeal::findOrEmpty($setMealOrder['set_meal_price_snapshot']['set_meal_id'])->toArray();
        $shop = PlatformShop::findOrEmpty($setMealOrder['sid']);
        $originSetMeal = SetMeal::findOrEmpty($shop['set_meal_id'])->toArray();
        $originExpiresTime = $shop->getData('expires_time');
        // 计算到期时间
        switch($setMealOrder['set_meal_price_snapshot']['time_type']) {
            case 1: // 月
                if ($shop->set_meal_id == $newSetMeal['id']) { // 相同套餐，到期时间+续费时间
                    $expires_time = $originExpiresTime + (strtotime('+'. (int)$setMealOrder['set_meal_price_snapshot']['time'] . 'months') - time());
                } else {  // 不同套餐，当前时间+续费时间
                    $expires_time = strtotime('+'. (int)$setMealOrder['set_meal_price_snapshot']['time'] . 'months');
                }
                break;
            case 2: // 年
                if ($shop->set_meal_id == $newSetMeal['id']) { // 相同套餐，到期时间+续费时间
                    $expires_time = $originExpiresTime + (strtotime('+'. (12 * (int)$setMealOrder['set_meal_price_snapshot']['time']) . 'months') - time());
                } else {  // 不同套餐，当前时间+续费时间
                    $expires_time = strtotime('+'. (12 * (int)$setMealOrder['set_meal_price_snapshot']['time']) . 'months');
                }
                break;
            case 3: // 永久
                $expires_time = 4102415999; // 2099-12-31 23:59:59
                break;
        }
        // 更新店铺套餐信息
        $shop->set_meal_id = $newSetMeal['id'];
        $shop->expires_time = $expires_time;
        $shop->duokaiSave();

        // 添加续费记录
        SetMealLog::duokaiCreate([
            'sid' => $setMealOrder['sid'],
            'type' => SetMealLogEnum::TYPE_SHOP,
            'operator_id' => $setMealOrder['operator_id'],
            'origin_set_meal_id' => $originSetMeal['id'],
            'set_meal_id' => $newSetMeal['id'],
            'set_meal_order_id' => $setMealOrder['id'],
            'origin_set_meal_name' => $originSetMeal['name'],
            'set_meal_name' => $newSetMeal['name'],
            'origin_expires_time' => $originExpiresTime,
            'expires_time' => $expires_time,
            'content' => '商户续费',
            'create_time' => time(),
            'channel' => 801,
        ], [], false, '', false);
    }
}
