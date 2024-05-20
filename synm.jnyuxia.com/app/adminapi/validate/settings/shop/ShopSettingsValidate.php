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
namespace app\adminapi\validate\settings\shop;

use app\common\validate\BaseValidate;

/**
 * 店铺设置验证器
 * Class ShopSettingsValidate
 * @package app\adminapi\validate\settings\shop
 */
class ShopSettingsValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
        'logo' => 'require',
        'admin_login_image' => 'require',
        'login_restrictions' => 'require|in:0,1',
        'password_error_times' => 'requireIf:login_restrictions,1|integer|gt:0',
        'limit_login_time' => 'requireIf:login_restrictions,1|integer|gt:0',
        'status' => 'require|in:0,1',
        'share_page' => 'require|in:1',
        'mall_contact' => 'require',
        'mall_contact_mobile' => 'require|mobile',
        'return_contact' => 'require',
        'return_contact_mobile' => 'require|mobile',
        'return_province' => 'require',
        'return_city' => 'require',
        'return_district' => 'require',
        'return_address' => 'require',
    ];

    protected $message = [
        'name.require' => '请输入商城名称',
        'logo.require' => '请上传商城logo',
        'admin_login_image.require' => '请上传管理后台登录页图片',
        'login_restrictions.require' => '请选择管理后台登录限制',
        'login_restrictions.in' => '管理后台登录限制状态值有误',
        'password_error_times.requireIf' => '请输入密码错误次数',
        'password_error_times.integer' => '密码错误次数须为整型',
        'password_error_times.gt' => '密码错误次数须大于0',
        'limit_login_time.requireIf' => '请输入限制登录分钟数',
        'limit_login_time.integer' => '限制登录分钟数须为整型',
        'limit_login_time.gt' => '限制登录分钟数须大于0',
        'status.require' => '请选择商城状态',
        'status.in' => '商城状态值有误',
        'share_page.require' => '请选择分享页面',
        'share_page.in' => '分享页面值有误',
        'share_image.require' => '请上传分享图片',
        'mall_contact.require' => '请输入联系人姓名',
        'mall_contact_mobile.require' => '请输入联系人手机号',
        'mall_contact_mobile.mobile' => '联系人手机号格式错误',
        'return_contact.require' => '请输入退货联系人',
        'return_contact_mobile.require' => '请输入退货联系人手机号',
        'return_contact_mobile.mobile' => '退货联系人手机号格式错误',
        'return_province.require' => '请选择退货省份',
        'return_city.require' => '请选择退货城市',
        'return_district.require' => '请选择退货地区',
        'return_address.require' => '请输入退货详细地址',
    ];

    /**
     * @notes 设置店铺信息场景
     * @return ShopSettingsValidate
     * @author Tab
     * @date 2021/7/28 14:50
     */
    public function sceneSetShopInfo()
    {
        return $this->only(['name', 'logo', 'admin_name', 'admin_logo', 'admin_login_image', 'login_restrictions', 'password_error_times', 'limit_login_time', 'status', 'mall_contact', 'mall_contact_mobile', 'return_contact', 'return_contact_mobile', 'return_province', 'return_city', 'return_district', 'return_address']);
    }

    /**
     * @notes 分享设置场景
     * @return ShopSettingsValidate
     * @author Tab
     * @date 2021/7/28 15:34
     */
    public function sceneSetShareSetting()
    {
        return $this->only(['share_page', 'share_image']);
    }
}