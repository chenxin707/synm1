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

namespace app\adminapi\validate;


use app\common\enum\OrderEnum;
use app\common\validate\BaseValidate;

class AwardIntegralValidate extends BaseValidate
{
    protected $rule = [
        'open_award' => 'require|in:0,1',
        'award_event' => 'require|in:'.OrderEnum::INTEGRAL_ORDER_PAY.','.OrderEnum::INTEGRAL_ORDER_DELIVERY.','.OrderEnum::INTEGRAL_ORDER_FINISH.','.OrderEnum::INTEGRAL_ORDER_AFTER_SALE_OVER.'',
        'award_ratio' => 'require|float|gt:0',
    ];

    protected $message = [
        'open_award.require' => '消费赠送积分状态不能为空',
        'open_award.in' => '消费赠送积分状态值错误',
        'award_event.require' => '请选者消费赠送积分场景',
        'award_event.in' => '消费赠送积分场景值错误',
        'award_ratio.require' => '请输入消费赠送积分比例',
        'award_ratio.float' => '消费赠送积分比例必须为浮点数',
        'award_ratio.gt' => '消费赠送积分比例必须大于0',
    ];
}