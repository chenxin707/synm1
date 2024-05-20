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

/**
 * 用户等级模型
 * Class UserLevel
 * @package app\common\model
 */
class UserLevel extends BaseModel
{
    //json转数组
    protected $json = ['condition'];
    protected $jsonAssoc = true;

    /**
     * @notes 获取用户等级名称
     * @param $level_id
     * @return mixed|string
     * @author Tab
     * @date 2021/7/27 15:42
     */
    public static function getLevelName($level_id)
    {
        $levels = self::column('name', 'id');
        return $levels[$level_id] ?? '无等级';
    }

    /**
     * @notes 背景图片域名
     * @param $value
     * @return string
     * @author cjhao
     * @date 2021/9/15 18:22
     */
    public static function getBackgroundImageAttr($value){
        return trim($value) ? FileService::getFileUrl($value) : '';
    }

    /**
     * @notes 设置背景图片
     * @param $value
     * @return mixed|string
     * @author cjhao
     * @date 2021/9/15 18:22
     */
    public function setBackgroundImageAttr($value)
    {
        return trim($value) ? FileService::setFileUrl($value) : '';
    }
}