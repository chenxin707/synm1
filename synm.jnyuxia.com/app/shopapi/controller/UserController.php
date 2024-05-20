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
use app\shopapi\{
    logic\UserLogic,
    lists\TransferLists,
    validate\UserValidate,
    validate\SetUserInfoValidate
};

/**
 * 用户控制器
 * Class UserController
 * @package app\shopapi\controller
 */
class UserController extends BaseShopController
{
    public array $notNeedLogin = ['resetPasswordCaptcha', 'resetPassword'];
    /**
     * @notes 个人中心
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/6 19:16
     */
    public function centre()
    {
        $data = (new UserLogic)->centre($this->userInfo);
        return $this->success('', $data);
    }


    /**
     * @notes 设置用户信息
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/6 20:34
     */
    public function setInfo()
    {
        $params = (new SetUserInfoValidate())->post()->goCheck(null, ['id' => $this->userId]);
        (new UserLogic)->setInfo($this->userId, $params);
        return $this->success('操作成功', [],1,1);
    }

    /**
     * @notes 发送验证码 - 绑定手机号
     * @author Tab
     * @date 2021/8/25 17:35
     */
    public function bindMobileCaptcha()
    {
        $params = (new UserValidate())->post()->goCheck('bindMobileCaptcha');
        $code = mt_rand(1000, 9999);
        $result = event('Notice', [
            'scene_id' =>  NoticeEnum::BIND_MOBILE_CAPTCHA,
            'params' => [
                'user_id' => $this->userId,
                'code' => $code,
                'mobile' => $params['mobile']
            ]
        ]);
        if ($result[0] === true) {
            return $this->success('发送成功');
        }

        return $this->fail($result[0], [], 0, 1);
    }

    /**
     * @notes 发送验证码 - 变更手机号
     * @author Tab
     * @date 2021/8/25 17:35
     */
    public function changeMobileCaptcha()
    {
        $params = (new UserValidate())->post()->goCheck('changeMobileCaptcha');
        $code = mt_rand(1000, 9999);
        $result = event('Notice', [
            'scene_id' =>  NoticeEnum::CHANGE_MOBILE_CAPTCHA,
            'params' => [
                'user_id' => $this->userId,
                'code' => $code,
                'mobile' => $params['mobile']
            ]
        ]);
        if ($result[0] === true) {
            return $this->success('发送成功');
        }

        return $this->fail($result[0], [], 0, 1);
    }

    /**
     * @notes 绑定手机号
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/25 17:46
     */
    public function bindMobile()
    {
        $params = (new UserValidate())->post()->goCheck('bindMobile');
        $params['id'] = $this->userId;
        $result = UserLogic::bindMobile($params);
        if($result) {
            return $this->success('绑定成功', [], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 用户等级
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/9 10:18
     */
    public function userLevel()
    {
        $userLevel = (new UserLogic)->userLevel($this->userId);
        return $this->success('', $userLevel);
    }

    /**
     * @notes 余额转账
     * @author Tab
     * @date 2021/8/11 20:20
     */
    public function transfer()
    {
        $params = (new UserValidate())->post()->goCheck('transfer', ['user_id' => $this->userId]);
        $result = UserLogic::transfer($params);
        if($result) {
            return $this->success('转账成功', [], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 最近转账记录(3条)
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 10:17
     */
    public function transferRecent()
    {
        $result = UserLogic::transferRecent($this->userId);
        return $this->data($result);
    }

    /**
     * @notes 收款用户信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 10:53
     */
    public function transferIn()
    {
        $params = (new UserValidate())->goCheck('transferIn');
        $result = UserLogic::transferIn($params);
        if($result !== false) {
            return $this->data($result);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 判断用户是否已设置支付密码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/17 10:29
     */
    public function hasPayPassword()
    {
        $result = UserLogic::hasPayPassword($this->userId);
        return $this->data($result);
    }


    /**
     * @notes 设置/修改交易密码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 11:10
     */
    public function setPayPassword()
    {
        $params = (new UserValidate())->post()->goCheck('setPayPassword');
        $params['user_id'] = $this->userId;
        $result = UserLogic::setPayPassword($params);
        if($result) {
            return $this->success('修改成功',[], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 发送验证码 - 重置支付密码
     * @author Tab
     * @date 2021/8/24 15:49
     */
    public function resetPayPasswordCaptcha()
    {
        $params = (new UserValidate())->post()->goCheck('resetPayPasswordCaptcha');
        $code = mt_rand(1000, 9999);
        $result = event('Notice', [
            'scene_id' =>  NoticeEnum::FIND_PAY_PASSWORD_CAPTCHA,
            'params' => [
                'user_id' => $this->userId,
                'code' => $code,
                'mobile' => $params['mobile']
            ]
        ]);
        if ($result[0] === true) {
            return $this->success('发送成功');
        }

        return $this->fail($result[0], [], 0, 1);
    }

    /**
     * @notes 重置支付密码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/24 16:18
     */
    public function resetPayPassword()
    {
        $params = (new UserValidate())->post()->goCheck('resetPayPassword', ['id' => $this->userId]);
        $result = UserLogic:: resetPayPassword($params);
        if($result) {
            return $this->success('重置支付密码成功', [], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 发送验证码 - 重置登录密码
     * @author Tab
     * @date 2021/8/25 16:33
     */
    public function resetPasswordCaptcha()
    {
        $params = (new UserValidate())->post()->goCheck('resetPasswordCaptcha');
        $code = mt_rand(1000, 9999);
        $result = event('Notice', [
            'scene_id' =>  NoticeEnum::FIND_LOGIN_PASSWORD_CAPTCHA,
            'params' => [
                'user_id' => $this->userId,
                'code' => $code,
                'mobile' => $params['mobile']
            ]
        ]);
        if ($result[0] === true) {
            return $this->success('发送成功');
        }

        return $this->fail($result[0], [], 0, 1);
    }

    /**
     * @notes 重置登录密码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/25 16:35
     */
    public function resetPassword()
    {
        $params = (new UserValidate())->post()->goCheck('resetPassword');
        $result = UserLogic:: resetPassword($params);
        if($result) {
            return $this->success('重置登录密码成功', [], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 查看转账记录列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 11:40
     */
    public function transferLists()
    {
        return $this->dataLists(new TransferLists());
    }

    /**
     * @notes 钱包
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 14:55
     */
    public function wallet()
    {
        $result = UserLogic::wallet($this->userId);
        return $this->data($result);
    }

    /**
     * @notes 用户信息
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/25 17:18
     */
    public function info()
    {
        $result = UserLogic::info($this->userId);
        return $this->data($result);
    }

    /**
     * @notes 获取微信手机号并绑定
     * @return mixed
     * @author Tab
     * @date 2021/8/24 15:09
     */
    public function getMobileByMnp()
    {
        $params = (new UserValidate())->post()->goCheck('getMobileByMnp');
        $params['user_id'] = $this->userId;
        $result = UserLogic::getNewMobileByMnp($params);
        if($result === false) {
            return $this->fail(UserLogic::getError());
        }
        return $this->success('绑定成功', [], 1, 1);
    }

    /**
     * @notes 获取服务协议
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/24 16:44
     */
    public function getServiceAgreement()
    {
        $result = UserLogic::getServiceAgreement();
        return $this->data($result);
    }

    /**
     * @notes 获取隐私政策
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/24 16:44
     */
    public function getPrivacyPolicy()
    {
        $result = UserLogic::getPrivacyPolicy();
        return $this->data($result);
    }

    /**
     * @notes 获取版本号
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/24 16:47
     */
    public function getVersion()
    {
        return $this->data(['version' => $this->request->header('version')]);
    }

    /**
     * @notes 设置登录密码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/10/22 18:09
     */
    public function setPassword()
    {
        $params = (new UserValidate())->post()->goCheck('setPassword');
        $params['user_id'] = $this->userId;
        $result = UserLogic::setPassword($params);
        if($result) {
            return $this->success('设置成功',[], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 修改登录密码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/10/22 18:09
     */
    public function changePassword()
    {
        $params = (new UserValidate())->post()->goCheck('changePassword');
        $params['user_id'] = $this->userId;
        $result = UserLogic::changePassword($params);
        if($result) {
            return $this->success('修改成功',[], 1, 1);
        }
        return $this->fail(UserLogic::getError());
    }

    /**
     * @notes 判断用户是否设置登录密码
     * @return mixed
     * @author Tab
     * @date 2021/10/22 18:24
     */
    public function hasPassword()
    {
        $result =  UserLogic::hasPassword($this->userId);
        return $this->data([
            'has_password' => $result
        ]);
    }

    /**
     * @notes 获取与客服聊天记录
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 14:47
     */
    public function chatRecord()
    {
        return $this->success('', UserLogic::getChatRecord($this->userId));
    }
}