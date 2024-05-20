<?php

namespace app\common\listener\websocket;


use app\common\enum\ChatEnum;
use app\common\model\Kefu;
use app\common\model\User;
use app\common\websocket\Response;

/**
 * 登录事件
 * Class Login
 * @package app\common\listener\websocket
 */
class Login
{

    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    public function handle(array $event)
    {
        ['type' => $type, 'token' => $token, 'terminal' => $terminal] = $event;
//        $test = [
//            'type' => $type,'token' => $token, 'terminal' => $terminal
//        ];
//        cache('socket-cache', json_encode($test, JSON_UNESCAPED_UNICODE));

        if (empty($type) || empty($token) || empty($terminal)) {
            return $this->response->error('参数缺失');
        }

        if (!in_array($type, ChatEnum::CHAT_TYPE)) {
            return $this->response->error('类型错误');
        }
        $event['handle']->getTokenBySid($type,$token);

        if (ChatEnum::TYPE_USER == $type) {
            // 查询用户信息
            $user = User::withoutGlobalScope()->aliasSid('u')->alias('u')
                ->field(['u.id','u.sid','sn', 'nickname', 'avatar', 'mobile', 'level', 'group_id', 'disable', 's.token'])
                ->join('user_session s', 'u.id = s.user_id')
                ->where(['s.token' => $token, 's.terminal' => $terminal])
                ->findOrEmpty();

        } else {
            // 查询客服信息
            $user = Kefu::withoutGlobalScope()->aliasSid('k')->alias('k')
                ->field('k.*, s.token')
                ->join('kefu_session s', 'k.id = s.kefu_id')
                ->where(['s.token' => $token, 'terminal' => $terminal])
                ->findOrEmpty();
        }
        if ($user->isEmpty() || $user['disable']) {
//            cache('socket-user', json_encode($user->toArray(), JSON_UNESCAPED_UNICODE));
            return $this->response->error('用户信息不存在或用户已被禁用');
        }

        $user['terminal'] = $terminal;

        return $this->response->success('', $user->toArray());
    }

}