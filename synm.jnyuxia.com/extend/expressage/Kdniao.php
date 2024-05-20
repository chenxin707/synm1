<?php

namespace expressage;


use app\common\service\ConfigService;
use Requests;

/**
 * 快递鸟
 * Class Kdniao
 * @package expressage
 */
class Kdniao extends Expressage
{
    /**
     * @notes 查询物流轨迹
     * @param $code //物流公司编号
     * @param $number //物流单号
     * @param $extra //付费模式且快递为顺丰时需要快递相关收件人或寄件人手机4位尾号
     * @return false
     * @author 段誉
     * @date 2021/8/11 16:30
     */
    public function logistics($code, $number, $extra = "")
    {
        $requestData = "{'OrderCode':'','ShipperCode':'$code','LogisticCode':'$number','CustomerName':'$extra'}";

        $datas = array(
            'EBusinessID' => $this->app,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        //快递鸟请求接口类型
        $expressConfig = unserialize(ConfigService::get('logistics_config', 'express_bird', ''));
        $requestType = $expressConfig['set_meal'] ?? 'free';
        $searchUrl = 'https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

        //为付费类型时调整请求指令
        if ($requestType == 'pay') {
            $datas['RequestType'] = '8001';
        }

        $datas['DataSign'] = self::encrypt($requestData, $this->key);

        $result = Requests::post($searchUrl, [], $datas);
        $result = json_decode($result->body, true);
        if (isset($result['Traces']) && !empty($result['Traces'])) {
            $this->logisticsInfo = $result['Traces'];
        }
        $this->error = json_encode($result, JSON_UNESCAPED_UNICODE);
        return false;
    }

}