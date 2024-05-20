<?php

namespace expressage;

class Expressage
{
    protected $app;
    protected $key;

    protected $logisticsInfo;

    protected $error;

    public function __construct($app, $key)
    {
        $this->app = $app;
        $this->key = $key;
    }


    /**
     * @notes 格式化
     * @return false
     * @author 段誉
     * @date 2021/8/11 16:52
     */
    public function logisticsFormat()
    {
        if (empty($this->logisticsInfo)) {
            return false;
        }
        $info = $this->logisticsInfo;
        foreach ($info as $k => $v) {
            $info[$k] = array_values($v);
        }
        return $info;
    }

    /**
     * @notes 电商Sign签名生成
     * @param $data
     * @param $appkey
     * @return string
     * @author 段誉
     * @date 2021/8/11 16:52
     */
    protected function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

    /**
     * @notes 获取错误信息
     * @return mixed
     * @author 段誉
     * @date 2021/8/11 16:52
     */
    public function getError()
    {
        return $this->error;
    }
}