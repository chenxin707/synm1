<?php

namespace app\common\listener\websocket;


use app\common\cache\ChatCache;
use think\facade\Log;

/**
 * 启动事件
 * Class Start
 * @package app\common\listener\websocket
 */
class Start
{
    public function handle($params)
    {
        try{
            $prefix = config('project.websocket_prefix');
            if (empty($prefix)) {
                return true;
            }
            $redis = new ChatCache();
            $redis->del($redis->keys($prefix));
            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::write('swoole启动异常:'.$e->getMessage());
        }
    }
}