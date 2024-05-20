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

namespace app\shopapi\controller;
use app\common\enum\NoticeEnum;
use app\common\enum\UserTerminalEnum;
use app\shopapi\{validate\LoginValidate,
    validate\OaLogicValidate,
    validate\TouTiaoValidate,
    validate\WechatLoginValidate,
    logic\LoginLogic};


class LoginController extends BaseShopController
{
    public array $notNeedLogin = ['account', 'logout','codeUrl','oaLogin','silentLogin','authLogin','config', 'captcha', 'toutiaoSilentLogin', 'toutiaoAuthLogin','uninAppLogin'];

    /**
     * @notes 账号密码/手机号密码/手机号验证码登录
     * @date 2021/6/30 17:01
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     */
    public function account()
    {
        $params = (new LoginValidate())->post()->goCheck('account');
        $result = (new LoginLogic())->login($params);
        if ($result === false) {
            return $this->fail(LoginLogic::getError());
        }
        return $this->data($result);
    }

    /**
     * @notes 发送验证码-登录
     * @author Tab
     * @date 2021/8/25 15:47
     */
    public function captcha()
    {
        $params = (new LoginValidate())->post()->goCheck('captcha');
        $code = mt_rand(1000, 9999);
        $result = event('Notice',  [
            'scene_id' => NoticeEnum::LOGIN_CAPTCHA,
            'params' => [
                'mobile' => $params['mobile'],
                'code' => $code,
            ]
        ]);
        if ($result[0] === true) {
            return $this->success('发送成功');
        }

        return $this->fail($result[0], [], 0, 1);
    }

    /**
     * @notes 退出登录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/8 00:36
     */
    public function logout()
    {
        //退出登录情况特殊，只有成功的情况，也不需要token验证
        (new LoginLogic())->logout($this->userInfo);
        return $this->success();
    }

    /**
     * @notes 获取微信请求code的链接
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/31 14:17
     */
    public function codeUrl()
    {
        $url = $this->request->get('url');
        return $this->success('获取成功', ['url' => (new LoginLogic)->codeUrl($url)], 1);
    }


    /**
     * @notes 公众号登录
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/31 14:27
     */
    public function oaLogin()
    {
        $params = (new WechatLoginValidate())->post()->goCheck('oa');
        $res = (new LoginLogic)->oaLogin($params);
        if(false === $res){
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('',$res);
    }


    /**
     * @notes 静默登录
     * @author cjhao
     * @date 2021/7/31 14:41
     */
    public function silentLogin(){

        $params = (new WechatLoginValidate())->post()->goCheck('silent');
        $res = (new LoginLogic)->silentLogin($params);
        if(false === $res){
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('',$res);

    }


    /**
     * @notes 登录接口
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/30 17:47
     */
    public function authLogin()
    {
        $params = (new WechatLoginValidate())->post()->goCheck('auth');
        $res = (new LoginLogic)->authLogin($params);
        if(false === $res){
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('',$res);
    }


    /**
     * @notes uniApp微信登录
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/2 11:52
     */
    public function uninAppLogin(){
        $params = (new WechatLoginValidate())->post()->goCheck('uninapp');
        $res = (new LoginLogic)->uninAppLogin($params);
        if(false === $res){
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('',$res);
    }

    /**
     * @notes 字节小程序-静默登录
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/10 16:57
     */
    public function toutiaoSilentLogin()
    {
        $params = (new TouTiaoValidate())->post()->goCheck("silent");
        $params['terminal'] = UserTerminalEnum::TOUTIAO;
        $result = (new LoginLogic())->toutiaoSilentLogin($params);
        if ($result === false) {
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('', $result);
    }

    /**
     * @notes 字节小程序-授权登录
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/10 16:57
     */
    public function toutiaoAuthLogin()
    {
        $params = (new TouTiaoValidate())->post()->goCheck("auth");
        $params['terminal'] = UserTerminalEnum::TOUTIAO;
        $result = (new LoginLogic())->toutiaoAuthLogin($params);
        if ($result === false) {
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('', $result);
    }

    /**
     * @notes 小程序绑定微信
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/5/17 15:56
     */
    public function mnpAuthLogin()
    {
        $params = (new WechatLoginValidate())->post()->goCheck("wechatAuth");
        $params['user_id'] = $this->userId;
        $result = (new LoginLogic())->mnpAuthLogin($params);
        if ($result === false) {
            return $this->fail(LoginLogic::getError());
        }
        return $this->success('绑定成功',[],1,1);

    }

    /**
     * @notes 公众号绑定微信
     * @return \think\response\Json
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2022/5/18 9:11
     */
    public function oaAuthLogin()
    {
        $params = (new WechatLoginValidate())->post()->goCheck("wechatAuth");
        $params['user_id'] = $this->userId;
        $result = (new LoginLogic())->oaAuthLogin($params);
        if ($result === false) {
            return $this->fail(LoginLogic::getError());
        }

        return $this->success('绑定成功',[],1,1);
    }

    /**
     * @notes 更新用户头像昵称
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     * @author ljj
     * @date 2023/2/2 6:36 下午
     */
    public function updateUser()
    {
        $params = (new WechatLoginValidate())->post()->goCheck("updateUser");
        LoginLogic::updateUser($params,$this->userId);

        return $this->success('操作成功',[],1,1);
    }
}