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


use app\common\model\Goods;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\validate\BaseValidate;

class GoodsCommentValidate extends BaseValidate
{
    protected $rule = [
        'order_goods_id' => 'require|checkOrderGoodsId',
        'goods_comment' => 'require|number|in:1,2,3,4,5',
        'service_comment' => 'require|number|in:1,2,3,4,5',
        'description_comment' => 'require|number|in:1,2,3,4,5',
        'express_comment' => 'require|number|in:1,2,3,4,5',
        'image' => 'array',
        'goods_id' => 'require|checkGoodsId',
    ];

    public function sceneAdd()
    {
        return $this->only(['order_goods_id','goods_comment','service_comment','description_comment','express_comment']);
    }

    public function sceneCommentCategory()
    {
        return $this->only(['goods_id']);
    }

    public function sceneCommentGoodsInfo()
    {
        return $this->only(['order_goods_id']);
    }

    /**
     * @notes 检查订单商品是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/6 6:11 下午
     */
    public function checkOrderGoodsId($value,$rule,$data)
    {
        $order_goods = OrderGoods::where('id', $value)->findOrEmpty();
        if ($order_goods->isEmpty()) {
            return '订单商品不存在';
        }
        if ($order_goods['is_comment'] == 1) {
            return '该商品已评价';
        }

        $order = Order::where('id', $order_goods['order_id'])->where('order_status', '=', 3)->findOrEmpty();
        if ($order->isEmpty()) {
            return '订单不存在或未完成';
        }

        return true;
    }

    /**
     * @notes 检查商品ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/6 8:29 下午
     */
    public function checkGoodsId($value,$rule,$data)
    {
        $order_goods = Goods::where('id', $value)->findOrEmpty();
        if ($order_goods->isEmpty()) {
            return '商品不存在';
        }

        return true;
    }
}