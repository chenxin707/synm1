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
namespace app\businessapi\logic;
use app\common\model\Admin;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\RegionService;
use think\facade\Config;

/**
 * 配置逻辑类
 * Class SettingController
 * @package app\businessapi\controller
 */
class SettingLogic
{

    /**
     * @notes 获取店铺配置
     * @return array
     * @author cjhao
     * @date 2023/2/16 14:35
     */
    public function getShopConfig()
    {
        $config = [
            'name'                  => ConfigService::get('shop', 'name'),
            'logo'                  => ConfigService::get('shop', 'logo'),
            'status'                => ConfigService::get('shop', 'status'),
            'mall_contact'          => ConfigService::get('shop', 'mall_contact', ''),
            'mall_contact_mobile'   => ConfigService::get('shop', 'mall_contact_mobile', ''),
            'return_contact'        => ConfigService::get('shop', 'return_contact', ''),
            'return_contact_mobile' => ConfigService::get('shop', 'return_contact_mobile', ''),
            'return_province'       => ConfigService::get('shop', 'return_province', ''),
            'return_city'           => ConfigService::get('shop', 'return_city', ''),
            'return_district'       => ConfigService::get('shop', 'return_district', ''),
            'return_address'        => ConfigService::get('shop', 'return_address', ''),
        ];
        $config['logo'] = FileService::getFileUrl($config['logo']);
        $config['region_address'] = RegionService::getAddress([$config['return_province'], $config['return_city'], $config['return_district']]);
        $config['address'] = $config['region_address'].$config['return_address'];
        return $config;

    }

    /**
     * @notes 设置店铺配置
     * @param $params
     * @author cjhao
     * @date 2023/2/16 14:39
     */
    public function setShopConfig($params)
    {

        $params['logo'] = FileService::setFileUrl($params['logo']);

        ConfigService::set('shop','name', $params['name']);
        ConfigService::set('shop','logo', $params['logo']);
        ConfigService::set('shop','status', $params['status']);
        ConfigService::set('shop','mall_contact', $params['mall_contact']);
        ConfigService::set('shop','mall_contact_mobile', $params['mall_contact_mobile']);
        ConfigService::set('shop','return_contact', $params['return_contact']);
        ConfigService::set('shop','return_contact_mobile', $params['return_contact_mobile']);
        ConfigService::set('shop','return_province', $params['return_province']);
        ConfigService::set('shop','return_city', $params['return_city']);
        ConfigService::set('shop','return_district', $params['return_district']);
        ConfigService::set('shop','return_address', $params['return_address']);

        return true;
    }


    /**
     * @notes 获取管理员信息
     * @param $adminId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2023/2/16 14:48
     */
    public function getAdminInfo($adminId)
    {
        return Admin::field('id,name,avatar')->find($adminId)->toArray();

    }

    /**
     * @notes 重置密码
     * @param $params
     * @param $adminId
     * @return bool|string
     * @author cjhao
     * @date 2023/2/16 14:55
     */
    public function resetPassword($params,$adminId)
    {
        try{

            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);
            Admin::update(['password'=>$password],['id'=>$adminId]);
            return true;

        }catch (\Exception $e) {
            return $e->getMessage();
        }


    }

}