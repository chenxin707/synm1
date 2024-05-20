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

namespace app\adminapi\validate\sign;

use app\common\enum\YesNoEnum;
use app\common\model\SignDaily;
use app\common\validate\BaseValidate;
use think\facade\Validate;

/**
 * 签到验证器
 * Class SignValidate
 * @package app\adminapi\validate\sign
 */
class SignValidate extends BaseValidate
{
    protected $rule = [
        'is_open' => 'require',
        'daily' => 'require|array|checkDaily',
        'remark' => 'require',
        'days' => 'require|integer|egt:2|checkDays',
        'integral_status' => 'require|in:0,1',
        'integral' => 'require|integer|gt:0',
        'id' => 'require',
        'sort' => 'number',
        'continuous' => 'array|checkContinuous',
    ];

    protected $message = [
        'is_open.require' => '请选择签到状态',
        'daily.require' => '请填写每日签到规则',
        'daily.array' => '每日签到规则格式须为数组',
        'remark.require' => '请填写签到说明',
        'days.require' => '请填写连续签到天数',
        'days.integer' => '连续签到天数须为整数',
        'days.egt' => '连续签到天数须大于或等于2天',
        'integral_status.require' => '请选择是否赠送积分',
        'integral_status.in' => '赠送积分状态错误',
        'integral.require' => '请填写赠送积分数量',
        'integral.integer' => '赠送积分数量须为整数',
        'integral.gt' => '赠送积分数量须大于0',
        'id.require' => '参数缺失',
        'sort.number' => '排序值错误',
        'continuous.array' => '连续签到规则格式须为数组',
    ];

    /**
     * @notes 设置签到规则场景
     * @param $value
     * @param $rule
     * @param $data
     * @return SignValidate
     * @author Tab
     * @date 2021/8/16 9:52
     */
    public function sceneSetConfig()
    {
        return $this->only(['is_open', 'daily', 'remark', 'continuous']);
    }

    /**
     * @notes 添加连续签到规则场景
     * @return SignValidate
     * @author Tab
     * @date 2021/8/16 15:23
     */
    public function sceneAdd()
    {
        return $this->only(['days', 'integral_status', 'integral']);
    }

    /**
     * @notes 编辑连续签到规则场景
     * @return SignValidate
     * @author Tab
     * @date 2021/8/16 15:42
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'days', 'integral_status', 'integral']);
    }

    /**
     * @notes 查看连续签到规则详情场景
     * @return SignValidate
     * @author Tab
     * @date 2021/8/16 11:36
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 删除连续签到规则场景
     * @return SignValidate
     * @author Tab
     * @date 2021/8/16 15:49
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验每日签到参数
     * @param $value
     * @return bool|string
     * @author Tab
     * @date 2021/8/16 10:06
     */
    public function checkDaily($value)
    {
        if(
            !isset($value['integral_status']) ||
            !isset($value['integral'])
        ) {
            return '每日签到参数缺失';
        }
        if(
            !in_array($value['integral_status'], [YesNoEnum::YES, YesNoEnum::NO]) ||
            $value['integral'] < 0
        ) {
            return '每日签到参数不符合要求';
        }
        return true;
    }

    /**
     * @notes 校验连续签到天数
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/16 15:32
     */
    public function checkDays($value, $rule, $data)
    {
        $where[] = ['days', '=', $value];
        if(isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $signDaily = SignDaily::where($where)->findOrEmpty();
        if(!$signDaily->isEmpty()) {
            return '已有相同的连续签到规则';
        }
        return true;
    }


    /**
     * @notes 校验签到规则
     * @param $value
     * @return bool|string
     * @author ljj
     * @date 2023/5/6 7:10 下午
     */
    public function checkContinuous($value)
    {
        $days_arr = array_column($value,'days');
        if (count($days_arr) != count(array_unique($days_arr))) {
            return '存在重复的连续天数';
        }
        foreach ($value as $key=>$val) {
            if(!isset($val['days']) || $val['days'] == '' || !Validate::number($val['days'])) {
                return '第'.($key+1).'行连续签到规则的连续天数错误';
            }
            if(!isset($val['integral']) || $val['integral'] == '' || !Validate::number($val['integral'])) {
                return '第'.($key+1).'行连续签到规则的连续奖励错误';
            }
            if(isset($val['sort']) && $val['sort'] != '' && !Validate::number($val['sort'])) {
                return '第'.($key+1).'行连续签到规则的排序错误';
            }
        }

        return true;
    }
}