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

namespace app\common\enum;


class TeamEnum
{
    // 拼团活动状态
    const TEAM_STATUS_NOT     = 1; //未开始
    const TEAM_STATUS_CONDUCT = 2; //进行中
    const TEAM_STATUS_END     = 3; //已结束

    const TEAM_FOUND_CONDUCT = 0; //进行中
    const TEAM_FOUND_SUCCESS = 1; //拼团成功
    const TEAM_FOUND_FAIL    = 2; //拼团失败

    /**
     * @notes 拼团活动状态
     * @param bool $value
     * @return array|mixed
     * @author 张无忌
     * @date 2021/7/20 17:55
     */
    public static function getActivityStatusDesc($value = true)
    {
        $data = [
            self::TEAM_STATUS_NOT     => '未开始',
            self::TEAM_STATUS_CONDUCT => '进行中',
            self::TEAM_STATUS_END     => '已结束'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 拼团状态
     * @param bool $value
     * @return array|mixed
     * @author 张无忌
     * @date 2021/8/4 19:02
     */
    public static function getStatusDesc($value = true)
    {
        $data = [
            self::TEAM_FOUND_CONDUCT  => '拼团中',
            self::TEAM_FOUND_SUCCESS  => '拼团成功',
            self::TEAM_FOUND_FAIL     => '拼团失败'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }
}