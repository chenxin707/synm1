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


use app\common\model\PlatformShop;
use app\common\model\User;
use app\common\model\UserSession;

class UserTokenCache extends BaseCache
{

    private $prefix = 'token_user_';

    public function __construct()
    {
        parent::__construct();
        $this->prefix = config('project.user_token.prefix');

    }

    /**
     * @notes 通过token获取缓存用户信息
     * @param $token
     * @return false|mixed
     * @author 令狐冲
     * @date 2021/6/30 16:57
     */
    public function getUserInfo($token)
    {
        //直接从缓存获取
        $userInfo = $this->get($this->prefix . $token,null,false);
        if ($userInfo) {
            return $userInfo;
        }

        //从数据获取信息被设置缓存(可能后台清除缓存）
        $userInfo = $this->setUserInfo($token);
        if ($userInfo) {
            return $userInfo;
        }

        return false;
    }

    /**
     * @notes 通过有效token设置用户信息缓存
     * @param $token
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/5 12:12
     */
    public function setUserInfo($token)
    {
        $userSession = UserSession::where([['token', '=', $token], ['expire_time', '>', time()]])->find();

        if (empty($userSession)) {
            return [];
        }
        $user = User::where('id', '=', $userSession->user_id)
            ->find();
        $shopSn = PlatformShop::where(['id'=>$user->sid])->value('sn');
        $userInfo = [
            'user_id'       => $user->id,
            'nickname'      => $user->nickname,
            'token'         => $token,
            'sn'            => $user->sn,
            'mobile'        => $user->mobile,
            'avatar'        => $user->avatar,
            'terminal'      => $userSession->terminal,
            'expire_time'   => $userSession->expire_time,
            'shop_sn'       => $shopSn,
            'sid'           => $userSession->sid,
        ];

        $this->set($this->prefix . $token, $userInfo, new \DateTime(Date('Y-m-d H:i:s', $userSession->expire_time)),false);
        return $this->getUserInfo($token);
    }

    /**
     * @notes 删除缓存
     * @param $token
     * @return bool
     * @author 令狐冲
     * @date 2021/7/3 16:57
     */
    public function deleteUserInfo($token)
    {
        return $this->delete($this->prefix . $token,false);
    }


}