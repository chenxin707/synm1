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


namespace app\common\cache;

use app\common\model\PlatformAdmin;
use app\common\model\PlatformAdminSession;

class PlatformAdminTokenCache extends BaseCache
{

    private $prefix = 'token_platform_admin_';

    /**
     * @notes 通过token获取缓存管理员信息
     * @param $token
     * @return false|mixed
     * @author 令狐冲
     */
    public function getPlatformAdminInfo($token)
    {
        //直接从缓存获取
        $platformAdminInfo = $this->get($this->prefix . $token);
        if ($platformAdminInfo) {
            return $platformAdminInfo;
        }

        //从数据获取信息被设置缓存(可能后台清除缓存）
        $platformAdminInfo = $this->setPlatformAdminInfo($token);
        if ($platformAdminInfo) {
            return $platformAdminInfo;
        }

        return false;
    }

    /**
     * @notes 通过有效token设置管理信息缓存
     * @param $token
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public function setPlatformAdminInfo($token)
    {
        $platfromAdminSession = PlatformAdminSession::withoutGlobalScope()
            ->where([
                ['token', '=', $token],
                ['expire_time', '>', time()]
            ])
            ->find();
        if (empty($platfromAdminSession)) {
            return [];
        }
        $platfromAdmin = PlatformAdmin::withoutGlobalScope()
            ->where('id', '=', $platfromAdminSession->platform_admin_id)->find();

        $platformAdminInfo = [
            'platform_admin_id' => $platfromAdmin->id,
            'root' => $platfromAdmin->root,
            'name' => $platfromAdmin->name,
            'account' => $platfromAdmin->account,
            'avatar' => $platfromAdmin->avatar,
            'token' => $token,
            'terminal' => $platfromAdminSession->terminal,
            'expire_time' => $platfromAdminSession->expire_time,
        ];
        $this->set($this->prefix . $token, $platformAdminInfo, new \DateTime(Date('Y-m-d H:i:s', $platfromAdminSession->expire_time)));
        return $this->getPlatformAdminInfo($token);
    }

    /**
     * @notes 删除缓存
     * @param $token
     * @return bool
     * @author 令狐冲
     */
    public function deletePlatformAdminInfo($token)
    {
        return $this->delete($this->prefix . $token);
    }


}