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
 * 砍价验证器
 * Class BargainValidate
 * @package app\shopapi\validate
 */
class BargainValidate extends BaseValidate
{
    protected $rule = [
        'activity_id' => 'require',
        'goods_id' => 'require',
        'item_id' => 'require',
        'goods_num' => 'require|integer',
        'initiate_id' => 'require',
        'type' => 'require',
        'delivery_type' => 'require'
    ];

    protected $message = [
        'activity_id.require' => '砍价活动id缺失',
        'goods_id.require' => '商品id缺失',
        'item_id.require' => '规格id缺失',
        'goods_num.require' => '商品数量',
        'goods_num.integer' => '商品数量须为整型',
        'initiate_id.require' => '砍价记录id缺失',
        'type.require' => '购买类型缺失',
        'delivery_type.require' => '请选择配送方式',
    ];

    /**
     * @notes 砍价商品详情
     * @return BargainValidate
     * @author Tab
     * @date 2021/8/28 15:46
     */
    public function sceneDetail()
    {
        return $this->only(['activity_id', 'goods_id']);
    }

    /**
     * @notes 发起砍价场景
     * @return BargainValidate
     * @author Tab
     * @date 2021/8/28 16:36
     */
    public function sceneInitiate()
    {
        return $this->only(['activity_id', 'item_id', 'goods_num']);
    }

    /**
     * @notes 帮助砍价场景
     * @return BargainValidate
     * @author Tab
     * @date 2021/8/30 11:12
     */
    public function sceneHelp()
    {
        return $this->only(['initiate_id']);
    }

    /**
     * @notes 查看砍价进度
     * @return BargainValidate
     * @author Tab
     * @date 2021/9/18 14:39
     */
    public function sceneBargainProgress()
    {
        return $this->only(['initiate_id']);
    }

    /**
     * @notes 分享帮砍详情
     * @return BargainValidate
     * @author Tab
     * @date 2021/9/18 17:17
     */
    public function sceneShareDetail()
    {
        return $this->only(['initiate_id']);
    }

    /**
     * @notes 砍价结算
     * @return BargainValidate
     * @author Tab
     * @date 2021/9/18 18:09
     */
    public function sceneSettle()
    {
        return $this->only(['initiate_id', 'type']);
    }

    /**
     * @notes 砍价下单
     * @return BargainValidate
     * @author Tab
     * @date 2021/9/23 14:46
     */
    public function sceneBuy()
    {
        return $this->only(['initiate_id', 'type', 'delivery_type']);
    }
}