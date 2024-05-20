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

namespace app\platformapi\validate\shop;

use app\common\enum\YesNoEnum;
use app\common\model\PlatformShop;
use app\common\model\SetMeal;
use app\common\validate\BaseValidate;

class ShopValidate extends BaseValidate
{
    protected $rule = [
        'id'                        => 'require',
        'name'                      => 'require|max:32',
        'super_admin'               => 'require|alphaNum|length:3,12',
        'super_password'            => 'require|alphaDash|length:6,12',
        'super_password_confirm'    => 'require|confirm:super_password',
        'status'                    => 'require|in:0,1',
        'contact_mobile'            => 'mobile',
        'domain_alias'              => 'checkDomain',
        'set_meal_id'               => 'require|checkMeal',
        'expires_time'              => 'require',
    ];

    protected $message = [
        'id.require'                        => '参数缺失',
        'name.require'                      => '请输入商城名称',
        'name.max'                          => '商城名称不允许超过32个字符',
        'super_admin.require'               => '请输入超级管理员账号',
        'super_admin.alphaNum'              => '超级管理员账号只允许字母及数字',
        'super_admin.length'                => '超级管理员账号长度须在3-12个字符之间',
        'super_password.require'            => '请输入密码',
        'super_password.alphaDash'          => '密码只允许是字母、数字、下划线_及破折号-',
        'super_password.length'             => '密码长度须在6-12个字符之间',
        'super_password_confirm.require'    => '请确认密码',
        'super_password_confirm.confirm'    => '两次输入的密码不一致',
        'status.require'                    => '请选择状态',
        'status.in'                         => '状态值错误',
        'contact_mobile.mobile'             => '手机号码格式错误',
        'set_meal_id.require'               => '请选择商城套餐',
        'expires_time.require'              => '请选择到期时间',
    ];

    public function sceneAdd()
    {
        return $this->only(['name', 'super_admin', 'super_password', 'super_password_confirm', 'status', 'contact_mobile', 'set_meal_id', 'expires_time']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'status', 'contact_mobile','domain_alias', 'set_meal_id', 'expires_time']);
    }

    public function sceneChangeSuperAdmin()
    {
        return $this->only(['id', 'super_admin', 'super_password', 'super_password_confirm']);
    }

    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    public function sceneSwitchStatus()
    {
        return $this->only(['id']);
    }

    public function sceneSetMealLogLists()
    {
        return $this->only(['id']);
    }

    public function sceneChangeRemark()
    {
        return $this->only(['id']);
    }


    public function checkDomain($value,$rule,$data){
        $isCheckDomain = $data['is_check_domain'] ?? 0;
        //todo 验证域名是否合法
        if($isCheckDomain){
            if(preg_match("/^(http|https|ftp:\/\/).*$/",$value)){
                return '域名不需要带协议';
            }
            //验证别名是否解析到我们的服务器上
            $mainDomain = env('project.main_domain');
            if(empty($mainDomain)){
                return '请在.env文件的PROJECT属性添加:MAIN_DOMAIN='.$_SERVER['HTTP_HOST'];
            }
            //获取域名对应的ip
            $mainDomainIp = gethostbyname($mainDomain);
            $aliasDomainIp = gethostbyname($value);
            if($value == $mainDomain){
                return '域名别名与平台域名冲突';
            }
            if($mainDomainIp != $aliasDomainIp){
                return '请将域名别名解析到当前域名';
            }

        }

        $where[] = ['id','<>',$data['id']];
        $where[] = ['domain_alias','=',$value];
        $platformShop = PlatformShop::where($where)->findOrEmpty();
        if(!$platformShop->isEmpty()){
            return '域名别名已存在了';
        }
        return true;
    }


    public function checkMeal($value,$rule,$data){
        $meal = SetMeal::withoutGlobalScope()->where(['id'=>$value])->find();
        if(empty($meal)){
            return '套餐不存在，请重新选择';
        }
        if(YesNoEnum::YES != $meal['status']){
            return '套餐已关闭，不允许使用';
        }
        return true;
    }
}