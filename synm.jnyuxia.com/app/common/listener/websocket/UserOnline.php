<?php

namespace app\common\listener\websocket;


use app\common\logic\ChatLogic;
use app\common\model\ChatRelation;

/**
 * 用户上线
 * Class UserOnline
 * @package app\common\listener\websocket
 */
class UserOnline
{
    public function handle($params)
    {
        $handleClass = $params['handle'];
        $fd = $params['fd'];

        // 当前用户信息
        $user = $handleClass->getDataByFd($fd);
        // 接收人信息
        $toId = $params['data']['kefu_id'] ?? 0;

        if (empty($user['type'] || $user['type'] != 'user' || empty($toId))) {
            return true;
        }

        // 是否有绑定关系
        $relationId = ChatLogic::bindRelation($user['uid'], $toId, [
            'terminal' => $user['terminal'],
        ], 1);

        $relation = ChatRelation::where(['id' => $relationId])->findOrEmpty();
        $toFd = $handleClass->getFdByUid($toId, 'kefu');
        if (!empty($toFd)) {
            $relation['online'] = 1;
            return $handleClass->pushData($toFd, 'user_online', $relation);
        }
        return true;
    }
}