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
namespace app\adminapi\logic\settings\user;
use app\common\{enum\DistributionConfigEnum,
    enum\UserEnum,
    model\Distribution,
    model\DistributionConfig,
    service\ConfigService,
    service\FileService};
/**
 * 设置-用户设置逻辑层
 * Class UserLogic
 * @package app\adminapi\logic\config
 */
class UserLogic
{


    /**
     * @notes 获取用户设置
     * @return array
     * @author cjhao
     * @date 2021/7/27 17:49
     */
    public static function getConfig():array
    {
        $poster = DistributionConfig::where(['key'=>'poster'])->value('value', DistributionConfigEnum::DEFAULT_POSTER);

        $config = [
            //默认头像
            'default_avatar'            => ConfigService::get('config', 'default_avatar',  FileService::getFileUrl(config('project.default_image.user_avatar'))),
            //开启邀请下级
            'invite_open'               => ConfigService::get('config', 'invite_open', config('project.default_user.invite_open')),
            //邀请下级资格
            'invite_ways'               => ConfigService::get('config', 'invite_ways', config('project.default_user.invite_ways')),
            //邀请下级指定用户
            'invite_appoint_user'       => ConfigService::get('config', 'invite_appoint_user', config('project.default_user.invite_appoint_user')),
            //成为下级条件
            'invite_condition'          => ConfigService::get('config', 'invite_condition', config('project.default_user.invite_condition')),
            //分销海报背景图
            'poster'                    => FileService::getFileUrl($poster),
        ];
        return $config;
    }

    /**
     * @notes 设置用户设置
     * @param array $postData
     * @return bool
     * @author cjhao
     * @date 2021/7/27 17:58
     */
    public function setConfig(array $params):bool
    {
        ConfigService::set('config', 'default_avatar', $params['default_avatar']);
        ConfigService::set('config', 'invite_open', $params['invite_open']);
        ConfigService::set('config', 'invite_ways', $params['invite_ways']);
        ConfigService::set('config', 'invite_appoint_user',$params['invite_appoint_user']);
        ConfigService::set('config', 'invite_condition', $params['invite_condition']);

        $config = DistributionConfig::where(['key' => 'poster'])->findOrEmpty();
        if($config->isEmpty()){
            DistributionConfig::duokaiCreate(['key'=>'poster','value'=>FileService::setFileUrl($params['poster'])]);
        }else{
            DistributionConfig::where(['key'=>'poster'])->update(['value'=>$params['poster']]);
        }
        return true;
    }

    public function getRegisterConfig():array
    {

        $config = [
            //注册方式
            'register_way'              => ConfigService::get('config', 'register_way', config('project.login.register_way')),
            //登录方式
            'login_way'                 => ConfigService::get('config', 'login_way',  config('project.login.login_way')),
            //手机号码注册需验证码
            'is_mobile_register_code'   => ConfigService::get('config', 'is_mobile_register_code',  config('project.login.is_mobile_register_code')),
            //注册强制绑定手机
            'coerce_mobile'             => ConfigService::get('config', 'coerce_mobile',  config('project.login.coerce_mobile')),
            //公众号微信授权登录
            'h5_wechat_auth'            => ConfigService::get('config', 'h5_wechat_auth',  config('project.login.h5_wechat_auth')),
            //公众号自动微信授权登录
            'h5_auto_wechat_auth'       => ConfigService::get('config', 'h5_auto_wechat_auth',  config('project.login.h5_auto_wechat_auth')),
            //小程序微信授权登录
            'mnp_wechat_auth'           => ConfigService::get('config', 'mnp_wechat_auth',  config('project.login.mnp_wechat_auth')),
            //小程序自动微信授权登录
            'mnp_auto_wechat_auth'      => ConfigService::get('config', 'mnp_auto_wechat_auth',  config('project.login.mnp_auto_wechat_auth')),
            //APP微信授权登录
            'app_wechat_auth'           => ConfigService::get('config', 'app_wechat_auth',  config('project.login.app_wechat_auth')),
            //字节小程序授权登录
            'toutiao_auth'           => ConfigService::get('config', 'toutiao_auth',  config('project.login.toutiao_auth')),
            //字节小程序自动授权登录
            'toutiao_auto_auth'      => ConfigService::get('config', 'toutiao_auto_auth',  config('project.login.toutiao_auto_auth')),
        ];
        return $config;
    }


    /**
     * @notes 设置登录注册
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/9/14 17:20
     */
    public static function setRegisterConfig(array $params):bool
    {
        //注册方式:1-手机号注册
        ConfigService::set('config', 'register_way', $params['register_way']);
        //登录方式：1-账号密码登录；2-手机短信验证码登录
        ConfigService::set('config', 'login_way', $params['login_way']);
        //手机号码注册需验证码
        ConfigService::set('config', 'is_mobile_register_code', $params['is_mobile_register_code']);
        //注册强制绑定手机
        ConfigService::set('config', 'coerce_mobile', $params['coerce_mobile']);
        //公众号微信授权登录
        ConfigService::set('config', 'h5_wechat_auth', $params['h5_wechat_auth']);
        //公众号自动微信授权登录
        ConfigService::set('config', 'h5_auto_wechat_auth', $params['h5_auto_wechat_auth']);
        //小程序微信授权登录
        ConfigService::set('config', 'mnp_wechat_auth', $params['mnp_wechat_auth']);
        //小程序自动微信授权登录
        ConfigService::set('config', 'mnp_auto_wechat_auth', $params['mnp_auto_wechat_auth']);
        //APP微信授权登录
        ConfigService::set('config', 'app_wechat_auth', $params['app_wechat_auth']);
        //字节小程序授权登录
        ConfigService::set('config', 'toutiao_auth', $params['toutiao_auth']);
        //字节小程序自动授权登录
        ConfigService::set('config', 'toutiao_auto_auth', $params['toutiao_auto_auth']);
        return true;

    }

    /**
     * @notes 获取用户提现
     * @return array
     * @author cjhao
     * @date 2021/9/14 17:22
     */
    public function getWithdrawConfig():array
    {
        $config = [
            //提现方式：1-钱包余额；2-微信零钱；3-银行卡；4-微信收款码；5-支付宝收款码
            'withdraw_way'              => ConfigService::get('config', 'withdraw_way'),
            //微信零钱接口：1-企业付款到零钱；2-商家转账到零钱
            'transfer_way'              => ConfigService::get('config', 'transfer_way',1),
            //最低提现金额
            'withdraw_min_money'        => ConfigService::get('config', 'withdraw_min_money'),
            //最高提现金额
            'withdraw_max_money'        => ConfigService::get('config', 'withdraw_max_money'),
            //提现手续费
            'withdraw_service_charge'   => ConfigService::get('config', 'withdraw_service_charge'),
        ];
        return $config;

    }

    /**
     * @notes 设置提现
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/9/14 17:24
     */
    public function setWithdrawConfig(array $params):bool
    {
        //提现方式：1-钱包余额；2-微信零钱；3-银行卡；4-微信收款码；5-支付宝收款码
        ConfigService::set('config', 'withdraw_way',$params['withdraw_way']);
        //微信零钱接口：1-企业付款到零钱；2-商家转账到零钱
        ConfigService::set('config', 'transfer_way',$params['transfer_way']);
        //最低提现金额
        ConfigService::set('config', 'withdraw_min_money', $params['withdraw_min_money']);
        //最高提现金额
        ConfigService::set('config', 'withdraw_max_money', $params['withdraw_max_money']);
        //提现手续费
        ConfigService::set('config', 'withdraw_service_charge', $params['withdraw_service_charge']);
        return true;

    }

    /**
     * @notes 判断用户是否具有邀请下级资格
     * @param $userId
     * @return bool|mixed
     * @author Tab
     * @date 2021/8/4 15:29
     */
    public static function eligible($userId)
    {
        // 邀请下级资格
        $inviteWays = ConfigService::get('config', 'invite_ways', config('project.default_user.invite_ways'));
        // 全部用户可邀请
        if($inviteWays == UserEnum::INVITE_WAYS_ALL) {
            return true;
        }
        // 指定用户可邀请
        $inviteAppointUser = ConfigService::get('config', 'invite_appoint_user', config('project.default_user.invite_appoint_user'));
        foreach($inviteAppointUser as $value) {
            // 分销会员
            if($value == UserEnum::DISTRIBUTION_MEMBER) {
                $isDistribution = Distribution::where('user_id', $userId)->value('is_distribution');
                return $isDistribution;
            }
            // 股东会员
            if($value == UserEnum::SHAREHOLDER_MEMBER) {
                return false;
            }
            // 代理会员
            if($value == UserEnum::PROXY_MEMBER) {
                return false;
            }
        }
        return false;
    }

}