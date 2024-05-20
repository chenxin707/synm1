<?php

namespace app\common\listener\websocket;


use app\common\model\ChatRelation;

/**
 * 已读状态
 * Class Read
 * @package app\common\listener\websocket
 */
class Read
{
    public function handle($params)
    {
        $userId = $params['data']['user_id'] ?? 0;

        $relation = ChatRelation::where(['user_id' => $userId])->findOrEmpty();

        if (!$relation->isEmpty()) {
            ChatRelation::update(['is_read' => 1], ['id' => $relation['id']]);
        }
    }
}