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

namespace app\common\model;

use app\common\enum\AfterSaleEnum;
use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\RegionService;
use app\shopapi\logic\Order\OrderBtnLogic;
use think\model\concern\SoftDelete;

/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class Order extends BaseModel
{
    protected $json = ['address'];


    /**
     * @notes 关联订单商品
     * @return \think\model\relation\HasMany
     * @author 段誉
     * @date 2021/8/2 20:16
     */
    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id', 'id');
    }

    /**
     * @notes 一对多关联订单日志
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2021/8/6 2:08 下午
     */
    public function orderLog()
    {
        return $this->hasMany(OrderLog::class, 'order_id', 'id');
    }

    /**
     * @notes 关联自提门店模型
     * @return \think\model\relation\HasOne
     * @author ljj
     * @date 2021/9/22 6:55 下午
     */
    public function selffetchShop()
    {
        return $this->hasOne(SelffetchShop::class, 'id', 'selffetch_shop_id');
    }

    /**
     * @notes 用户关联模型
     * @return \think\model\relation\HasOne
     * @author cjhao
     * @date 2023/2/16 18:45
     */
    public function user()
    {
        return $this->hasOne(User::class,'id','user_id')->field('id,sn,nickname');
    }


    public function delivery()
    {
        return $this->hasOne(Delivery::class,'id','delivery_id')->field('id,express_id,invoice_no,send_type')
            ->append(['express_code']);
    }

    /**
     * @notes 通过订单id获取用户订单
     * @param $id
     * @param $userId
     * @return Order|array|\think\Model
     * @author 段誉
     * @date 2021/7/26 17:48
     */
    public function getUserOrderById($id, $userId)
    {
        return $this->with(['order_goods'])->where(['id' => $id, 'user_id' => $userId])->findOrEmpty();
    }

    /**
     * @notes 通过订单id获取订单
     * @param $id
     * @return Order|array|\think\Model
     * @author 段誉
     * @date 2021/7/26 17:48
     */
    public function getOrderById($id)
    {
        return $this->with(['order_goods'])->where(['id' => $id])->findOrEmpty();
    }


    /**
     * @notes 筛选门店
     * @param $query
     * @param $value
     * @param $data
     * @author cjhao
     * @date 2022/4/29 17:40
     */
    public function searchShopIdAttr($query,$value,$data){
        if($value){
            $query->where('selffetch_shop_id', '=', $value);

        }
    }

    /**
     * @notes 搜索器-用户id
     * @param $query
     * @param $value
     * @param $data
     * @author 段誉
     * @date 2021/7/23 18:56
     */
    public function searchUserIdAttr($query, $value, $data)
    {
        $query->where('user_id', '=', $value);
    }


    /**
     * @notes 搜索器-订单类型
     * @param $query
     * @param $value
     * @param $data
     * @author 段誉
     * @date 2021/7/23 18:56
     */
    public function searchOrderTypeAttr($query, $value, $data)
    {
        switch ($value ?? '') {
            //待支付
            case 'pay':
                $query->where('order_status', '=', OrderEnum::STATUS_WAIT_PAY);
                break;
            //待发货
            case 'delivery':
                $query->where('order_status', '=', OrderEnum::STATUS_WAIT_DELIVERY);
                break;
            //待收货
            case 'take':
                $query->where('order_status','=',OrderEnum::STATUS_WAIT_RECEIVE);
                break;
            //已完成
            case 'finish':
                $query->where('order_status', '=', OrderEnum::STATUS_FINISH);
                break;
            //已关闭
            case 'close':
                $query->where('order_status', '=', OrderEnum::STATUS_CLOSE);
                break;
        }
    }


    /**
     * @notes 获取器-操作按钮
     * @param $value
     * @param $data
     * @return array
     * @author 段誉
     * @date 2021/8/2 20:13
     */
    public function getBtnAttr($value, $data)
    {
        return OrderBtnLogic::getOrderBtn($this);
    }

    /**
     * @notes 收货地址
     * @param $value
     * @param $data
     * @return mixed|string
     * @author 段誉
     * @date 2021/8/2 20:34
     */
    public function getDeliveryAddressAttr($value, $data)
    {
        return RegionService::getAddress(
            [
                $data['address']->province ?? '',
                $data['address']->city ?? '',
                $data['address']->district ?? ''
            ],
            $data['address']->address ?? '',
        );
    }

    /**
     * @notes 取消未支付订单时间
     * @param $value
     * @param $data
     * @return float|int|string
     * @author 段誉
     * @date 2021/8/2 20:34
     */
    public function getCancelUnpaidOrdersTimeAttr($value, $data)
    {
        $end_time = '';
        if ($data['order_status'] == 0 && $data['pay_status'] == 0) {
            //系统取消待付款订单
            $systemCancel = ConfigService::get('transaction', 'cancel_unpaid_orders');
            if ($systemCancel == YesNoEnum::YES) {
                $systemCancelTime = ConfigService::get('transaction', 'cancel_unpaid_orders_times');
                $end_time = $data['create_time'] + ($systemCancelTime * 60);
            }
        }
        return $end_time;
    }

    /**
     * @notes 订单类型获取器
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2021/8/4 2:29 下午
     */
    public function getOrderTypeDescAttr($value,$data)
    {
        return OrderEnum::getOrderTypeDesc($data['order_type']);
    }

    /**
     * @notes 订单状态获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/4 2:34 下午
     */
    public function getOrderStatusDescAttr($value,$data)
    {
        return OrderEnum::getOrderStatusDesc($data['order_status']);
    }

    /**
     * @notes 支付状态获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/4 2:39 下午
     */
    public function getPayStatusDescAttr($value,$data)
    {
        return PayEnum::getPayStatusDesc($data['pay_status']);
    }

    /**
     * @notes 订单来源获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/6 3:42 下午
     */
    public function getOrderTerminalDescAttr($value,$data)
    {
        return (new UserTerminalEnum)->getTermInalDesc($data['order_terminal']);
    }

    /**
     * @notes 支付方式获取器
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2021/8/6 3:45 下午
     */
    public function getPayWayDescAttr($value,$data)
    {
        return PayEnum::getPayDesc($data['pay_way']);
    }

    /**
     * @notes 用户头像获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/8/4 2:53 下午
     */
    public function getAvatarAttr($value,$data)
    {
        return empty($value) ? '' : FileService::getFileUrl($value);
    }

    /**
     * @notes 后台订单操作按钮获取器
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2021/8/4 8:17 下午
     */
    public function getAdminOrderBtnAttr($value, $data)
    {
        return \app\adminapi\logic\order\OrderBtnLogic::getOrderBtn($this);
    }

    /**
     * @notes 支付时间获取器
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/6 3:49 下午
     */
    public function getPayTimeAttr($value,$data)
    {
        if ($value) {
            return date('Y-m-d H:i:s',$value);
        }
        return '——';
    }

    /**
     * @notes 完成时间获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/8/6 3:56 下午
     */
    public function getConfirmTakeTimeAttr($value,$data)
    {
        if ($value) {
            return date('Y-m-d H:i:s',$value);
        }
        return '——';
    }

    /**
     * @notes 配送状态获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/6 4:11 下午
     */
    public function getExpressStatusDescAttr($value, $data)
    {
        return DeliveryEnum::getDeliveryStatusDesc($data['express_status']);
    }

    /**
     * @notes 配送方式获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/6 4:12 下午
     */
    public function getDeliveryTypeDescAttr($value, $data)
    {
        return DeliveryEnum::getDeliveryTypeDesc($data['delivery_type']);
    }

    /**
     * @notes 配送时间获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/8/6 4:56 下午
     */
    public function getExpressTimeAttr($value,$data)
    {
        if ($value) {
            return date('Y-m-d H:i:s',$value);
        }
        return '——';
    }

    public function getVerificationStatusDescAttr($value, $data)
    {
        return OrderEnum::getVerificationStatusDesc($data['verification_status']);
    }


    /**
     * @notes 订单编号搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:33 下午
     */
    public function searchOrderSnAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.sn','like', '%'.$value.'%');
        }
    }

    /**
     * @notes 用户信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:34 下午
     */
    public function searchUserInfoAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('u.nickname|u.sn|u.mobile','like', '%'.$value.'%');
        }
    }

    /**
     * @notes 商品名称搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:35 下午
     */
    public function searchGoodsNameAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('og.goods_name','like', '%'.$value.'%');
        }
    }

    /**
     * @notes 收货人信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:38 下午
     */
    public function searchContactInfoAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.address->contact|o.address->mobile','like', '%'.$value.'%');
        }
    }

    /**
     * @notes 订单来源搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:40 下午
     */
    public function searchOrderTerminalAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.order_terminal','=', $value);
        }
    }

    /**
     * @notes 后台订单类型搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:41 下午
     */
    public function searchOrderTypeAdminAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.order_type','=', $value);
        }
    }

    /**
     * @notes 支付方式搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:42 下午
     */
    public function searchPayWayAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.pay_way','=', $value);
        }
    }

    /**
     * @notes 支付状态搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:43 下午
     */
    public function searchPayStatusAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.pay_status','=', $value);
        }
    }

    /**
     * @notes 配送方式搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:43 下午
     */
    public function searchDeliveryTypeAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.delivery_type','=', $value);
        }
    }

    /**
     * @notes 时间段搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:46 下午
     */
    public function searchTimeTypeAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->whereBetweenTime('o.'.$value, $data['start_time'], $data['end_time']);
        }
    }

    /**
     * @notes 订单状态搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/5 4:48 下午
     */
    public function searchOrderStatusAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.order_status','=', $value);
        }
    }

    /**
     * @notes 提货吗搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/26 4:05 下午
     */
    public function searchPickupCodeAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.pickup_code','like', '%'.$value.'%');
        }
    }

    /**
     * @notes 核销状态搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/8/26 4:06 下午
     */
    public function searchVerificationStatusAttr($query, $value, $data)
    {
        if(isset($value) && $value != ''){
            $query->where('o.verification_status','=', $value);
        }
    }

    /**
     * @notes 判断是否显示核销码
     * @param $value
     * @param $data
     * @author cjhao
     * @date 2022/4/1 11:42
     */
    public function getShowPickupCodeAttr($value,$data){
        $show = true;
        if( OrderEnum::TEAM_ORDER == $data['order_type'] && 1 != $data['is_team_success']){
            $show = false;
        }
        return $show;

    }

    /**
     * @notes 获取器-操作按钮
     * @param $value
     * @param $data
     * @return array
     * @author 段誉
     * @date 2021/8/2 20:13
     */
    public function getBusinesseBtnAttr($value, $data)
    {
        return \app\adminapi\logic\order\OrderBtnLogic::getBusinesseBtn($this);
    }



    /**
     * @notes 获取收货人
     * @param $value
     * @param $data
     * @return string
     * @author cjhao
     * @date 2023/2/23 14:30
     */
    public function getConsigneeAttr($value, $data)
    {
        return $data['address']->contact ?? '';

    }

    /**
     * @notes 获取收货人电话
     * @param $value
     * @param $data
     * @return string
     * @author cjhao
     * @date 2023/2/23 14:30
     */
    public function getMobileAttr($value,$data)
    {
        return $data['address']->mobile ?? '';
    }

}