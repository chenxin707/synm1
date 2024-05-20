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

namespace app\shopapi\validate;

use app\common\validate\BaseValidate;

/**
 * 售后验证器
 * Class AfterSaleValidate
 * @package app\shopapi\validate
 */
class AfterSaleValidate extends BaseValidate
{
    protected $rule = [
        'refund_method' => 'require|in:1,2',
        'refund_reason' => 'require',
        'order_goods_id' => 'require',
        'id' => 'require',
        'express_name' => 'require',
        'invoice_no' => 'require',
        'type' => 'require',
        'voucher' => 'array|checkCount',
    ];

    protected $message = [
        'refund_method.require' => '请选择售后方式',
        'refund_method.in' => '售后方式错误',
        'refund_reason.require' => '请输入退款原因',
        'order_goods_id.require' => '请提供子订单ID',
        'id.require' => '参数缺失',
        'express_name.require' => '请填写快递公司',
        'invoice_no.require' => '请填写物流单号',
        'type.require' => '请选择列表类型',
        'voucher.array' => '凭证须为数组格式',
    ];

    /**
     * @notes 获取子订单商品信息场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/7/31 17:50
     */
    public function sceneOrderGoodsInfo()
    {
        return $this->only(['order_goods_id', 'refund_method'])
            ->remove('refund_method', 'require');
    }

    /**
     * @notes 申请商品售后场景
     * @author Tab
     * @date 2021/7/30 19:07
     */
    public function sceneApply()
    {
        return $this->only(['refund_method', 'refund_reason', 'order_goods_id', 'voucher']);
    }

    /**
     * @notes 买家取消售后场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 10:07
     */
    public function sceneCancel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 买家确认退货场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/3 11:24
     */
    public function sceneReturnGoods()
    {
        return $this->only(['id', 'express_name', 'invoice_no']);
    }

    /**
     * @notes 列表场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/10 10:17
     */
    public function sceneLists()
    {
        return $this->only(['type']);
    }

    /**
     * @notes 售后详情场景
     * @return AfterSaleValidate
     * @author Tab
     * @date 2021/8/10 15:13
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 凭证数量限制
     * @param $voucher
     * @return bool|string
     * @author Tab
     * @date 2021/8/26 10:36
     */
    public function checkCount($voucher)
    {
        if (count($voucher) > 3) {
            return '最多上传3张凭证';
        }
        return true;
    }
}