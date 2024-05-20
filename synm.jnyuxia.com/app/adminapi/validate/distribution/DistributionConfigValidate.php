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

namespace app\adminapi\validate\distribution;

use app\common\validate\BaseValidate;

/**
 * 分销配置验证器
 * Class DistributionConfigValidate
 * @package app\adminapi\validate\distribution
 */
class DistributionConfigValidate extends BaseValidate
{
    protected $rule = [
        'switch' => 'require|in:0,1',
        'level'  => 'require|in:1,2',
        'self'   => 'require|in:0,1',
        'open'   => 'require|in:1,2,3',
        'cal_method' => 'require|in:1',
        'settlement_timing' => 'require|in:1',
        'settlement_time' => 'requireWith:settlement_timing|integer',
    ];

    protected $message = [
        'switch.require' => '请选择是否开启分销功能',
        'switch.in' => '分销开关状态值错误',
        'level.require' => '请选择分销层级',
        'level.in' => '分销层级状态值错误',
        'self.require' => '请选择是否开启自购返佣',
        'self.in' => '自购返佣状态值错误',
        'open.require' => '请选择开通分销会员条件',
        'open.in' => '成为分销会员状态值错误',
        'cal_method.require' => '请选择佣金计算方式',
        'cal_method.in' => '佣金计算方式状态值错误',
        'settlement_timing.require' => '请选择结算时机',
        'settlement_timing.in' => '结算时机状态值错误',
        'settlement_time.requireWith' => '请填写结算时间天数',
        'settlement_time.integer' => '结算时间天数须为整数',
    ];
}