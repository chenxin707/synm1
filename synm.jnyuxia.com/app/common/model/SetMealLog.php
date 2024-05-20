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


use app\common\enum\SetMealLogEnum;

class SetMealLog extends BaseModel
{
    // 关闭全局查询范围
    protected $globalScope = [];
    protected $deleteTime = false;


    /**
     * @notes 操作人
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ljj
     * @date 2022/3/7 11:00 上午
     */
    public function getOperatorAttr($value,$data)
    {
        if ($data['type'] == SetMealLogEnum::TYPE_SYSTEM) {
            return '系统';
        }
        if ($data['type'] == SetMealLogEnum::TYPE_PLATFORM) {
            return PlatformAdmin::where('id',$data['operator_id'])->value('name');
        }
        if ($data['type'] == SetMealLogEnum::TYPE_SHOP) {
            return Admin::withoutGlobalScope()->where('id',$data['operator_id'])->value('name');
        }
        return '未知';
    }

    /**
     * @notes 到期时间
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2022/3/7 11:03 上午
     */
    public function getExpiresTimeDescAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['expires_time']);
    }

    public function getTimeDescAttr($value,$data) {
        $setMealOrder = SetMealOrder::withoutGlobalScope()->where('id', $data['set_meal_order_id'])->findOrEmpty()->toArray();
        if (empty($setMealOrder)) {
            return '-';
        }
        $setMealOrder['set_meal_price_snapshot'] = json_decode($setMealOrder['set_meal_price_snapshot'], true);
        switch($setMealOrder['set_meal_price_snapshot']['time_type']) {
            case SetMealLogEnum::MONTH:
                return (int)$setMealOrder['set_meal_price_snapshot']['time'].'个月';
            case SetMealLogEnum::YEAR:
                return (int)$setMealOrder['set_meal_price_snapshot']['time'].'年';
            case SetMealLogEnum::FOREVER:
                return '永久';
        }
    }

    public function getOriginExpiresTimeDescAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['origin_expires_time']);
    }
}
