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


namespace app\shopapi\validate;


use app\common\cache\UserAccountSafeCache;
use app\common\enum\LoginEnum;
use app\common\enum\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\service\sms\SmsDriver;
use app\common\validate\BaseValidate;
use think\facade\Config;

class LoginValidate extends BaseValidate
{
    protected $rule = [
        'terminal' => 'require|in:' . UserTerminalEnum::WECHAT_MMP . ',' . UserTerminalEnum::WECHAT_OA . ','
            . UserTerminalEnum::H5 . ',' . UserTerminalEnum::PC . ','
            . UserTerminalEnum::IOS . ',' . UserTerminalEnum::ANDROID . ',' . UserTerminalEnum::TOUTIAO,
        'scene' => 'require|in:1,2|checkConfig',
        'account' => 'require',
        'mobile' => 'require|mobile'
    ];

    protected $message = [
        'terminal.require' => '终端参数缺失',
        'terminal.in' => '终端参数状态值不正确',
        'scene.require' => '场景不能为空',
        'scene.in' => '场景值错误',
        'account.require' => '请输入账号',
        'password.require' => '请输入密码',
        'mobile.require' => '请输入手机号',
        'mobile.mobile' => '无效的手机号',
    ];

    /**
     * @notes 账号密码/手机号密码/手机号验证码登录场景
     * @return LoginValidate
     * @author Tab
     * @date 2021/8/25 15:53
     */
    public function sceneAccount()
    {
        return $this->remove('mobile', 'require|mobile');
    }

    /**
     * @notes 发送登录验证码
     * @return LoginValidate
     * @author Tab
     * @date 2021/8/25 15:48
     */
    public function sceneCaptcha()
    {
        return $this->only(['mobile']);
    }

    /**
     * @notes 密码验证
     * @param $password
     * @param $other
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 令狐冲
     * @date 2021/7/2 14:00
     */
    public function checkPassword($password, $other, $data)
    {
        //后台账号安全机制，连续输错后锁定，防止账号密码暴力破解
        $userAccountSafeCache = new UserAccountSafeCache();
        if (!$userAccountSafeCache->isSafe()) {
            return '密码连续' . $userAccountSafeCache->count . '次输入错误，请' . $userAccountSafeCache->minute . '分钟后重试';
        }

        $where = [];

        if($data['scene'] == LoginEnum::MOBILE_PASSWORD || $data['scene'] == LoginEnum::MOBILE_CAPTCHA) {
            // 手机号密码登录
            $where = ['mobile' => $data['account']];
        }

        $userInfo = User::where($where)
            ->field(['password,disable'])
            ->find();

        if (empty($userInfo)) {
            return '账号或密码错误';
        }

        if ($userInfo['disable'] === YesNoEnum::YES) {
            return '您的账号被冻结，请联系客服';
        }

        if (empty($userInfo['password'])) {
            $userAccountSafeCache->record();
            return '用户不存在';
        }

        $passwordSalt = Config::get('project.unique_identification');
        if ($userInfo['password'] !== create_password($password, $passwordSalt)) {
            $userAccountSafeCache->record();
            return '账号或密码错误';
        }

        $userAccountSafeCache->relieve();

        return true;
    }

    /**
     * @notes 校验登录设置
     * @return bool|string
     * @author Tab
     * @date 2021/8/25 15:14
     */
    public function checkConfig($scene, $rule, $data)
    {
        $config = ConfigService::get('config', 'login_way', []);
        if(!in_array($scene, $config)) {
            return '不支持的登录方式';
        }
        if(($scene == LoginEnum::MOBILE_PASSWORD) && !isset($data['password'])) {
            return '请输入密码';
        }
        if(($scene == LoginEnum::MOBILE_PASSWORD)) {
            return $this->checkPassword($data['password'], [], $data);
        }
        if($scene == LoginEnum::MOBILE_CAPTCHA && !isset($data['code'])) {
            return '请输入手机验证码';
        }
        if($scene == LoginEnum::MOBILE_CAPTCHA) {
            return $this->checkCode($data['code'], [], $data);
        }
        return true;
    }

    /**
     * @notes 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/25 15:43
     */
    public function checkCode($code, $rule, $data)
    {
        $smsDriver = new SmsDriver();
        $result = $smsDriver->verify($data['account'], $code);
        if($result) {
            return true;
        }
        return '验证码错误';
    }
}