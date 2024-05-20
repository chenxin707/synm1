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

use app\common\model\DistributionLevel;
use app\common\validate\BaseValidate;

/**
 * 分销会员等级验证器
 * Class DistributionLevelValidate
 * @package app\adminapi\validate\distribution
 */
class DistributionLevelValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|checkName',
        'weights' => 'require|integer|gt:1|checkWeights',
        'self_ratio' => 'require|between:0,100',
        'first_ratio' => 'require|between:0,100',
        'second_ratio' => 'require|between:0,100|checkRatioSum',
        'update_relation' => 'require|integer|in:1,2',
        'update_condition' => 'require|array|checkCondition',
        'id' => 'require',
    ];

    protected $message = [
        'name.require' => '请填写等级名称',
        'weights.require' => '请输入级别',
        'weights.integer' => '级别须为整型',
        'weights.gt' => '级别须大于1',
        'self_ratio.require' => '请输入自购佣金比例',
        'self_ratio.between' => '自购佣金比例须在0-100之间',
        'first_ratio.require' => '请输入一级佣金比例',
        'first_ratio.between' => '一级佣金比例须在0-100之间',
        'second_ratio.require' => '请输入二级佣金比例',
        'second_ratio.between' => '二级佣金比例须在0-100之间',
        'update_relation.require' => '请选择升级关系',
        'update_relation.in' => '升级关系状态值错误',
        'update_condition.require' => '请选择升级条件',
        'update_condition.array' => '升级条件类型错误',
        'id.require' => '参数缺失',
    ];

    /**
     * @notes 添加分销会员等级场景
     * @return DistributionLevelValidate
     * @author Tab
     * @date 2021/7/22 11:18
     */
    public function sceneAdd()
    {
        return $this->only(['name', 'weights', 'self_ratio', 'first_ratio', 'second_ratio', 'update_condition', 'update_relation']);
    }

    /**
     * @notes 获取分销会员等级详情
     * @return DistributionLevelValidate
     * @author Tab
     * @date 2021/7/22 14:50
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 编辑分销会员等级场景
     * @return DistributionLevelValidate
     * @author Tab
     * @date 2021/7/22 15:43
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'weights', 'self_ratio', 'first_ratio', 'second_ratio'])
            ->remove('weights', 'gt');
    }

    /**
     * @notes 删除分销会员等级
     * @return DistributionLevelValidate
     * @author Tab
     * @date 2021/7/22 16:28
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验等级名称
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/7/22 11:13
     */
    public function checkName($value, $rule, $data)
    {
        $where = [['name', '=', $value]];
        if(isset($data['id'])) {
            // 编辑的场景
            $where[] = ['id', '<>', $data['id']];
        }
        $level = DistributionLevel::where($where)->findOrEmpty();
        if(!$level->isEmpty()) {
            return '等级名称已存在';
        }
        return true;
    }

    /**
     * @notes 校验等级级别
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/7/22 11:16
     */
    public function checkWeights($value, $rule, $data)
    {
        $where = [['weights', '=', $value]];
        if(isset($data['id'])) {
            // 编辑的场景
            $where[] = ['id', '<>', $data['id']];
        }
        $level = DistributionLevel::where($where)->findOrEmpty();
        if(!$level->isEmpty()) {
            return '等级级别已存在';
        }
        return true;
    }

    /**
     * @notes 校验升级条件
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/22 16:13
     */
    public function checkCondition($value)
    {
        if(!count($value)) {
            return '请选择升级条件';
        }
        return true;
    }

    /**
     * @notes 校验佣金总比例
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/12/28 18:55
     */
    public function checkRatioSum($value, $rule, $data)
    {
        $selfRatio = $data['self_ratio'] ?: 0;
        $firstRatio = $data['first_ratio'] ?: 0;
        $secondRatio = $data['second_ratio'] ?: 0;
        if ($selfRatio + $firstRatio + $secondRatio > 100) {
            return '等级佣金比例总和不能超过100%';
        }
        return true;
    }
}