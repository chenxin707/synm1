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

namespace app\common\model;

use app\common\service\FileService;

use think\facade\Config;

/**
 * 平台管理员
 */
class PlatformAdmin extends BaseModel
{
    // 关闭全局查询范围
    protected $globalScope = [];

    /**
     * @notes 获取头像获取器
     * @param $value
     * @author Tab
     * @date 2021/12/13 16:25
     */
    public function getAvatarAttr($value)
    {
        $defaultAvatar = FileService::getFileUrl(Config::get('platform.default_image.platform_admin_avatar') );
        return empty(trim($value)) ? $defaultAvatar : FileService::getFileUrl($value);
    }

    /**
     * @notes 头像设置器
     * @param $value
     * @author Tab
     * @date 2021/12/13 16:27
     */
    public function setAvatarAttr($value)
    {
        if (empty(trim($value))) {
            return '';
        }
        return FileService::setFileUrl($value);
    }
}