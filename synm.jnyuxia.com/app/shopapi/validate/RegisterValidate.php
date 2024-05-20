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

use app\common\enum\YesNoEnum;
use app\common\model\User;
use app\common\service\ConfigService;
use app\common\service\sms\SmsDriver;
use app\common\validate\BaseValidate;

/**
 * 注册验证器
 * Class RegisterValidate
 * @package app\shopapi\validate
 */
class RegisterValidate extends BaseValidate
{
    protected $rule = [
        'register_source' => 'require',
        'mobile' => 'require|mobile',
        'code' => 'checkCode',
        'password' => 'require|length:6,25|alphaDash',
        'password_confirm' => 'require|confirm'
    ];

    protected $message = [
        'register_source.require' => '注册来源参数缺失',
        'mobile.require' => '请输入手机号',
        'mobile.mobile' => '无效的手机号',
        'password.require' => '请输入密码',
        'password.length' => '密码须在6-25位之间',
        'password.alphaDash' => '密码须为字母数字下划线或破折号',
        'password_confirm.require' => '请确认密码',
        'password_confirm.confirm' => '两次输入的密码不一致'
    ];

    /**
     * @notes 注册发送验证码场景
     * @return RegisterValidate
     * @author Tab
     * @date 2021/8/25 11:18
     */
    public function sceneCaptcha()
    {
        return $this->only(['mobile']);
    }

    /**
     * @notes 用户注册
     * @return RegisterValidate
     * @author Tab
     * @date 2021/8/25 11:32
     */
    public function sceneRegister()
    {
        return $this->only(['register_source', 'mobile', 'code', 'password', 'password_confirm'])
            ->append('mobile', 'checkMobile|checkConfig');
    }

    /**
     * @notes 校验手机号是否已注册
     * @param $value
     * @return bool|string
     * @author Tab
     * @date 2021/8/25 11:34
     */
    public function checkMobile($value)
    {
        $user = User::where('mobile', $value)->findOrEmpty();
        if(!$user->isEmpty()) {
            return '该手机号已被注册';
        }
        return true;
    }

    /**
     * @notes 校验验证码
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/25 11:41
     */
    public function checkCode($value, $rule, $data)
    {
        $smsDriver = new SmsDriver();
        $result = $smsDriver->verify($data['mobile'], $value);
        if($result) {
           return true;
        }
        return '验证码错误';
    }

    /**
     * @notes 检测注册配置
     * @param $value
     * @param $rue
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/25 14:33
     */
    public function checkConfig($value, $rue, $data)
    {
        $config = [
            'register_way' => ConfigService::get('config', 'register_way', [1]),
            'is_mobile_register_code' => ConfigService::get('config', 'is_mobile_register_code', YesNoEnum::YES)
        ];
        // 1-手机号注册
        if(!in_array(1, $config['register_way'])) {
            return '未开启手机号注册';
        }
        if($config['is_mobile_register_code'] && !isset($data['code'])) {
            return '请输入验证码';
        }
        return true;
    }
}