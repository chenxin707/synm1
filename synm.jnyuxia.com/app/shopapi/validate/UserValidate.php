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

use app\common\model\User;
use app\common\validate\BaseValidate;

/**
 * 用户验证器
 * Class UserValidate
 * @package app\shopapi\validate
 */
class UserValidate extends BaseValidate
{
    protected $rule = [
        'pay_password' => 'require|checkPayPassword',
        'transfer_in' => 'require|checkTransferIn',
        'money' => 'require|gt:0|checkMoney',
        'code' => 'require',
        'encrypted_data' => 'require',
        'iv' => 'require',
        'mobile' => 'require|mobile',
        'password' => 'require|length:6,20|alphaDash',
        'old_password' => 'require',
    ];

    protected $message = [
        'pay_password.require' => '请输入支付密码',
        'pay_password.length' => '支付密码须在4-8位之间',
        'pay_password.alphaDash' => '支付密码须为字母数字下划线或破折号',
        'transfer_in.require' => '请选择收款用户',
        'money.require' => '请输入转账金额',
        'money.gt' => '转账金额须大于0',
        'code.require' => '参数缺失',
        'encrypted_data.require' => '参数缺失',
        'iv.require' => '参数缺失',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '无效的手机号码',
        'old_password.require' => '请输入原密码',
        'password.require' => '请输入登录密码',
        'password.length' => '登录密码须在6-20位之间',
        'password.alphaDash' => '登录密码须为字母数字下划线或破折号',
    ];

    /**
     * @notes 余额转账场景
     * @return UserValidate
     * @author Tab
     * @date 2021/8/11 20:27
     */
    public function sceneTransfer()
    {
        return $this->only(['pay_password', 'transfer_in', 'money']);
    }

    /**
     * @notes 收款用户信息场景
     * @return UserValidate
     * @author Tab
     * @date 2021/8/12 10:53
     */
    public function sceneTransferIn()
    {
        return $this->only(['transfer_in'])
            ->remove('transfer_in', 'checkTransferIn');
    }

    /**
     * @notes 设置/修改交易密码场景
     * @return UserValidate
     * @author Tab
     * @date 2021/8/12 11:12
     */
    public function sceneSetPayPassword()
    {
        return $this->only(['pay_password'])
            ->remove('pay_password', 'checkPayPassword')
            ->append('pay_password', 'length:4,8|alphaDash');
    }

    /**
     * @notes 获取微信手机号场景
     * @return UserValidate
     * @author Tab
     * @date 2021/8/24 15:13
     */
    public function sceneGetMobileByMnp()
    {
        return $this->only(['code']);
    }

    /**
     * @notes 发送验证码 -  重置支付密码
     * @return UserValidate
     * @author Tab
     * @date 2021/8/24 15:52
     */
    public function sceneResetPayPasswordCaptcha()
    {
        return $this->only(['mobile']);
    }

    /**
     * @notes 发送验证码 -  重置登录密码
     * @return UserValidate
     * @author Tab
     * @date 2021/8/25 16:34
     */
    public function sceneResetPasswordCaptcha()
    {
        return $this->only(['mobile']);
    }

    /**
     * @notes 发送验证码 -  绑定手机号
     * @return UserValidate
     * @author Tab
     * @date 2021/8/25 16:34
     */
    public function sceneBindMobileCaptcha()
    {
        return $this->only(['mobile']);
    }

    /**
     * @notes 发送验证码 -  变更手机号
     * @return UserValidate
     * @author Tab
     * @date 2021/8/25 16:34
     */
    public function sceneChangeMobileCaptcha()
    {
        return $this->only(['mobile']);
    }

    /**
     * @notes 绑定手机号场景
     * @return UserValidate
     * @author Tab
     * @date 2021/8/25 17:44
     */
    public function sceneBindMobile()
    {
        return $this->only(['mobile', 'code']);
    }

    /**
     * @notes 重置支付密码
     * @return UserValidate
     * @author Tab
     * @date 2021/8/24 16:14
     */
    public function sceneResetPayPassword()
    {
        return $this->only(['mobile', 'code', 'pay_password'])
            ->remove('pay_password', 'checkPayPassword')
            ->append('pay_password', 'length:4,8|alphaDash');
    }

    /**
     * @notes 重置登录密码
     * @return UserValidate
     * @author Tab
     * @date 2021/8/25 16:37
     */
    public function sceneResetPassword()
    {
        return $this->only(['mobile', 'code', 'password'])
            ->append('password', 'require|length:6,12|alphaDash');
    }

    /**
     * @notes 设置登录密码
     * @return UserValidate
     * @author Tab
     */
    public function sceneSetPassword()
    {
        return $this->only(['password'])
            ->append('password','require|length:6,20|alphaDash|checkComplexity');
    }

    /**
     * @notes 修改登录密码
     * @return UserValidate
     * @author Tab
     */
    public function sceneChangePassword()
    {
        return $this->only(['old_password','password'])
            ->append('password','require|length:6,20|alphaDash|checkComplexity');
    }

    /**
     * @notes 校验支付密码
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/11 20:35
     */
    public function checkPayPassword($value, $rule, $data)
    {
        $user = User::findOrEmpty($data['user_id']);
        if(empty($user->mobile)) {
            return '请先绑定手机号';
        }
        if(empty($user->pay_password)) {
            return '请先设置支付密码';
        }
        if($user->pay_password == md5($value)) {
            return true;
        }
        return '支付密码错误';
    }

    /**
     * @notes 校验收款用户
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/11 20:38
     */
    public function checktransferIn($value, $rule, $data)
    {
        $me = User::findOrEmpty($data['user_id']);
        if($me->sn == $data['transfer_in'] || $me->mobile == $data['transfer_in']) {
            return '不能自己转账给自己';
        }
        $transferIn = User::whereOr('sn', $data['transfer_in'])
            ->whereOr('mobile', $data['transfer_in'])
            ->findOrEmpty();
        if($transferIn->isEmpty()) {
            return '收款用户不存在';
        }
        return true;
    }

    /**
     * @notes 校验余额
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/11 20:42
     */
    public function checkMoney($value, $rule, $data)
    {
        $user = User::findOrEmpty($data['user_id']);
        if($user->user_money < $data['money']) {
            return '余额不足';
        }
        return true;
    }

    /**
     * @notes 校验密码复杂度
     * @param $value
     * @param $rue
     * @param $data
     * @author Tab
     * @date 2021/12/10 15:06
     */
    public function checkComplexity($value, $rue, $data)
    {
        $lowerCase = range('a', 'z');
        $upperCase = range('A', 'Z');
        $numbers = range(0, 9);
        $cases = array_merge($lowerCase, $upperCase);
        $caseCount = 0;
        $numberCount = 0;
        $passwordArr = str_split(trim(($data['password'] . '')));
        foreach ($passwordArr as $value) {
            if (in_array($value, $numbers)) {
                $numberCount++;
            }
            if (in_array($value, $cases)) {
                $caseCount++;
            }
        }
        if ($numberCount >= 1 && $caseCount >= 1) {
            return true;
        }
        return '密码需包含数字和字母';
    }
}