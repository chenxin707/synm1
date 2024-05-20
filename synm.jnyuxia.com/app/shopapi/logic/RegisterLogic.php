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

namespace app\shopapi\logic;

use app\common\logic\BaseLogic;
use app\common\model\User;
use app\common\model\UserLevel;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Config;

/**
 * 注册逻辑层
 * Class RegisterLogic
 * @package app\shopapi\logic
 */
class RegisterLogic extends BaseLogic
{
    public static function register($params)
    {
        try {
            $defaultAvatar = ConfigService::get('default_image', 'user_avatar');
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);
            // 创建用户
            $data = [
                'register_source' => $params['register_source'],
                'sn' => create_user_sn(),
                'nickname' => $params['mobile'],
                'avatar' => $defaultAvatar,
                'mobile' => $params['mobile'],
                'user_money' => 0,
                'user_integral' => 0,
                'total_order_amount' => 0,
                'total_order_num' => 0,
                'account' => $params['mobile'],
                'password' => $password,
                'user_growth' => 0,
                'code' => generate_code(),
                'user_earnings' => 0
            ];
            $user = User::duokaiCreate($data);
            // 注册奖励
            \app\common\logic\UserLogic::registerAward($user->id);

            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }




}