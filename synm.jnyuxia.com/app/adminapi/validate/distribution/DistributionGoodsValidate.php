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
 * 分销商品验证器
 * Class DistributionGoodsValidate
 * @package app\adminapi\validate\distribution
 */
class DistributionGoodsValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require',
        'is_distribution' => 'require|in:0,1',
        'rule' => 'require|in:1,2',
        'ratio_data' => 'requireIf:rule,2|array',
        'ids' => 'require|array',
        'id'  => 'require',
    ];

    protected $message = [
        'goods_id.require' => '商品id不存在',
        'is_distribution.require' => '分销状态不能为空',
        'is_distribution.in' => '分销状态值错误',
        'rule.require' => '请选择佣金规则',
        'rule.in' => '佣金规则值错误',
        'ratio_data.requireIf' => '分佣比例数据不存在',
        'ids.require' => '商品id不能为空',
        'ids.array' => '商品id数据格式不正确',
        'id.require' => '参数缺失',
    ];

    /**
     * @notes 设置佣金场景
     * @return DistributionGoodsValidate
     * @author Tab
     * @date 2021/7/23 16:42
     */
    public function sceneSet()
    {
        return $this->only(['goods_id', 'is_distribution', 'rule', 'ratio_data']);
    }

    /**
     * @notes 参与/不参与分销的场景
     * @return DistributionGoodsValidate
     * @author Tab
     * @date 2021/7/23 17:36
     */
    public function sceneJoin()
    {
        return $this->only(['ids', 'is_distribution']);
    }

    /**
     * @notes 获取分销商品详情场景
     * @return DistributionGoodsValidate
     * @author Tab
     * @date 2021/7/23 18:18
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }
}