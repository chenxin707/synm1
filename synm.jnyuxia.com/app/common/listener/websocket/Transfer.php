<?php

namespace app\common\listener\websocket;


use app\common\enum\{ChatMsgEnum, ChatRecordEnum};
use app\common\model\{Kefu, ChatRecord, ChatRelation};
use app\common\websocket\Response;

/**
 * 转接事件
 * Class Transfer
 * @package app\common\listener\websocket
 */
class Transfer
{

    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    public function handle($params)
    {
        $oldKefuFd = $params['fd'];
        $requestData = $params['data'];
        $handleClass = $params['handle'];

        // 当前客服
        $oldKefu = $handleClass->getDataByFd($oldKefuFd);
        $oldKefuId = $oldKefu['uid'] ?? 0;
        // 当前用户
        $userFd = $handleClass->getFdByUid($requestData['user_id'], 'user');
        $userId = $requestData['user_id'] ?? 0;

        // 新客服的 fd
        $newKefuFd = $handleClass->getFdByUid($requestData['kefu_id'], 'kefu');
        $newKefuId = $requestData['kefu_id'] ?? 0;

        $userConnect = $handleClass->onlineFd($userFd);
        $kefuConnect = $handleClass->onlineFd($newKefuFd);


        if (empty($userId) || empty($userFd) || empty($userConnect)) {
            return $handleClass->pushData($oldKefuFd, 'error', $this->response->formatSendError('该用户不在线'));
        }

        if (empty($newKefuId) || empty($newKefuFd) || empty($kefuConnect)) {
            return $handleClass->pushData($oldKefuFd, 'error', $this->response->formatSendError('该客服不在线'));
        }

        $relation = ChatRelation::where(['user_id' => $userId])->findOrEmpty();

        if (empty($relation) || $relation['kefu_id'] != $oldKefuId) {
            return $handleClass->pushData($oldKefuFd, 'error', $this->response->formatSendError('转接失败'));
        }

        $newKefu = Kefu::where(['id' => $newKefuId])->findOrEmpty();

        $record = [];

        if (!$newKefu->isEmpty()) {
            // 增加通知记录-主要用于告知用户
            $record = ChatRecord::duokaiCreate([
                'from_id' => $newKefu['id'],
                'from_type' => 'kefu',
                'to_id' => $userId,
                'to_type' => 'user',
                'msg' => '客服(' . $newKefu['nickname'] . ')为您服务',
                'msg_type' => ChatMsgEnum::TYPE_TEXT,
                'is_read' => 1,
                'type' => ChatRecordEnum::TYPE_NOTICE,
                'create_time' => time(),
            ])->toArray();

            $record['goods'] = [];
            $record['from_avatar'] = $newKefu['avatar'];
            $record['from_nickname'] = $newKefu['nickname'];
            $record['create_time_stamp'] = strtotime($record['create_time']);

            // 更新关系
            ChatRelation::duokaiUpdate([
                'id' => $relation['id'],
                'kefu_id' => $newKefuId,
                'msg' => '',
                'msg_type' => ChatMsgEnum::TYPE_TEXT,
                'update_time' => time()
            ],[], [], '', false);
        }

        if (!empty($record)) {
            // 用于前端显示 ‘xxx为你服务’
            $handleClass->pushData($userFd, 'chat', $record);
        }

        // 通知用户,新客服id 头像昵称
        $handleClass->pushData($userFd, 'transfer', [
            'avater' => $newKefu['avater'],
            'nickname' => $newKefu['nickname'] ?? '客服',
            'id' => $newKefu['id'] ?? 0,
        ]);

        // 通知原客服转接成功
        $handleClass->pushData($oldKefuFd, 'transfer', [
            'status' => 'send_success',
        ]);


        $relation['online'] = 1;
        $relation['msg'] = '';
        $relation['msg_type'] = ChatMsgEnum::TYPE_TEXT;
        $relation['update_time'] = '';
        // 通知新客服转接成功
        $handleClass->pushData($newKefuFd, 'transfer', [
            'status' => 'get_success',
            'user' => $relation,
        ]);
    }
}