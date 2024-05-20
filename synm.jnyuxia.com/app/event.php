<?php
// 事件定义文件
return [
    'bind' => [
    ],

    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],
        // 通知
        'Notice' => ['app\common\listener\NoticeListener'],

         // swoole 相关事件
        'swoole.start' => ['app\common\listener\websocket\Start'], // 开启
        'swoole.websocket.login' => ['app\common\listener\websocket\Login'], // 登录事件
        'swoole.websocket.chat' => ['app\common\listener\websocket\Chat'], // 对话事件
        'swoole.websocket.transfer' => ['app\common\listener\websocket\Transfer'], // 转接事件
        'swoole.websocket.close' => ['app\common\listener\websocket\Close'], // 关闭事件
        'swoole.websocket.user_online' => ['app\common\listener\websocket\UserOnline'], // 上线事件
        'swoole.websocket.read' => ['app\common\listener\websocket\Read'], // 已读事件
    ],

    'subscribe' => [
    ],
];
