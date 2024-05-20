<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\service\RegionService;
use think\model\concern\SoftDelete;

/**
 * 积分订单模型
 * Class IntegralOrder
 * @package app\common\model
 */
class IntegralOrder extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';

    // 设置json类型字段
    protected $json = ['goods_snap', 'address'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * @notes 关联用户模型
     * @return \think\model\relation\HasOne
     * @author ljj
     * @date 2022/3/30 5:16 下午
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }

    /**
     * @notes 收货地址
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ljj
     * @date 2022/3/30 5:17 下午
     */
    public function getDeliveryAddressAttr($value, $data)
    {
        return RegionService::getAddress(
            [
                $data['address']['province'] ?? '',
                $data['address']['city'] ?? '',
                $data['address']['district'] ?? ''
            ],
            $data['address']['address'] ?? '',
        );
    }

    /**
     * @notes 按钮
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2022/3/30 5:26 下午
     */
    public function getAdminBtnsAttr($value, $data)
    {
        $goods_snap = $data['goods_snap'];

        $btns = [
            'cancel_btn' => $this->getAdminCancelBtn($data, $goods_snap),
            'delivery_btn' => $this->getDeliveryBtn($data, $goods_snap),
            'confirm_btn' => $this->getConfirmBtn($data, $goods_snap),
            'to_ship_btn' => $this->getToShipBtn($data),
        ];
        return $btns;
    }

    /**
     * @notes 用户端按钮
     * @param $value
     * @param $data
     * @return array
     * @author 段誉
     * @date 2022/3/31 16:39
     */
    public function getBtnsAttr($value, $data)
    {
        $goods_snap = $data['goods_snap'];
        return  [
            'pay_btn' => $this->getPayBtn($data),
            'cancel_btn' => $this->getAdminCancelBtn($data, $goods_snap),
            'delivery_btn' => $this->getDeliveryBtn($data, $goods_snap),
            'confirm_btn' => $this->getConfirmBtn($data, $goods_snap),
            'del_btn' => $this->getDelBtn($data),
        ];
    }

    /**
     * @notes 是否显示支付按钮
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/3/31 16:32
     */
    public function getPayBtn($data)
    {
        $btn = 0;
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 是否显示删除按钮
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/3/31 16:36
     */
    public function getDelBtn($data)
    {
        $btn = 0;
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_DOWN) {
            if ($data['pay_status'] == PayEnum::UNPAID || $data['refund_status'] == 1) {
                $btn = 1;
            }
        }
        return $btn;
    }

    /**
     * @notes 后台取消订单按钮
     * @param $data
     * @param $goods_snap
     * @return int
     * @author ljj
     * @date 2022/3/30 5:33 下午
     */
    public function getAdminCancelBtn($data, $goods_snap)
    {
        $btn = 0;
        // 积分订单 商品类型为红包时 不可取消
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE) {
            return $btn;
        }

        // 未支付的订单 或 已支付但未发货 可以取消
        if (is_string($data['create_time'])) {
            $data['create_time'] = strtotime($data['create_time']);
        }
        if (($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID)
            || ($data['pay_status'] == PayEnum::ISPAID && $data['order_status'] == IntegralOrderEnum::ORDER_STATUS_DELIVERY)) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 物流查询按钮
     * @param $data
     * @param $goods_snap
     * @return int
     * @author ljj
     * @date 2022/3/30 5:34 下午
     */
    public function getDeliveryBtn($data, $goods_snap)
    {
        // 红包类型 或 商品无需物流
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE || $goods_snap['delivery_way'] == 0) {
            return 0;
        }
        return $data['express_status'];
    }

    /**
     * @notes 确认收货按钮
     * @param $data
     * @param $goods_snap
     * @return int
     * @author ljj
     * @date 2022/3/30 5:35 下午
     */
    public function getConfirmBtn($data, $goods_snap)
    {
        $btn = 0;

        // 红包类型 或 订单无需物流
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE || $goods_snap['delivery_way'] == 0) {
            return $btn;
        }

        // 订单待收货 且 已发货状态
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_GOODS && $data['express_status'] == 1) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 去发货按钮
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2022/3/30 5:37 下午
     */
    public function getToShipBtn($data)
    {
        $btn = 0;
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_DELIVERY && $data['pay_status'] == PayEnum::ISPAID) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 兑换类型
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/30 5:37 下午
     */
    public function getExchangeTypeDescAttr($value, $data)
    {
        return IntegralGoodsEnum::getTypeDesc($data['exchange_type']);
    }

    /**
     * @notes 订单状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/30 5:38 下午
     */
    public function getOrderStatusDescAttr($value, $data)
    {
        return IntegralOrderEnum::getOrderStatus($data['order_status']);
    }

    /**
     * @notes 支付状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/30 5:38 下午
     */
    public function getPayStatusDescAttr($value, $data)
    {
        return PayEnum::getPayStatusDesc($data['pay_status']);
    }

    /**
     * @notes 支付方式
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/30 5:38 下午
     */
    public function getPayWayDescAttr($value, $data)
    {
        return PayEnum::getPayDesc($data['pay_way']);
    }

    /**
     * @notes 支付时间
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2022/3/31 9:44 上午
     */
    public function getPayTimeAttr($value, $data)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '-';
    }

    /**
     * @notes 发货时间
     * @param $value
     * @param $data
     * @return string
     * @author 段誉
     * @date 2022/3/31 16:26
     */
    public function getExpressTimeAttr($value, $data)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '-';
    }
}