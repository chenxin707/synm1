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

namespace app\common\command;


use app\common\enum\DeliveryEnum;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\UserTerminalEnum;
use app\common\logic\RefundLogic;
use app\common\model\Delivery;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\RechargeOrder;
use app\common\model\UserAuth;
use DateTime;
use EasyWeChat\Factory;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class UploadShippingInfo extends Command
{
    protected function configure()
    {
        $this->setName('upload_shipping_info')
            ->setDescription('上传发货信息');
    }

    protected function execute(Input $input, Output $output)
    {
        $dateTime = new DateTime();

        //待收货已完成订单
        $order_lists = Order::withoutGlobalScope()
            ->field('id,user_id,transaction_id,delivery_type,delivery_id,sid')
            ->where(['is_upload_shipping'=>0,'order_status'=>[OrderEnum::STATUS_WAIT_RECEIVE,OrderEnum::STATUS_FINISH],'order_terminal'=>UserTerminalEnum::WECHAT_MMP,'pay_way'=>PayEnum::WECHAT_PAY])
            ->order(['id'=>'desc'])
            ->select()
            ->toArray();
        $recharge_lists = RechargeOrder::withoutGlobalScope()
            ->field('id,user_id,transaction_id,sid')
            ->where(['is_upload_shipping'=>0,'terminal'=>UserTerminalEnum::WECHAT_MMP,'pay_status'=>PayEnum::ISPAID,'pay_way'=>PayEnum::WECHAT_PAY])
            ->order(['id'=>'desc'])
            ->select()
            ->toArray();
        //门店自提待发货订单
        $selffetch_lists = Order::withoutGlobalScope()
            ->field('id,user_id,transaction_id,delivery_type,delivery_id,sid')
            ->where(['is_upload_shipping'=>0,'delivery_type'=>DeliveryEnum::SELF_DELIVERY,'order_status'=>OrderEnum::STATUS_WAIT_DELIVERY,'order_terminal'=>UserTerminalEnum::WECHAT_MMP,'pay_way'=>PayEnum::WECHAT_PAY])
            ->order(['id'=>'desc'])
            ->select()
            ->toArray();
        $lists = array_merge_recursive($order_lists,$recharge_lists,$selffetch_lists);

        $user_ids = array_unique(array_column($lists,'user_id'));
        $delivery_ids = array_unique(array_column($lists,'delivery_id'));
        $openid_arr = UserAuth::withoutGlobalScope()->where(['user_id'=>$user_ids,'terminal'=>UserTerminalEnum::WECHAT_MMP])->group('user_id')->column('openid','user_id');
        $delivery_arr = Delivery::withoutGlobalScope()->where(['id'=>$delivery_ids])->field('id,express_id,invoice_no,send_type,mobile')->append(['express_code'])->select()->toArray();
        $delivery_arr = array_column($delivery_arr,null,'id');

        if (!empty($lists)) {
            $order_update_data = [];
            $recharge_update_data = [];
            $item_desc = '充值';
            $logistics_type = 3;
            $tracking_no = '';
            $express_company = '';
            $receiver_contact = '';
            foreach ($lists as $key=>$val) {
                $openid = $openid_arr[$val['user_id']] ?? null;
                if (!$openid) {
                    continue;
                }

                if (isset($val['delivery_type'])) {
                    $order_goods = OrderGoods::withoutGlobalScope()->field('id,goods_name,goods_snap')->where(['order_id'=>$val['id']])->select()->toArray();
                    $item_desc = implode('、',array_column($order_goods,'goods_name'));
                    $delivery = $delivery_arr[$val['delivery_id']] ?? [];
                    if ($val['delivery_type'] == DeliveryEnum::EXPRESS_DELIVERY && !empty($delivery)) {
                        if ($delivery['send_type'] == DeliveryEnum::NO_EXPRESS) {
                            $logistics_type = 3;
                        } else {
                            $logistics_type = 1;
                            $tracking_no = $delivery['invoice_no'];
                            $express_company = $delivery['express_code'];
                            $receiver_contact = $delivery['mobile'];
                        }
                    }
                    if ($val['delivery_type'] == DeliveryEnum::SELF_DELIVERY) {
                        $logistics_type = 4;
                    }
                    if ($val['delivery_type'] == DeliveryEnum::SAME_CITY) {
                        $logistics_type = 2;
                    }
                    if ($val['delivery_type'] == DeliveryEnum::DELIVERY_VIRTUAL) {
                        $logistics_type = 3;
                    }
                }

                $formattedDateTime = $dateTime->setTimestamp(time())->format('Y-m-d\TH:i:s.vP');
                $data = [
                    'order_key' => [//订单，需要上传物流信息的订单
                        'order_number_type' => 2,//订单单号类型：1-使用下单商户号和商户侧单号；2-使用微信支付单号。
                        'transaction_id' => $val['transaction_id'],//原支付交易对应的微信订单号
                    ],
                    'logistics_type' => $logistics_type,//物流模式：1、实体物流配送采用快递公司进行实体物流配送形式 2、同城配送 3、虚拟商品，虚拟商品，例如话费充值，点卡等，无实体配送形式 4、用户自提
                    'delivery_mode' => 1,//发货模式枚举值：1、UNIFIED_DELIVERY（统一发货）2、SPLIT_DELIVERY（分拆发货）
                    'shipping_list' => [//物流信息列表
                        [
                            'tracking_no' => $tracking_no,//物流单号，物流快递发货时必填
                            'express_company' => $express_company,//物流公司编码，物流快递发货时必填
                            'item_desc' => mb_substr($item_desc,0,120),//商品信息,必填 限120个字以内
                            'contact' => [//联系方式，当发货的物流公司为顺丰时，联系方式为必填
                                'receiver_contact' => $receiver_contact,//收件人联系方式
                            ],
                        ]
                    ],
                    'upload_time' => $formattedDateTime,//上传时间，用于标识请求的先后顺序
                    'payer' => [//支付者信息
                        'openid' => $openid,//用户标识
                    ],
                ];

                $config = RefundLogic::getWechatConfigByTerminalCommand(UserTerminalEnum::WECHAT_MMP, $val['sid']);
                $app = Factory::miniProgram($config);
                $access_token = $app->access_token->getToken();
                $url = 'https://api.weixin.qq.com/wxa/sec/order/upload_shipping_info?access_token='.$access_token['access_token'];
                $response = \Requests::post($url,[],json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $result = json_decode($response->body, true);

                if (isset($val['delivery_type'])) {
                    $order_update_data[$key]['id'] = $val['id'];
                    $order_update_data[$key]['upload_shipping_result'] = $response->body;
                    if (isset($result['errcode']) && $result['errcode'] === 0) {
                        $order_update_data[$key]['is_upload_shipping'] = 1;
                    }
                } else {
                    $recharge_update_data[$key]['id'] = $val['id'];
                    $recharge_update_data[$key]['upload_shipping_result'] = $response->body;
                    if (isset($result['errcode']) && $result['errcode'] === 0) {
                        $recharge_update_data[$key]['is_upload_shipping'] = 1;
                    }
                }

                if (isset($result['errcode']) && $result['errcode'] === 40001) {
                    //重置token
                    $app->access_token->getRefreshedToken();
                    break;
                }
            }

            //更新订单信息
            (new Order())->duokaiSaveAll($order_update_data,true,false,false);
            (new RechargeOrder())->duokaiSaveAll($recharge_update_data,true,false,false);
        }
    }
}