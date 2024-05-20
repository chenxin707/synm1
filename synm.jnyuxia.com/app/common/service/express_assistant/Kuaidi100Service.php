<?php
namespace app\common\service\express_assistant;

use app\common\enum\DeliveryEnum;
use app\common\enum\ExpressEnum;
use app\common\enum\ThirdPartyEnum;
use app\common\service\ConfigService;
use app\common\service\RegionService;

class Kuaidi100Service
{
    // 授权key
    private $key;
    // 授权密钥
    private $secret;
    // 设备编号
    private $siid;

    /**
     * 构造方法
     * @param $type 面单类型
     */
    public function __construct($type)
    {
        $this->init($type);
    }

    public function init($type)
    {
        $typeEnName = ThirdPartyEnum::getThirdPartyDesc($type, 'en');
        $this->key = ConfigService::get($typeEnName, 'key', '');
        $this->secret = ConfigService::get($typeEnName, 'secret', '');
        $this->siid = ConfigService::get($typeEnName, 'siid', '');
    }

    /**
     * @notes 打印电子面单
     * @param $data
     * @throws \Exception
     * @author Tab
     * @date 2021/11/22 18:18
     */
    public function print($data)
    {
        if (empty($this->key) || empty($this->secret) || empty($this->siid)) {
            throw new \Exception('请先进行面单设置');
        }
        if ($data['express']['name'] != '顺丰快递' && empty($data['template']['template_id'])) {
            throw new \Exception('请在模板中设置电子面单客户账号');
        }
        if ($data['express']['name'] != '顺丰快递' && empty($data['template']['partner_key'])) {
            throw new \Exception('请在模板中设置电子面单密钥');
        }
        $code = ExpressEnum::getkuaidi100code($data['express']['code100']);

        $param = array(
            'printType'  => 'CLOUD',
            'partnerId'  => $data['template']['partner_id'],
            'partnerKey' => $data['template']['partner_key'],
            'net'        => $data['template']['net'] ?? $data['express']['name'],
            'kuaidicom'  => $data['express']['code100'],
            'tempid'     => $data['template']['template_id'],
            'siid'       => $this->siid,
            'cargo'      => '商品',
            'code'       => $code,      //申通快递需要code
            'weight'     => $data['totalWeight'] ? $data['totalWeight'] : 1,
            'count'      => 1,
            'payType'    => DeliveryEnum::getKuaidi100Desc($data['template']['pay_type']),
            'expType'    => '标准快递',
            'remark'     => $data['remark'],
            'recMan' => array(
                'name'      => $data['order']['address']->contact,
                'mobile'    => $data['order']['address']->mobile,
                'printAddr' => RegionService::getAddress([
                    $data['order']['address']->province,
                    $data['order']['address']->city,
                    $data['order']['address']->district,
                ], $data['order']['address']->address)
            ),
            'sendMan' => array(
                'name'      => $data['sender']['name'],
                'mobile'    => $data['sender']['mobile'],
                'printAddr' => RegionService::getAddress([
                    $data['sender']['province_id'],
                    $data['sender']['city_id'],
                    $data['sender']['district_id'],
                ], $data['sender']['address'])
            )
        );
        // 当前时间戳
        list($msec, $sec) = explode(' ', microtime());
        $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        // 格式化参数
        $post_data = array();
        $post_data["param"] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $postData["method"] = "order";
        $post_data["key"] = $this->key;
        $post_data["t"] = $t;
        $sign = md5($post_data["param"] . $t . $this->key . $this->secret);
        $post_data["sign"] = strtoupper($sign);


        // 发送请求
//        $url = 'http://poll.kuaidi100.com/printapi/printtask.do?method=eOrder';
        //v2接口
        $url = 'https://api.kuaidi100.com/label/order?method=order';


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($ch), true);
        if (!$result || $result['code'] != '200') {
            throw new \Exception($result['message'] ?? '打印电子面单异常,原因未知');
        }
        return $result;
    }
}