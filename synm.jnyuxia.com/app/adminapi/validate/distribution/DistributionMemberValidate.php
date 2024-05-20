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
 * 分销会员验证器
 * Class DistributionMemberValidate
 * @package app\adminapi\validate
 */
class DistributionMemberValidate extends BaseValidate
{
    protected $rule = [
        'ids' => 'require|array',
        'level_id' => 'require',
        'user_id' => 'require',
    ];

    protected $message = [
        'ids.require' => '参数缺失',
        'ids.array' => '参数须为数组格式',
        'level_id.require' => '请选择分销等级',
        'user_id.require' => '参数缺失',
    ];

    /**
     * @notes 开通分销
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 16:54
     */
    public function sceneOpen()
    {
        return $this->only(['ids', 'level_id']);
    }

    /**
     * @notes 查看分销商详情
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 16:56
     */
    public function sceneDetail()
    {
        return $this->only(['user_id']);
    }

    /**
     * @notes 调整分销商等级界面
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 18:45
     */
    public function sceneAdjustLevelInfo()
    {
        return $this->only(['user_id']);
    }

    /**
     * @notes 调整分销商等级
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 18:57
     */
    public function sceneAdjustLevel()
    {
        return $this->only(['user_id', 'level_id']);
    }

    /**
     * @notes 冻结/解冻资格
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 19:10
     */
    public function sceneFreeze()
    {
        return $this->only(['user_id']);
    }

    /**
     * @notes 查看下级
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 19:23
     */
    public function sceneFans()
    {
        return $this->only(['user_id']);
    }

    /**
     * @notes 下级列表
     * @return DistributionMemberValidate
     * @author Tab
     * @date 2021/9/14 20:11
     */
    public function sceneFansLists()
    {
        return $this->only(['user_id']);
    }
}