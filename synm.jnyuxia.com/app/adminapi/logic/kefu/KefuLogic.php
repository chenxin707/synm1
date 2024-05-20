<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------

namespace app\adminapi\logic\kefu;


use app\common\enum\KefuTerminalEnum;
use app\common\logic\BaseLogic;
use app\common\logic\ChatLogic;
use app\common\model\Admin;
use app\common\model\Kefu;
use app\kefuapi\service\KefuTokenService;

/**
 * 客服逻辑
 * Class KefuLogic
 * @package app\adminapi\logic\kefu
 */
class KefuLogic extends BaseLogic
{


    /**
     * @notes 添加客服
     * @param array $params
     * @return Kefu|\think\Model
     * @author 段誉
     * @date 2022/3/8 17:52
     */
    public static function add(array $params)
    {
        return Kefu::duokaiCreate([
            'admin_id' => $params['admin_id'],
            'nickname' => $params['nickname'],
            'disable' => $params['disable'],
            'sort' => $params['sort'] ?: 1,
            'avatar' => $params['avatar']
        ]);
    }

    /**
     * @notes 编辑客服
     * @param array $params
     * @return Kefu
     * @author 段誉
     * @date 2022/3/8 17:55
     */
    public static function edit(array $params)
    {
        return Kefu::duokaiUpdate([
            'id' => $params['id'],
            'nickname' => $params['nickname'],
            'sort' => $params['sort'] ?: 1,
            'disable' => $params['disable'],
            'avatar' => $params['avatar'],
        ],[], [], '', false);
    }


    /**
     * @notes 删除客服
     * @param int $id
     * @return bool
     * @author 段誉
     * @date 2022/3/8 17:59
     */
    public static function del(int $id): bool
    {
        return Kefu::destroy($id);
    }


    /**
     * @notes 获取客服详情
     * @param int $id
     * @return array
     * @author 段誉
     * @date 2022/3/8 18:05
     */
    public static function detail(int $id)
    {
        return Kefu::with(['admin' => function ($query) {
            $query->withField(['id', 'account', 'name']);
        }])->findOrEmpty($id)->toArray();
    }


    /**
     * @notes 设置客服状态
     * @param array $params
     * @return Kefu
     * @author 段誉
     * @date 2022/3/8 18:23
     */
    public static function setStatus(array $params)
    {
        if ($params['disable'] == 1) {
            ChatLogic::setChatDisable($params['id']);
        }
        return Kefu::duokaiUpdate(['id' => $params['id'], 'disable' => $params['disable']],[], [], '', false);
    }

    /**
     * @notes 客服token
     * @param int $id
     * @return false|string
     * @author 段誉
     * @date 2022/3/18 16:40
     */
    public static function login(int $id)
    {
        try{
            $kefu = Admin::withoutGlobalScope()->aliasSid('a')->alias('a')
                ->field(['k.id', 'k.disable' => 'kefu_disable', 'a.disable' => 'admin_disable'])
                ->join('kefu k', 'a.id = k.admin_id')
                ->where(['k.id' => $id])
                ->findOrEmpty();

            if($kefu->isEmpty()) {
                throw new \Exception('该客服信息缺失');
            }
            if ($kefu['kefu_disable'] || $kefu['admin_disable']) {
                throw new \Exception('该客服已被禁用');
            }

            $token = KefuTokenService::setToken($kefu['id'], KefuTerminalEnum::PC)['token'] ?? '';

            return request()->domain() . '/kefu?token='. $token;

        } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

}