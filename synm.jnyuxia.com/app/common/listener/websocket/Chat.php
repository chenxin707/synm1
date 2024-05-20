<?php

namespace app\common\listener\websocket;


use app\common\service\FileService;
use app\common\enum\{ChatEnum, ChatMsgEnum, ChatRecordEnum};
use app\common\model\{Goods, ChatRecord};
use app\common\logic\ChatLogic;
use app\common\websocket\Response;

/**
 * 对话事件
 * Class Chat
 * @package app\common\listener\websocket
 */
class Chat
{

    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    public function handle($params)
    {
        $fromFd = $params['fd'];
        $requestData = $params['data'];
        $handleClass = $params['handle'];

        // 发起人信息
        $fromData = $handleClass->getDataByFd($fromFd);
        $toFd = $handleClass->getFdByUid($requestData['to_id'], $requestData['to_type']);
        // 当前发送人信息是否存在
        if (empty($fromData['uid'])) {
            return $handleClass->pushData($fromFd, 'error', $this->response->formatSendError('聊天用户不存在'));
        }

        // 验证后台配置是否开启
        $check = $this->checkConfig();
        if (true !== $check) {
            return $handleClass->pushData($fromFd, 'error', $this->response->formatSendError($check));
        }

        $toName = '客服';
        if (ChatEnum::TYPE_KEFU == $fromData['type']) {
            $toName = '用户';
        }

        // 接收的人不存在
        if (empty($toFd)) {
            return $handleClass->pushData($fromFd, 'error', $this->response->formatSendError($toName . '不存在'));
        }

        // 接收人不在线
        $onlineFd = $handleClass->onlineFd($toFd);
        if (empty($onlineFd)) {
            return $handleClass->pushData($fromFd, 'error', $this->response->formatSendError($toName . '不在线'));
        }

        // 添加聊天记录
        $record = $this->insertRecord([
            'from_id' => $fromData['uid'],
            'from_type' => $fromData['type'],
            'to_id' => $requestData['to_id'],
            'to_type' => $requestData['to_type'],
            'msg' => $requestData['msg'],
            'msg_type' => $requestData['msg_type'],
        ]);

        $record['from_avatar'] = FileService::setFileUrl($fromData['avatar']);
        $record['from_nickname'] = $fromData['nickname'];
        $record['create_time_stamp'] = strtotime($record['create_time']);

        $record['goods'] = [];
        if ($requestData['msg_type'] == ChatMsgEnum::TYPE_GOODS) {
            $record['goods'] = json_decode($record['msg'], true);
        }
        
        // 更新聊天关系记录
        $this->bindRelation([
            'from_id' => $fromData['uid'],
            'from_type' => $fromData['type'],
            'to_id' => $requestData['to_id'],
            'to_type' => $requestData['to_type'],
            'msg' => $record['msg'],
            'msg_type' => $requestData['msg_type'],
            'terminal' => $fromData['terminal']
        ]);

        if (!empty($record)) {
            $record['update_time'] = is_string($record['update_time']) ? strtotime($record['update_time']) : $record['update_time'];
            $handleClass->pushData($fromFd, 'chat', $record);
            return $handleClass->pushData($toFd, 'chat', $record);
        }
    }


    /**
     * @notes 检查后台配置
     * @return array|bool|string
     * @author 段誉
     * @date 2021/12/20 18:29
     */
    public function checkConfig()
    {
        if (false === ChatLogic::checkConfig()) {
            return ChatLogic::getError() ?: '请联系管理员设置后台配置';
        }
        return true;
    }


    /**
     * @notes 增加聊天记录
     * @param $data
     * @return array
     * @author 段誉
     * @date 2021/12/17 14:33
     */
    public function insertRecord($data)
    {
        switch ($data['msg_type']) {
            case ChatMsgEnum::TYPE_IMG:
                $msg = $data['msg'];
                break;

            case ChatMsgEnum::TYPE_GOODS:
                $goods = ChatLogic::getChatGoodsDetail($data['msg']);
                $msg = json_encode([
                    'id' => $goods['id'] ?? 0,
                    'image' => FileService::setFileUrl($goods['image']) ?? '',
                    'min_price' => $goods['min_price'] ?? 0,
                    'name' => $goods['name'] ?? '',
                ], true);
                break;

            default:
                $msg = htmlspecialchars($data['msg']);
        }
        $result = ChatRecord::duokaiCreate([
            'from_id' => $data['from_id'],
            'from_type' => $data['from_type'],
            'to_id' => $data['to_id'],
            'to_type' => $data['to_type'],
            'msg' => $msg,
            'msg_type' => $data['msg_type'],
            'is_read' => $data['is_read'] ?? 1,
            'type' => ChatRecordEnum::TYPE_NORMAL,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        return $result->toArray();
    }


    /**
     * @notes 绑定关系
     * @param $data
     * @author 段誉
     * @date 2021/12/17 14:33
     */
    public function bindRelation($data)
    {
        if ($data['to_type'] == 'kefu') {
            $kefuId = $data['to_id'];
            $userId = $data['from_id'];
        } else {
            $kefuId = $data['from_id'];
            $userId = $data['to_id'];
        }

        $isRead = 1;
        if ($data['from_type'] == 'user') {
            $isRead = 0;
        }

        ChatLogic::bindRelation($userId, $kefuId, [
            'terminal' => $data['terminal'],
            'msg' => $data['msg'],
            'msg_type' => $data['msg_type'],
        ], $isRead);
    }


}