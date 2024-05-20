<?php

namespace expressage;

use Requests;

/**
 * 快递100
 * Class Kd100
 * @package expressage
 */
class Kd100 extends Expressage
{
    /**
     * @notes 查询物流轨迹
     * @param $code //物流公司编号
     * @param $number //快递单号
     * @return false
     * @author 段誉
     * @date 2021/8/11 16:27
     */
    public function logistics($code, $number, $extra = "")
    {
        $requestData = '{"com":"' . $code . '","num":"' . $number . '","from":"","phone":"' . $extra . '","to":"","resultv2":"0","show":"0","order":"desc"}';

        $datas = array(
            'customer'  => $this->app,
            'sign'      => strtoupper(md5($requestData . $this->key . $this->app)),
            'param'     => $requestData,
        );

        $params = "";
        foreach ($datas as $k => $v) {
            $params .= "$k=" . urlencode($v) . "&";
        }
        $params = substr($params, 0, -1);
        $result = Requests::get('https://poll.kuaidi100.com/poll/query.do?' . $params);
        $result = json_decode($result->body, true);

        if (isset($result['data'])) {
            $this->logisticsInfo = $result['data'];
        }
        $this->error = json_encode($result, JSON_UNESCAPED_UNICODE);
        return false;
    }
}