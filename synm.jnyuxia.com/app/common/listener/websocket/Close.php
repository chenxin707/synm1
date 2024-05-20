<?php

namespace app\common\listener\websocket;


use app\common\model\ChatRelation;

/**
 * 关闭事件
 * Class Close
 * @package app\common\listener\websocket
 */
class Close
{
    public function handle($params)
    {
        $fd = $params['fd'];
        $handleClass = $params['handle'];
        // 当前fd信息
        $data = $handleClass->getDataByFd($fd);

        if (!empty($data) && 'user' == $data['type']) {
            // 获取用户对应客服信息
            $relation = ChatRelation::where(['user_id' => $data['uid']])->findOrEmpty();
            $kefuFd = $handleClass->getFdByUid($relation['kefu_id'] ?? 0, 'kefu');
            // 通知客服用户下线
            if (!empty($kefuFd)) {
                $relation['online'] = 0;
                $handleClass->pushData($kefuFd, 'user_online', $relation);
            }
        }
    }
}