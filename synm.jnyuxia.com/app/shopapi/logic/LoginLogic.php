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

namespace app\shopapi\logic;
use app\shopapi\service\{
    UserTokenService,
    WechatUserService
};
use app\common\{enum\LoginEnum,
    model\User,
    logic\BaseLogic,
    model\UserAuth,
    service\ConfigService,
    service\FileService,
    enum\UserTerminalEnum,
    service\WeChatService};
use think\facade\{
    Db,
    Config,
};
use Requests;
use EasyWeChat\Kernel\Exceptions\Exception;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;


class LoginLogic  extends BaseLogic
{
    /**
     * @notes 用户账号登录
     * @param $params
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/6/30 17:00
     */
    public function login($params)
    {
        try {
            if($params['scene'] == LoginEnum::MOBILE_PASSWORD || $params['scene'] == LoginEnum::MOBILE_CAPTCHA) {
                // 手机号密码、手机验证码登录
                $where =  ['mobile' => $params['account']];
            } else {
                // 账号密码登录
                $where =  ['account' => $params['account']];
            }
            $user = User::where($where)->findOrEmpty();
            if ($user->isEmpty()) {
                throw new \Exception('用户不存在');
            }

            //更新登录信息
            $user->login_time = time();
            $user->login_ip = request()->ip();
            $user->duokaiSave();

            //设置token
            $userInfo = UserTokenService::setToken($user->id, $params['terminal']);

            //返回登录信息
            $avatar = $user->avatar ? $user->avatar : Config::get('project.default_image.user_avatar');
            $avatar = FileService::getFileUrl($avatar);
            return [
                'nickname' => $userInfo['nickname'],
                'sn' => $userInfo['sn'],
                'mobile' => $userInfo['mobile'],
                'avatar' => $avatar,
                'token' => $userInfo['token'],
            ];
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 退出登录
     * @param $userInfo
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/5 14:34
     */
    public function logout($userInfo)
    {

        //token不存在，不注销
        if (!isset($userInfo['token'])) {
            return false;
        }

        //设置token过期
        return UserTokenService::expireToken($userInfo['token']);

    }



    /**
     * @notes 获取微信请求code的链接
     * @param $url
     * @return string
     * @author cjhao
     * @date 2021/7/31 14:28
     */
    public function codeUrl(string $url)
    {
        return WeChatService::getCodeUrl($url);
    }

    /**
     * @notes 公众号登录
     * @param $params
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author cjhao
     * @date 2021/8/2 17:54
     */
    public function oaLogin(array $params){
        //微信调用
        try {
            Db::startTrans();
            //通过code获取微信 openid
            $response = WeChatService::getOaResByCode($params);
            $userServer = new WechatUserService($response,UserTerminalEnum::WECHAT_OA);
            $userInfo = $userServer->getResopnseByUserInfo()->authUserLogin()->getUserInfo();

            // 更新登录信息
            $this->updateLoginInfo($userInfo['id']);

            Db::commit();
            return $userInfo;

        } catch (Exception |AuthorizeFailedException|\think\Exception $e) {

            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }

    }


    /**
     * @notes 小程序-静默登录
     * @param $post
     * @return array|bool
     * @author cjhao
     * @date 2021/8/2 17:00
     */
    public function silentLogin(array $params)
    {
        try {
            //通过code获取微信 openid
            $response = WeChatService::getMnpResByCode($params);
            $userServer = new WechatUserService($response,UserTerminalEnum::WECHAT_MMP);
            $userInfo = $userServer->getResopnseByUserInfo('silent')->getUserInfo();
            if (!empty($userInfo)) {
                // 更新登录信息
                $this->updateLoginInfo($userInfo['id']);
            }

            return $userInfo;

        } catch (Exception | \think\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }

    }


    /**
     * @notes 小程序-授权登录
     * @param $params
     * @return array|bool
     * @author cjhao
     * @date 2021/7/30 19:00
     */
    public function authLogin(array $params)
    {
        try {
            Db::startTrans();

            //通过code获取微信 openid
            $response = WeChatService::getMnpResByCode($params);
            $response['headimgurl'] = $params['headimgurl'];
            $response['nickname']   = $params['nickname'];
            $userServer = new WechatUserService($response,UserTerminalEnum::WECHAT_MMP);
            $userInfo = $userServer->getResopnseByUserInfo()->authUserLogin()->getUserInfo();
            // 更新登录信息
            $this->updateLoginInfo($userInfo['id']);

            Db::commit();
            return $userInfo;

        } catch (Exception | \think\Exception $e) {

            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }

    }

    /**
     * @notes uniApp微信登录
     * @param array $params
     * @return array|bool
     * @author cjhao
     * @date 2021/9/2 14:17
     */
    public function uninAppLogin(array $params)
    {

        try {
            Db::startTrans();

            //sdk不支持app登录，直接调用微信接口
            $requests = Requests::get('https://api.weixin.qq.com/sns/userinfo?openid=' . 'openid=' . $params['openid'] . '&access_token=' . $params['access_token']);
            $response = json_decode($requests->body, true);
            $userServer = new WechatUserService($response,$params['terminal']);
            $userInfo = $userServer->getResopnseByUserInfo()->authUserLogin()->getUserInfo();
            // 更新登录信息
            $this->updateLoginInfo($userInfo['id']);

            Db::commit();
            return $userInfo;

        } catch (Exception | \think\Exception $e) {

            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 字节小程序-静默登录
     * @author Tab
     * @date 2021/11/10 16:58
     */
    public function toutiaoSilentLogin($params)
    {
        Db::startTrans();
        try {
            $userInfo =  (new TouTiaoLogic())->silentLogin($params);
            if (!empty($userInfo)) {
                // 更新登录信息
                $this->updateLoginInfo($userInfo['id']);
            }
            Db::commit();
            return $userInfo;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 字节小程序-授权登录
     * @author Tab
     * @date 2021/11/10 16:58
     */
    public function toutiaoAuthLogin($params)
    {
        Db::startTrans();
        try {
            $userInfo =  (new TouTiaoLogic())->authLogin($params);
            // 更新登录信息
            $this->updateLoginInfo($userInfo['id']);
            Db::commit();
            return $userInfo;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 更新登录信息
     * 如果用户不存在，创建用户；用户存在，更新用户信息，并检查该端信息是否需要写入
     * @param $userId
     * @throws \Exception
     * @author Tab
     * @date 2021/12/7 14:12
     */
    public function updateLoginInfo($userId)
    {
        $user = User::findOrEmpty($userId);
        if ($user->isEmpty()) {
            throw new \Exception('用户不存在');
        }
        $time = time();
        $user->login_time = $time;
        $user->login_ip = request()->ip();
        $user->update_time = $time;
        $user->duokaiSave();
    }

    /**
     * @notes 小程序端绑定微信
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2022/5/17 15:47
     */
    public function mnpAuthLogin(array $params)
    {

        try {
            //通过code获取微信openid
            $response = WeChatService::getMnpResByCode($params);
            $response['user_id'] = $params['user_id'];
            $response['terminal'] = UserTerminalEnum::WECHAT_MMP;

            return $this->createAuth($response);

        } catch (\Exception  $e) {

            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 公众号端绑定微信
     * @param array $params
     * @return bool
     * @throws AuthorizeFailedException
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2022/5/17 16:20
     */
    public function oaAuthLogin(array $params)
    {
        try {
            //通过code获取微信openid
            $response = WeChatService::getOaResByCode($params);
            $response['user_id'] = $params['user_id'];
            $response['terminal'] = UserTerminalEnum::WECHAT_OA;

            return $this->createAuth($response);

        } catch (\Exception  $e) {

            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 生成一条授权记录
     * @param $response
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2022/5/17 16:18
     */
    public function createAuth($response)
    {

        //先检查openid是否有记录
        $isAuth = UserAuth::where('openid', '=', $response['openid'])->findOrEmpty();
        if(!$isAuth->isEmpty()){
            throw new \Exception('该微信已经绑定，请切换微信授权登录');
        }
        if(isset($response['unionid']) && !empty($response['unionid'])) {
            //在用unionid找记录，防止生成两个账号，同个unionid的问题
            $userAuth = UserAuth::where(['unionid'=>$response['unionid']])
                ->findOrEmpty();
            if (!$userAuth->isEmpty() && $userAuth->user_id != $response['user_id']) {
                throw new \Exception('该微信已经绑定，请切换微信授权登录');
            }
        }

        //如果没有授权，直接生成一条微信授权记录
        (new UserAuth)->duokaiSave([
            'user_id'       => $response['user_id'],
            'openid'        => $response['openid'],
            'unionid'       => $response['unionid'] ?? '',
            'terminal'      => $response['terminal'],
        ]);

        return true;
    }

    /**
     * @notes 更新用户头像昵称
     * @param $post
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DbException
     * @author ljj
     * @date 2023/2/2 6:36 下午
     */
    public static function updateUser($post,$user_id)
    {
        Db::name('user')->where(['id'=>$user_id])->update(['nickname'=>$post['nickname'],'avatar'=>FileService::setFileUrl($post['avatar']),'is_new_user'=>0]);
        return true;
    }
}