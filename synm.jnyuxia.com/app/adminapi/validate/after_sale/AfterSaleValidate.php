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

namespace app\adminapi\validate\after_sale;

use app\common\validate\BaseValidate;

/**
 * 售后验证器
 * Class AfterSaleValidate
 * @package app\adminapi\validate\after_sale
 */
class AfterSaleValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'refund_total_amount' => 'require|egt:0',
        'refund_way' => 'require|in:1,2'
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'refund_total_amount.require' => '请输入退款金额',
        'refund_total_amount.egt' => '退款金额须大于等于0',
        'refund_way.require' => '请选择退款方式',
        'refund_way.in' => '退款方式状态值有误',
    ];

    /**
     * @notes 卖家同意售后场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/2 19:22
     */
    public function sceneAgree()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 卖家拒绝售后场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/2 19:55
     */
    public function sceneRefuse()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 卖家拒绝收货场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 11:56
     */
    public function sceneRefuseGoods()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 卖家确认收货场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 14:03
     */
    public function sceneConfirmGoods()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 卖家同意退款场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 14:19
     */
    public function sceneAgreeRefund()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 卖家拒绝退款场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 14:29
     */
    public function sceneRefuseRefund()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 卖家确认退款场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 10:47
     */
    public function sceneConfirmRefund()
    {
        return $this->only(['id', 'refund_total_amount', 'refund_way']);
    }

    /**
     * @notes 售后详情场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/9 18:12
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }
}