<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\common\websocket;

use app\common\cache\DuokaiCache;
use Swoole\Server;
use think\App;
use think\Event;
use think\facade\Log;
use think\Request;
use think\swoole\websocket\Room;
use app\common\cache\ChatCache;
use app\common\enum\ChatEnum;
use app\common\enum\ChatMsgEnum;
use Swoole\Websocket\Frame;
use think\swoole\Websocket;

class Handler extends Websocket
{
    protected $server;

    protected $room;

    protected $parser;

    protected $cache;

    protected $prefix;

    public function __construct(App $app, Server $server, Room $room, Event $event, Parser $parser, ChatCache $redis)
    {
        try {

            $this->server = $server;
            $this->room = $room;
            $this->parser = $parser;
            $this->cache = $redis;
            $this->prefix = config('project.websocket_prefix');
            parent::__construct($app, $server, $room, $event);
        } catch (\Throwable $e) {
            Log::write('客服建立连接错误--'. $e->getMessage());
        }
    }


    public function onOpen($fd, Request $request)
    {

        $type = $request->get('type/s'); //登录类型,user kefu
        $token = $request->get('token/s');
        $terminal = $request->get('terminal/d');

        try {
            $user = $this->triggerEvent('login', [
                'type'      => $type,
                'terminal'  => $terminal,
                'token'     => $token,
                'handle'    => $this,
            ]);

            if ($user['code'] == 20001 || empty($user['data']['id'])) {
                throw new \Exception(empty($user['msg']) ? "未知错误" : $user['msg']);
            }
        } catch (\Throwable $e) {
            return $this->server->close($fd);
        }

        // 登录者绑定fd
        $this->bindFd($type, $user['data'], $fd);

        $this->ping($fd);

        return $this->pushData($fd, 'login', [
            'msg' => '连接成功',
            'msg_type' => ChatMsgEnum::TYPE_TEXT
        ]);
    }


    /**
     * @notes onMessage
     * @param Frame $frame
     * @return bool|mixed|void
     * @author 段誉
     * @date 2021/12/20 10:53
     */
    public function onMessage(Frame $frame)
    {
        $param = $this->parser->decode($frame->data);

        try {
            // 回应ping
            if ('ping' === $param['event']) {
                return $this->ping($frame->fd);
            }

            $param['handle'] = $this;
            $param['fd'] = $frame->fd;

            return $this->triggerEvent($param['event'], $param);

        } catch (\Throwable $e) {
//            echo $e->getMessage();
            return $this->pushData($frame->fd, 'error', [
                'msg' => $e->getMessage(),
                'msg_type' => ChatMsgEnum::TYPE_TEXT
            ]);
        }
    }


    /**
     * @notes onClose
     * @param int $fd
     * @param int $reactorId
     * @author 段誉
     * @date 2021/12/15 19:03
     */
    public function onClose($fd, $reactorId)
    {
        $this->triggerEvent('close', ['handle' => $this, 'fd' => $fd]);
        $this->removeBind($fd);
        $this->server->close($fd);
    }


    /**
     * @notes 触发事件
     * @param string $event
     * @param array $data
     * @return mixed
     * @author 段誉
     * @date 2021/12/15 19:03
     */
    public function triggerEvent(string $event, array $data)
    {
        return $this->event->until('swoole.websocket.' . $event, $data);
    }


    /**
     * @notes 登录者的id绑定fd
     * @param $type
     * @param $user
     * @param $fd
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function bindFd($type, $user, $fd)
    {
        $uid = $user['id'];

        // socket_fd_{fd} => ['uid' => {uid}, 'type' => {type}]
        // 以fd为键缓存当前fd的信息
        $fdKey = $this->prefix . 'fd_' . $fd;
        $fdData = [
            'sid'       => $user['sid'],
            'uid'       => $uid,
            'type'      => $type,
            'nickname'  => $user['nickname'],
            'avatar'    => $user['avatar'],
            'terminal'  => $user['terminal'],
            'token'     => $user['token'],
        ];
        $this->cache->set($fdKey, json_encode($fdData, true));

        // socket_user_1(user_id) => {fd} 用户user_id为1 的 fd
        // socket_kefu_2(kefu_id) => {fd} 客服kefu_id为2 的 fd
        $uidKey = $this->prefix . $type . '_' . $uid;
        $this->cache->sadd($uidKey, $fd);

        // socket_user => {fd} 在线用户的所有fd
        if ($type == 'kefu') {
            $groupKey = $this->prefix . 'kefu';
        } else {
            $groupKey = $this->prefix . 'user';
        }
        $this->cache->sadd($groupKey, $uid);
    }


    /**
     * @notes 移除绑定
     * @param $fd
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function removeBind($fd)
    {
        $data = $this->getDataByFd($fd);
        if ($data) {
            $key = $this->prefix . 'user';
            if ($data['type'] == ChatEnum::TYPE_KEFU) {
                $key = $this->prefix . 'kefu';
            }
            $this->cache->srem($key, $data['uid']); // socket_user => 11
            $this->cache->srem($key . '_' . $data['uid'], $fd); // socket_user_uid => fd
        }
        $this->cache->del($this->prefix . 'fd_' . $fd);
    }


    /**
     * @notes 通过登录id和登录类型获取对应的fd
     * @param $uid
     * @param $type
     * @return bool
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function getFdByUid($uid, $type)
    {
        $key = $this->prefix . $type . '_' . $uid;
        return $this->cache->sMembers($key);
    }


    /**
     * @notes 根据fd获取登录的id和登录类型
     * @param $fd
     * @return mixed|string
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function getDataByFd($fd)
    {
        $key = $this->prefix . 'fd_' . $fd;
        $result = $this->cache->get($key);
        if (!empty($result)) {
            $result = json_decode($result, true);
        }
        \request()->sid = $result['sid'] ?? '';
        return $result;
    }


    /**
     * @notes ping
     * @param $fd
     * @return bool
     * @author 段誉
     * @date 2021/12/20 15:19
     */
    public function ping($fd)
    {
        $data = $this->getDataByFd($fd);
        if (!empty($data)) {
            return $this->pushData($fd, 'ping', ['terminal_time' => time()]);
        }
        return true;
    }


    /**
     * @notes 推送数据
     * @param $fd
     * @param $event
     * @param $data
     * @return bool
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function pushData($fd, $event, $data)
    {
        $data = $this->parser->encode($event, $data);

        // fd非数组时转为数组
        if (!is_array($fd)) {
            $fd = [$fd];
        }

        // 向fd发送消息
        foreach ($fd as $item) {
            if ($this->server->exist($item)) {
                $this->server->push($item, $data);
            }
        }
        return true;
    }


    /**
     * @notes 在线fd
     * @param $fd
     * @return array
     * @author 段誉
     * @date 2021/12/17 18:19
     */
    public function onlineFd($fd)
    {
        $result = [];

        if (empty($fd)) {
            return $result;
        }

        if (!is_array($fd)) {
            $fd = [$fd];
        }

        foreach ($fd as $item) {
            if ($this->server->exist($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @notes 通过token获取sid
     * @param $type
     * @param $token
     * @author cjhao
     * @date 2022/3/24 11:27
     */
    public function getTokenBySid($type,$token){
        $prefix = config('project.kefu_token.prefix');
        if(ChatEnum::TYPE_USER == $type){
            $prefix = config('project.user_token.prefix');
        }
        $key = $prefix.$token;
        $duokaiCache = new DuokaiCache();
        $cacheData = $duokaiCache->get($key,null,false);
        \request()->sid = $cacheData['sid'] ?? '';
    }

}
