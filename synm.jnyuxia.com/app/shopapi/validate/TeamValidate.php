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

class TeamValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',

        'action'         => 'require|in:buy,settle',
        'pay_way'        => 'number|in:1,2,3',
        'address_id'     => 'number',
        'coupon_id'      => 'number',
        'team_id'        => 'require|number',
        'goods'          => 'require|array|checkGoods'
    ];

    protected $message = [
        'id.require' => '缺少id参数',
        'id.number'  => 'id参数必须为数字',

        'action.require'        => '缺少action参数',
        'action.in'             => 'action参数不在合法范围',
        'pay_way.number'        => 'pay_way参数异常',
        'pay_way.in'            => 'pay_way参数不在合法范围',
        'address_id.number'     => 'address_id参数异常',
        'coupon_id.number'      => 'coupon_id参数异常',
        'team_id.require'       => '缺少team_id参数',
        'team_id.number'        => 'team_id参数异常',
    ];

    /**
     * @notes id验证场景
     * @return TeamValidate
     * @author 张无忌
     * @date 2021/8/3 15:25
     */
    public function sceneId()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 开团场景验证
     * @return TeamValidate
     * @author 张无忌
     * @date 2021/8/3 16:28
     */
    public function sceneKaituan()
    {
        return $this->only([
            'action', 'pay_way', 'address_id', 'goods'
        ]);
    }

    /**
     * @notes 验证商品参数
     * @param $value
     * @author 张无忌
     * @return bool|string
     * @date 2021/7/26 17:22
     */
    public function checkGoods($value)
    {
        if (empty($value)) {
            return '缺少goods相关参数';
        }

        if (empty($value['goods_id'])) {
            return '缺少goods_id参数';
        }

        if (empty($value['item_id'])) {
            return '缺少item_id参数';
        }

        if (empty($value['count'])) {
            return '缺少count参数';
        }

        return true;
    }
}