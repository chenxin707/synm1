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

namespace app\kefuapi\logic;


use app\common\enum\UserTerminalEnum;
use app\common\logic\{BaseLogic, ChatLogic as CommonChatLogic};
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\model\{Order, User, Kefu};

/**
 * 客服逻辑
 * Class ChatLogic
 * @package app\kefuapi\logic
 */
class ChatLogic extends BaseLogic
{
    /**
     * @notes 获取在线客服
     * @param int $kefu_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/14 12:16
     */
    public static function getOnlineKefu(int $kefu_id): array
    {
        $online = CommonChatLogic::getOnlineKefu();

        if (empty($online)) {
            return [];
        }

        $lists = Kefu::field(['id', 'nickname', 'avatar'])
            ->where([
                ['id', 'in', $online],
                ['id', '<>', $kefu_id],
            ])
            ->select()
            ->toArray();

        return $lists;
    }


    /**
     * @notes 获取用户信息
     * @param int $user_id
     * @return array|false
     * @author 段誉
     * @date 2022/3/14 12:15
     */
    public static function getUserInfo(int $user_id)
    {
        try {
            $user = User::where(['id' => $user_id])
                ->field([
                    'id', 'sn', 'nickname', 'avatar',
                    'level', 'mobile', 'total_order_amount',
                    'birthday', 'register_source', 'create_time'
                ])
                ->findOrEmpty()
                ->append(['level_name'])
                ->toArray();

            if (empty($user)) {
                throw new \Exception('用户不存在');
            }

            $user['birthday'] = empty($user['birthday']) ? '-' : $user['birthday'];
            $user['mobile'] = empty($user['mobile']) ? '-' : substr_replace($user['mobile'], '****', 3, 4);
            $user['register_source'] = UserTerminalEnum::getTermInalDesc($user['register_source']);

            return $user;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 获取客服信息
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/3/14 12:15
     */
    public static function getKefuInfo(int $id): array
    {
        $result = Kefu::where(['id' => $id])
            ->field(['id', 'nickname', 'avatar'])
            ->findOrEmpty()
            ->toArray();

        $online = CommonChatLogic::getOnlineKefu();

        $result['online'] = 0;
        if (in_array($result['id'], $online)) {
            $result['online'] = 1;
        }

        return $result;
    }


    /**
     * @notes 上传文件域名
     * @return array
     * @author 段誉
     * @date 2022/3/14 12:13
     */
    public static function getConfig(): array
    {
        return [
            'copyright' => ConfigService::get('shop', 'copyright', ''),
            'favicon' => FileService::getFileUrl(ConfigService::get('shop', 'favicon')),
            'base_domain' => FileService::getFileUrl(),
            // 客服请求域名
            'ws_domain' => env('project.ws_domain', 'ws:127.0.0.1')
        ];
    }

}
