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
// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\shopapi\logic;

use app\common\logic\BaseLogic;
use app\common\model\User;
use app\common\model\UserAuth;
use app\common\service\ConfigService;
use app\common\service\storage\Driver as StorageDriver;
use app\shopapi\service\UserTokenService;

/**
 * 头条逻辑层(字节跳动)
 * API文档: https://microapp.bytedance.com/docs/zh-CN/mini-app/develop/server/server-api-introduction
 */
class TouTiaoLogic extends BaseLogic
{
    private $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        if (empty($this->config['appid']) || empty($this->config['secret'])) {
            throw new \Exception("请先在后台配置appid和secret");
        }
    }

    /**
     * @notes 静默登录
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 18:29
     */
    public function silentLogin($params)
    {
        // 获取access_token
        $access_token = $this->getAccessToken();
        // 获取session_key和openId
        $result = $this->code2Session($params);
        // 获取用户信息
        $userInfo = $this->getUserInfo($result);
        if(empty($userInfo)){
            return [];
        }
        // 校验账号
        $this->checkAccount($userInfo);
        // 设置缓存并得到token
        $userInfo['token'] = $this->getToken($userInfo, $params);
        // 返回用户信息
        return $userInfo;
    }

    /**
     * @notes 授权登录
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 18:29
     */
    public function authLogin($params)
    {
        // 获取access_token
        $access_token = $this->getAccessToken();
        // 获取session_key和openId
        $result = $this->code2Session($params);
        // 获取用户信息
        $userInfo = $this->getUserInfo($result);
        if(empty($userInfo)){
            $userInfo = $this->createUser($params, $result);
        }
        // 校验账号
        $this->checkAccount($userInfo);
        // 设置缓存并得到token
        $userInfo['token'] = $this->getToken($userInfo, $params);
        // 返回用户信息
        return $userInfo;
    }

    /**
     * @notes 获取配置
     * @return array
     * @author Tab
     * @date 2021/11/10 17:08
     */
    public function getConfig()
    {
        return [
            'appid' => ConfigService::get("toutiao", "appid", ''),
            'secret' => ConfigService::get("toutiao", "secret", ''),
            'access_token' => ConfigService::get("toutiao", "access_token", ''),
            'expires_in' => ConfigService::get("toutiao", "expires_in", ''),
            'expires_in_time' => ConfigService::get("toutiao", "expires_in_time", ''),
        ];
    }

    /**
     * @notes 获取access_token
     * @return mixed
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 18:04
     */
    public function getAccessToken()
    {
        // 已获取过access_token并且仍在有效期
        if (!empty($this->config['access_token']) && $this->config['expires_in_time'] > time()) {
            return $this->config['access_token'];
        }
        // 重新获取access_token
        $url = 'https://developer.toutiao.com/api/apps/v2/token';
        $data = [
            "appid" =>   $this->config['appid'],
            "secret" =>   $this->config['secret'],
            "grant_type" =>   'client_credential'
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        // 获取成功
        if ($result['err_no'] == 0) {
            // 获取成功存入数据库
            ConfigService::set('toutiao', 'access_token', $result['data']['access_token']);
            ConfigService::set('toutiao', 'expires_in', $result['data']['expires_in']);
            // 有效期比官方缩短10分钟，提前去重新获取access_token
            $expires_in_time = time() + $result['data']['expires_in'] - 600;
            ConfigService::set('toutiao', 'expires_in_time', $expires_in_time);
            return $result['data']['access_token'];
        }

        // 获取失败
        throw new \Exception("access_token获取失败：" . $result['err_tips']);
    }

    /**
     * @notes 获取session_key和openId
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 18:29
     */
    public function code2Session($params)
    {
        $url = 'https://developer.toutiao.com/api/apps/v2/jscode2session';
        $data = [
            'appid' =>   $this->config['appid'],
            'secret' =>   $this->config['secret'],
            'code' =>   $params['code']
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = $this->http_post($url, $data);
        $result = json_decode($result, true);
        // 获取成功
        if ($result['err_no'] == 0) {
            return $result['data'];
        }

        // 获取失败
        throw new \Exception("openid获取失败：" . $result['err_tips']);
    }

    /**
     * @notes post请求
     * @param $url
     * @param $data
     * @return bool|string
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 17:36
     */
    public function http_post($url,$data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length:' . strlen($data)));
        $result = curl_exec($curl);
        if(curl_errno($curl)) {
            throw new \Exception('Errno'.curl_errno($curl));
        }
        curl_close($curl);
        return $result;
    }

    /**
     * @notes 获取用户信息
     * @param $result
     * @author Tab
     * @date 2021/11/10 18:44
     */
    public function getUserInfo($result)
    {
        $user = User::hasWhere('userAuth',['openid'=>$result['openid']],'id,sn,nickname,avatar,mobile,disable')
            ->findOrEmpty();
        //用户没有该端记录，且返回了unionid，则用unionid找该用户
        if($user->isEmpty() && !empty($result['unionid'])){
            $user = User::hasWhere('userAuth',['unionid'=>$result['unionid']],'id,sn,nickname,avatar,mobile,disable')
                ->findOrEmpty();
        }
        if ($user->isEmpty()) {
            return [];
        }
        return $user->toArray();
    }

    /**
     * @notes 创建新用户
     * @author Tab
     * @date 2021/11/10 18:52
     */
    public function createUser($params, $result)
    {
        // 获取存储引擎
        $config = [
            'default' => ConfigService::get('storage', 'default', 'local'),
            'engine'  => ConfigService::get('storage')
        ];

        //设置头像
        if (empty($params['avatarUrl'])) {
            //默认头像
            $avatar = ConfigService::get('config', 'default_avatar', '');
        } else {
            if ($config['default'] == 'local') {
                $file_name = md5($result['openid'] . time()) . '.jpeg';
                $avatar = download_file($params['avatarUrl'], 'uploads/user/avatar/', $file_name);
            } else {
                $avatar = 'uploads/user/avatar/' . md5($result['openid']  . time()) . '.jpeg';
                $StorageDriver = new StorageDriver($config);
                if (!$StorageDriver->fetch($params['avatarUrl'], $avatar)) {
                    throw new Exception( '头像保存失败:'. $StorageDriver->getError());
                }
            }

        }

        // 用户编码
        $sn = create_user_sn();
        // 邀请码
        $code = generate_code();

        // 生成新用户
        $data = [
            'sn' => $sn,
            'nickname' => $params['nickName'],
            'avatar' => $avatar,
            'code' => $code,
            'disable' => 0,
            'register_source' => $params['terminal']
        ];
        $user = User::duokaiCreate($data);

        // 用户授权记录
        $data = [
            'user_id'       => $user->id,
            'openid'        => $result['openid'] ?? '',
            'unionid'       => $result['unionid'] ?? '',
            'terminal'      => $params['terminal'],
        ];

        UserAuth::duokaiCreate($data);

        // 其他业务逻辑
        \app\common\logic\UserLogic::registerAward($user->id);

        // 返回用户信息
        return $user->toArray();
    }

    /**
     * @notes 校验账号
     * @param $userInfo
     * @throws \Exception
     * @author Tab
     * @date 2021/11/11 14:17
     */
    public function checkAccount($userInfo)
    {
        if ($userInfo['disable']) {
            throw new \Exception("账号已禁用，请联系客服处理！");
        }
    }

    /**
     * @notes 获取token
     * @param $userInfo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/11/11 14:22
     */
    public function getToken($userInfo, $params)
    {
        $user = UserTokenService::setToken($userInfo['id'],$params['terminal']);
        return $user['token'];
    }
}