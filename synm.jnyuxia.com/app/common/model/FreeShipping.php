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

namespace app\common\model;

use app\common\enum\FreeShippingEnum;
use think\model\concern\SoftDelete;

class FreeShipping extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';

    /**
     * @notes 开始时间
     */
    public function getStartTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['start_time']);
    }

    /**
     * @notes 结束时间
     */
    public function getEndTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['end_time']);
    }



    /**
     * @notes 包邮区域
     */
    public function getRegionAttr($value, $data) {
        return json_decode($data['region']);
    }

    /**
     * @notes 包邮活动订单数量
     */
    public function getOrderNumAttr($value, $data) {
        return FreeShippingOrder::where('free_shpping_id', $data['id'])->count('order_id');
    }

    /**
     * @notes 包邮活动订单总金额
     */
    public function getOrderAmountAttr($value, $data) {
        return FreeShippingOrder::where('free_shpping_id', $data['id'])->sum('amount');
    }

    /**
     * @notes 按钮状态
     */
    public function getBtnAttr($value, $data) {
        switch($data['status']) {
            case FreeShippingEnum::WAIT:
                return [
                    'detail_btn' => true,
                    'edit_btn' => true,
                    'start_btn' => true,
                    'end_btn' => false,
                    'delete_btn' => true,
                ];
            case FreeShippingEnum::ING:
                return [
                    'detail_btn' => true,
                    'edit_btn' => true,
                    'start_btn' => false,
                    'end_btn' => true,
                    'delete_btn' => true,
                ];
            case FreeShippingEnum::END:
                return [
                    'detail_btn' => true,
                    'edit_btn' => false,
                    'start_btn' => false,
                    'end_btn' => false,
                    'delete_btn' => true,
                ];
        }
    }
}
