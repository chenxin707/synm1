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

namespace app\common\service\printer;

use App\Api\PrinterService;
use App\Api\PrintService;
use app\common\cache\YlyPrinterCache;
use app\common\enum\DeliveryEnum;
use app\common\enum\PrinterEnum;
use app\common\enum\YesNoEnum;
use app\common\model\Printer;
use app\common\model\PrinterTemplate;
use app\common\service\ConfigService;
use App\Config\YlyConfig;
use App\Oauth\YlyOauthClient;

/**
 * 易联云打印服务
 */
class YlyPrinterService
{
    /**
     * @notes 获取access_token
     * @param $printer
     * @param $config
     * @return mixed
     * @author Tab
     * @date 2021/11/16 19:37
     */
    public function getAccessToken($printer, $config)
    {
        $token = (new YlyPrinterCache())->getAccessToken($printer['client_id']);
        if (!empty($token)) {
            return $token;
        }
        // 没有则调用接口去获取
        $client = new YlyOauthClient($config);
        $token = $client->getToken();
        $accessToken = $token->access_token;
        // 自有应用模式access_token无失效时间，接口访问频次：10次/日
        (new YlyPrinterCache())->setAccessToken($printer['client_id'], $accessToken, 20 * 24 * 3600);
        return $accessToken;
    }

    /**
     * @notes 获取打印模板
     * @param $printer
     * @return array
     * @author Tab
     * @date 2021/11/16 19:37
     */
    public function tempalte($printer)
    {
        $withoutField = 'create_time, update_time, delete_time';
        return PrinterTemplate::withoutField($withoutField)->findOrEmpty($printer['template_id'])->toArray();
    }

    /**
     * @notes 开始打印
     * @param $order
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/11/16 19:37
     */
    public function startPrint($order, $scene = PrinterEnum::ORDER_PRIINT)
    {
        // 打印机列表
        $printerLists = Printer::where('status', YesNoEnum::YES)->select()->toArray();
        foreach ($printerLists as $printer){
            if ($scene == PrinterEnum::ORDER_PAY && !$printer['auto_print']) {
                // 订单支付但未开启打印跳过
                continue;
            }
            $template = $this->tempalte($printer);
            $this->singlePrint($printer, $order, $template);
        }
    }

    /**
     * @notes 添加打印机
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/11/16 19:37
     */
    public function addPrinter($params)
    {
        $print = $this->getPrinter($params);
        $response = $print->addPrinter($params['machine_code'],$params['private_key'],$params['name']);
        return $response;
    }

    /**
     * @notes 删除打印机
     * @param $params
     * @author Tab
     * @date 2021/11/16 17:32
     */
    public function deletePrinter($params)
    {
        $print = $this->getPrinter($params);
        $print->deletePrinter($params['machine_code']);
    }

    /**
     * @notes 获取打印机状态
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/11/16 18:04
     */
    public function getPrintStatus($params){
        $print = $this->getPrinter($params);
        $response = $print->getPrintStatus($params['machine_code']);
        return $response;
    }

    /**
     * @notes 获取打印机接口
     * @param $params
     * @return PrinterService
     * @author Tab
     * @date 2021/11/16 18:52
     */
    public function getPrinter($params)
    {
        $config = new YlyConfig($params['client_id'], $params['client_secret']);
        $accessToken = $this->getAccessToken($params, $config);
        return new PrinterService($accessToken, $config);
    }

    /**
     * @notes 获取打印接口
     * @param $params
     * @return PrintService
     * @author Tab
     * @date 2021/11/16 18:53
     */
    public function getPrint($params)
    {
        $config = new YlyConfig($params['client_id'], $params['client_secret']);
        $accessToken = $this->getAccessToken($params, $config);
        return new PrintService($accessToken, $config);
    }

    /**
     * @notes 单次打印
     * @param $printer
     * @param $order
     * @param $template
     * @author Tab
     * @date 2021/11/16 18:53
     */
    public function singlePrint($printer, $order, $template)
    {
        $shopName = ConfigService::get('shop', 'name');
        $print = $this->getPrint($printer);
        $content = "<MN>".$printer['print_number']."</MN>";
        $content .= "<center>".$template['ticket_name']."</center>" . PHP_EOL;
        $content .= str_repeat('-', 32).PHP_EOL;
        if ($template['show_shop_name'] && $shopName) {
            $content .= "<FS><center>".$shopName."</center></FS>";
            $content .= str_repeat('-', 32).PHP_EOL;
        }
        $content .= "订单编号：".$order['sn'].PHP_EOL;
        $content .= "支付方式：".$order['pay_way_desc'].PHP_EOL;
        $content .= "配送方式：".$order['delivery_type_desc'].PHP_EOL;
        $content .= str_repeat('-', 32).PHP_EOL;
        // 商品信息
        $content .= "<table><tr><td>商品信息</td><td>数量</td><td>金额</td></tr>";
        foreach ($order['orderGoods'] as $goods){
            $content .= "<tr><td>".mb_substr($goods['goods_snap']->goods_name, 0, 15)."</td>";
            $content .= "<td>".$goods['goods_num']."</td>";
            $content .= "<td>￥".$goods['goods_price']."</td></tr>";
        }
        $content .= "<tr><td>运费</td><td></td><td>￥".$order['express_price']."</td></tr>";
        $content .= '</table>';
        $content .= str_repeat('-', 32).PHP_EOL;
        // 合计信息
        $content .= "<LR>订单原价：,￥".$order['goods_price']."</LR>";
        $content .= "<LR>优惠金额：,￥".$order['discount_amount']."</LR>";
        $content .= "<LR>实付金额：,￥".$order['order_amount']."</LR>";

        // 收货信息
        if ($order['delivery_type'] == DeliveryEnum::EXPRESS) {
            if($template['consignee_info']->name && $template['consignee_info']->mobile && $template['consignee_info']->address){
                $content .= str_repeat('-', 32).PHP_EOL;
                $content .= "收货信息".PHP_EOL;
            }
            if($template['consignee_info']->name){
                $content .= "收货人：".$order['address']->contact.PHP_EOL;
            }
            if($template['consignee_info']->mobile){
                $content .= "联系方式：".$order['address']->mobile.PHP_EOL;
            }
            if($template['consignee_info']->address){
                $content .= "收货地址：".$order['address']->address.PHP_EOL;
            }
        }

        // 自提订单
        if ($order['delivery_type'] == DeliveryEnum::SELF_DELIVERY) {
            //门店信息
            if($template['selffetch_shop']->shop_name || $template['selffetch_shop']->contacts || $template['selffetch_shop']->shop_address){
                $content .= str_repeat('-', 32).PHP_EOL;
                $content .= "门店信息".PHP_EOL;
            }
            if($template['selffetch_shop']->shop_name){
                $content .= "自提门店：".$order['selffetch_shop']['name'].PHP_EOL;
            }
            if($template['selffetch_shop']->contacts){
                $content .= "联系人：".$order['selffetch_shop']['contact'].PHP_EOL;
            }
            if($template['selffetch_shop']->shop_address){
                $content .= "门店地址：".$order['selffetch_shop']['detailed_address'].PHP_EOL;
            }
            //提货人信息
            if($template['verification_info']->contacts || $template['verification_info']->mobile || $template['verification_info']->pickup_code){
                $content .= str_repeat('-', 32).PHP_EOL;
                $content .= "提货人信息".PHP_EOL;
            }
            if($template['verification_info']->contacts){
                $content .= "提货人：".$order['address']->contact.PHP_EOL;
            }
            if($template['verification_info']->mobile){
                $content .= "联系方式：".$order['address']->mobile.PHP_EOL;
            }
            if($template['verification_info']->pickup_code){
                $content .= "提货码：".$order['pickup_code'].PHP_EOL;
            }

        }

        $content .= str_repeat('-', 32).PHP_EOL;

        // 买家留言
        if($template['show_buyer_message'] && $order['user_remark']){
            $content .= '买家留言：'.$order['user_remark'].PHP_EOL;
            $content .= str_repeat('-', 32).PHP_EOL;
        }
        // 二维码
        if($template['show_qrcode'] && $template['qrcode_name']){
            $content .= "<center>".$template['qrcode_name']."</center>".PHP_EOL;
        }
        if($template['show_qrcode'] && $template['qrcode_content']){
            $content .= "<QR>".$template['qrcode_content']."</QR>".PHP_EOL;
        }
        $content .= str_repeat('-', 32).PHP_EOL;
        // 底部信息
        $content .=  "<center>".$template['bottom']."</center>".PHP_EOL;
        $print->index($printer['machine_code'], $content, $order['sn']);
    }
}