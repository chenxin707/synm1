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

namespace app\adminapi\logic\printer;

use app\common\cache\YlyPrinterCache;
use app\common\enum\PrinterEnum;
use app\common\logic\BaseLogic;
use app\common\model\Printer;
use app\common\model\PrinterTemplate;
use app\common\service\printer\YlyPrinterService;
use think\facade\Cache;
use think\facade\Db;

class PrinterLogic extends BaseLogic
{
    /**
     * @notes 获取打印机类型
     * @return string|string[]
     * @author Tab
     * @date 2021/11/15 16:14
     */
    public static function getPrinterType()
    {
        $data = [];
        $lists =  PrinterEnum::getPrinterTypeDesc();
        foreach($lists as $k => $v) {
            $temp['label'] = $v;
            $temp['value'] = $k;
            $data[] = $temp;
        }
        return $data;
    }

    /**
     * @notes 添加打印机
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/15 17:12
     */
    public static function addPrinter($params)
    {
        Db::startTrans();
        try {
            Printer::duokaiCreate($params);
            //调用易联云添加打印机
            (new YlyPrinterService())->addPrinter($params);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return self::handleCatch($e);
        }
    }

    /**
     * @notes 打印机详情
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/11/16 11:51
     */
    public static function printerDetail($params)
    {
        $printer = Printer::withoutField('create_time,update_time,delete_time')->findOrEmpty($params['id'])->toArray();
        return $printer;
    }

    /**
     * @notes 编辑打印机
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/15 17:32
     */
    public static function editPrinter($params)
    {
        Db::startTrans();
        try {
            Printer::duokaiUpdate($params, [], [], '', false);
            // 调用易联云接口更新打印机(和添加时一样的接口，相同的机器会自动更新)
            (new YlyPrinterService())->addPrinter($params);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return self::handleCatch($e);
        }
    }

    /**
     * @notes 删除打印机
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/15 17:34
     */
    public static function deletePrinter($params)
    {
        Db::startTrans();
        try {
            $printer = Printer::find($params['id']);
            $params = $printer->toArray();
            $printer->duokaiDelete();
            // 调用易联云接口删除打印机
            (new YlyPrinterService())->deletePrinter($params);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            return self::handleCatch($e);
        }
    }

    /**
     * @notes 自动打印状态切换
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/17 10:34
     */
    public static function autoPrint($params)
    {
        try {
            $printer = Printer::find($params['id']);
            $printer->auto_print = $printer->auto_print ? 0 : 1;
            $printer->duokaiSave();

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 添加模板
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/15 18:19
     */
    public static function addTemplate($params)
    {
        try {
            $params['selffetch_shop'] = json_encode($params['selffetch_shop']);
            $params['consignee_info'] = json_encode($params['consignee_info']);
            $params['verification_info'] = json_encode($params['verification_info']);
            $params['qrcode_name'] = $params['qrcode_name'] ?? '';
            $params['qrcode_content'] =  $params['qrcode_content'] ?? '';
            PrinterTemplate::duokaiCreate($params);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 编辑模板
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/15 18:42
     */
    public static function editTemplate($params)
    {
        try {
            $params['qrcode_name'] = $params['qrcode_name'] ?? '';
            $params['qrcode_content'] =  $params['qrcode_content'] ?? '';
            PrinterTemplate::duokaiUpdate($params, [], [], '', false);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除模板
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/15 18:46
     */
    public static function deleteTemplate($params)
    {
        try {
            PrinterTemplate::duokaiDestroy($params['id']);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 模板详情
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/11/16 12:00
     */
    public static function templateDetail($params)
    {
        $template = PrinterTemplate::withoutField('create_time,update_time,delete_time')->findOrEmpty($params['id'])->toArray();
        $template['selffetch_shop']->shop_name = intval($template['selffetch_shop']->shop_name);
        $template['selffetch_shop']->contacts = intval($template['selffetch_shop']->contacts);
        $template['selffetch_shop']->shop_address = intval($template['selffetch_shop']->shop_address);
        $template['consignee_info']->name = intval($template['consignee_info']->name);
        $template['consignee_info']->mobile = intval($template['consignee_info']->mobile);
        $template['consignee_info']->address = intval($template['consignee_info']->address);

        $template['verification_info']->contacts = intval($template['verification_info']->contacts ?? 0);
        $template['verification_info']->mobile = intval($template['verification_info']->mobile ?? 0);
        $template['verification_info']->pickup_code = intval($template['verification_info']->pickup_code ?? 0);
        return $template;
    }

    /**
     * @notes 统一处理涉及易联云的错误
     * @param $e
     * @return false
     * @author Tab
     * @date 2021/11/16 17:30
     */
    public static function handleCatch($e)
    {
        $msg = json_decode($e->getMessage(),true);
        if(18 === $e->getCode()){
            //access_token过期，清除缓存中的access_token
            (new YlyPrinterCache())->deleteTag();
        };
        if($msg && isset($msg['error'])){
            self::$error =  '易联云：'.$msg['error_description'];
            return false;
        }

        self::$error = $e->getMessage();
        return false;
    }

    /**
     * @notes 测试打印机
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/16 18:08
     */
    public static function testPrinter($params)
    {
        try {
            $printer = Printer::findOrEmpty($params['id'])->toArray();

            //获取打印机状态
            $response = (new YlyPrinterService())->getPrintStatus($printer);

            if(1 == $response->body->state){
                $data = self::testData($printer['template_id']);
                (new YlyPrinterService())->singlePrint($printer, $data['order'], $data['template']);
                return true;
            }
            $msg = '打印机离线';

            if(2 == $response->body->state){
                $msg = '打印机缺纸';
            }
            throw new \Exception($msg);
        }catch (\Exception $e){
            return self::handleCatch($e);
        }
    }

    /**
     * @notes 测试数据
     * @return array
     * @author Tab
     * @date 2021/11/16 18:00
     */
    public static function testData($templateId){
        $address = json_encode([
            'contact' => '小明',
            'mobile' => 13899999999,
            'address' => '广东省广州市中心1号',
        ]);
        $goods_snap = json_encode(['goods_name' => "测试商品"]);
        $order = [
            'sn'          => date("Ymd").'88888888888',
            'delivery_type'     => 1,
            'consignee'         => '张先生',
            'mobile'            => '138888888888',
            'address'           => json_decode($address),
            'user_remark'       => '尽快发货',
            'pay_way_desc'       => '微信支付',
            'delivery_type_desc' => '快递配送',
            'orderGoods'       => [
                [
                    'goods_num'     => '11',
                    'goods_price'   => '2222',
                    'goods_snap'   => json_decode($goods_snap),
                ],
                [
                    'goods_num'     => '33',
                    'goods_price'   => '5555',
                    'goods_snap'   => json_decode($goods_snap),
                ]
            ],
            'selffetch_shop'    => [],
            'goods_price'      => '888888',  //商品总价
            'discount_amount'   => '80',      //优惠金额
            'express_price'    => '12',      //运费
            'order_amount'      => '222'      //应付金额
        ];

        $template = self::templateDetail(['id' => $templateId]);
        return [
            'order' => $order,
            'template' => $template,
        ];
    }
}