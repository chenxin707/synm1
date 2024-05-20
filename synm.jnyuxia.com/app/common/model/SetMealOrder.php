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

namespace app\common\model;

use app\common\enum\PayEnum;
use app\common\enum\SetMealLogEnum;
use app\common\service\FileService;
use think\model\concern\SoftDelete;

/**
 * 套餐续费订单
 */
class SetMealOrder extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    public function getTimeDescAttr($value, $data) {
        $data['set_meal_price_snapshot'] = json_decode($data['set_meal_price_snapshot'], true);
        switch($data['set_meal_price_snapshot']['time_type']) {
            case SetMealLogEnum::MONTH:
                return (int)$data['set_meal_price_snapshot']['time'].'个月';
            case SetMealLogEnum::YEAR:
                return (int)$data['set_meal_price_snapshot']['time'].'年';
            case SetMealLogEnum::FOREVER:
                return '永久';
        }
        return '未知';
    }

    public function getPriceAttr($value, $data) {
        $data['set_meal_price_snapshot'] = json_decode($data['set_meal_price_snapshot'], true);
        return $data['set_meal_price_snapshot']['price'];
    }

    public function getPayWayDescAttr($value, $data) {
        return PayEnum::getPayDesc($data['pay_way']);
    }

    public function getPayStatusDescAttr($value, $data) {
        return PayEnum::getPayStatusDesc($data['pay_status']);
    }

    public function getOrderStatusDescAttr($value, $data) {
        switch($data['order_status']) {
            case 0:
                return '未付款';
            case 1:
                return '已完成';
            case 2:
                return '已关闭';
        }
        return '未知';
    }

    public function getCreateTimeAttr($value, $data) {
        return date('Y-m-d H:i:s', $value);
    }

    public function getShopLogoAttr($value, $data) {
        $defaultLogo = config('project.shop.logo');
        $shopLogo = Config::withoutGlobalScope()->where(['type'=>'shop','name'=>'logo', 'sid' => $data['sid']])->value('value');
        if (empty($shopLogo)) {
            return FileService::getFileUrl($defaultLogo);
        }
        return FileService::getFileUrl($shopLogo);
    }

    public function getSetMealNameAttr($value, $data) {
        $data['set_meal_price_snapshot'] = json_decode($data['set_meal_price_snapshot'], true);
        $setMealName = SetMeal::where('id', $data['set_meal_price_snapshot']['set_meal_id'])->value('name');
        return $setMealName;
    }

    public function getPayTimeDescAttr($value, $data) {
        return date('Y-m-d H:i:s',$data['pay_time']);
    }

    public function getExpiresTimeDescAttr($value, $data) {
        return date('Y-m-d H:i:s',$data['expires_time']);
    }

    public function getVoucherAttr($value, $data) {
        if (empty($value)) {
            return '';
        }
        $value = json_decode($value);
        $data = [];
        foreach($value as $item) {
            $data[] = FileService::getFileUrl($item);
        }
        return $data;
    }
}
