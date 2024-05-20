<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\logic;


use app\common\cache\ChatCache;
use app\common\enum\{ChatGoodsEnum, ChatMsgEnum, ChatEnum};
use app\common\model\{ChatRelation, Goods, Kefu, User};
use app\common\service\ConfigService;

/**
 * 通用聊天逻辑
 * Class ChatLogic
 * @package app\common\logic
 */
class ChatLogic extends BaseLogic
{

    /**
     * @notes 获取在线客服
     * @return array|bool
     * @author 段誉
     * @date 2021/12/14 12:07
     */
    public static function getOnlineKefu()
    {
        $key = config('project.websocket_prefix') . 'kefu';
        return (new ChatCache())->getSmembersArray($key);
    }


    /**
     * @notes 在线用户
     * @return array|bool
     * @author 段誉
     * @date 2021/12/14 12:11
     */
    public static function getOnlineUser()
    {
        $key = config('project.websocket_prefix') . 'user';
        return (new ChatCache())->getSmembersArray($key);
    }


    /**
     * @notes 格式化聊天记录
     * @param $records
     * @return array
     * @author 段誉
     * @date 2022/3/14 15:05
     */
    public static function formatChatRecords($records) : array
    {
        if (empty($records)) {
            return [];
        }

        $kefu = [];
        $user = [];

        // 获取到客服和用户不同的两组id
        foreach ($records as $item) {
            if ($item['from_type'] == ChatEnum::TYPE_KEFU) {
                $kefu[] = $item['from_id'];
            } else {
                $user[] = $item['from_id'];
            }
        }

        $kefu = array_unique($kefu);
        $user = array_unique($user);

        $kefu = Kefu::where('id', 'in', $kefu)->column('nickname, avatar', 'id');
        $user = User::where('id', 'in', $user)->column('nickname, avatar', 'id');

        foreach ($records as &$item) {
            $item['from_nickname'] = '';
            $item['from_avatar'] = '';

            if ($item['from_type'] == ChatEnum::TYPE_KEFU) {
                $kefuId = $item['from_id'];
                if (isset($kefu[$kefuId])) {
                    $item['from_nickname'] = $kefu[$kefuId]['nickname'] ?? '';
                    $item['from_avatar'] = $kefu[$kefuId]['avatar'] ?? '';
                }
            }

            if ($item['from_type'] == ChatEnum::TYPE_USER) {
                $userId = $item['from_id'];
                if (isset($user[$userId])) {
                    $item['from_nickname'] = $user[$userId]['nickname'] ?? '';
                    $item['from_avatar'] = $user[$userId]['avatar'] ?? '';
                }
            }

            $item['goods'] = [];
            if ($item['msg_type'] == ChatMsgEnum::TYPE_GOODS) {
                $item['goods'] = json_decode($item['msg'], true);
            }

            $item['create_time_stamp'] = strtotime($item['create_time']);
        }
        return array_reverse($records);
    }



    /**
     * @notes 绑定关系
     * @param $userId
     * @param $kefuId
     * @param $data
     * @param int $isRead
     * @return mixed
     * @author 段誉
     * @date 2022/3/14 15:06
     */
    public static function bindRelation($userId, $kefuId, $data, $isRead = 0)
    {
        $relation = ChatRelation::where(['user_id' => $userId])->findOrEmpty();

        $user = User::where(['id' => $userId])->findOrEmpty();
        
        if ($relation->isEmpty()) {
            $relation = ChatRelation::duokaiCreate([
                'user_id' => $userId,
                'kefu_id' => $kefuId,
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'terminal' => $data['terminal'] ?? 0,
                'msg' => $data['msg'] ?? '',
                'msg_type' => $data['msg_type'] ?? ChatMsgEnum::TYPE_TEXT,
                'is_read' => 1, // 新创建关系都算已读
                'create_time' => time(),
                'update_time' => time(),
            ]);
        } else {
            ChatRelation::duokaiUpdate(
                [
                    'id' => $relation['id'],
                    'kefu_id' => $kefuId,
                    'nickname' => $user['nickname'],
                    'avatar' => $user['avatar'],
                    'terminal' => $data['terminal'] ?? 0,
                    'msg' => $data['msg'] ?? '',
                    'msg_type' => $data['msg_type'] ?? ChatMsgEnum::TYPE_TEXT,
                    'update_time' => time(),
                    'is_read' => $isRead
                ],[], [], '', false);
        }

        return $relation['id'];
    }


    /**
     * @notes 配置
     * @return array
     * @author 段誉
     * @date 2021/12/17 11:24
     * @remark code => 0时显示人工客服页,code => 1时显示在线客服页
     */
    public static function getConfig(): array
    {
        // 在线客服状态 0->关闭; 1->开启
        if (self::getConfigSetting() != 1) {
            return ['code' => 0, 'msg' => ''];
        }

        // 缓存配置
        if ('redis' != self::getCacheDrive()) {
            return ['code' => 0, 'msg' => '请参考部署文档配置在线客服'];
        }

        // 当前在线客服
        $online = self::getOnlineKefu();
        if (empty($online)) {
            return ['code' => 0, 'msg' => '当前客服不在线,有问题请联系人工客服'];
        }

        return ['code' => 1, 'msg' => ''];
    }


    /**
     * @notes 检查配置
     * @return bool
     * @author 段誉
     * @date 2021/12/20 14:11
     */
    public static function checkConfig():  bool
    {
        try {
            if (self::getConfigSetting() != 1) {
                throw new \Exception('请联系管理员开启在线客服');
            }
            if ('redis' != self::getCacheDrive()) {
                throw new \Exception('请参考部署文档配置在线客服');
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 后台客服配置
     * @return array|int|mixed|string|null
     * @author 段誉
     * @date 2021/12/20 11:51
     */
    public static function getConfigSetting()
    {
        // 后台在线客服状态 0-关闭 1-开启
        return ConfigService::get('service', 'status', 0);
    }


    /**
     * @notes 当前缓存驱动
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 11:51
     */
    public static function getCacheDrive()
    {
        return config('cache.default');
    }

    /**
     * @notes 聊天商品信息
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/3/24 14:23
     */
    public static function getChatGoodsDetail($params)
    {
        switch ($params['type']) {
            case ChatGoodsEnum::GOODS_SECKILL:
                $goods = Goods::withoutGlobalScope()->aliasSid('g')->alias('g')
                    ->join('seckill_goods s', 's.goods_id = g.id')
                    ->where(['g.id' => $params['goods_id'], 's.id' => $params['activity_id']])
                    ->field(['g.id', 'g.image', 'g.name','s.min_seckill_price' => 'min_price'])
                    ->findOrEmpty();
                break;
            case ChatGoodsEnum::GOODS_TEAM:
                $goods = Goods::withoutGlobalScope()->aliasSid('g')->alias('g')
                    ->join('team_goods t', 't.goods_id = g.id')
                    ->where(['g.id' => $params['goods_id'], 't.id' => $params['activity_id']])
                    ->field(['g.id', 'g.image', 'g.name','t.min_team_price' => 'min_price'])
                    ->findOrEmpty();
                break;
            default:
                $goods = Goods::where(['id' => $params['goods_id']])
                    ->field([
                        'id', 'image', 'min_price', 'name'
                    ])
                    ->findOrEmpty();
        }
        return $goods->toArray();
    }


    /**
     * @notes 禁用客服
     * @param $kefu_id
     * @author 段誉
     * @date 2022/4/14 17:42
     */
    public static function setChatDisable($kefu_id)
    {
        $cache = new ChatCache();
        $prefix = config('project.websocket_prefix');
        $key = $prefix .'kefu';
        $result = $cache->getSmembersArray($key);

        $fds = $cache->getSmembersArray($prefix . 'kefu_' . $kefu_id);
        if (in_array($kefu_id, $result) && $fds) {
            $cache->srem($key, $kefu_id);
            foreach ($fds as $fd) {
                $cache->srem($prefix . 'kefu_' . $kefu_id, $fd);
                $cache->del($prefix . 'fd_' . $fd);
            }
        }
    }


}