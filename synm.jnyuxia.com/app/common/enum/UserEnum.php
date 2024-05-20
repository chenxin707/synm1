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

/**
 * 用户枚举
 * Class UserEnum
 * @package app\common\enum
 */
class UserEnum
{
    /**
     * 成为下级条件
     * 1-邀请码
     */
    const INVITE_CONDITION_CODE = 1;

    /**
     * 邀请下级资格
     * INVITE_WAYS_ALL 全部用户可邀请
     * INVITE_WAYS_APPOINT 指定用户可邀请
     */
    const INVITE_WAYS_ALL = 1;
    const INVITE_WAYS_APPOINT = 2;

    /**
     * 指定用户
     * DISTRIBUTION_MEMBER 分销会员
     * SHAREHOLDER_MEMBER 股东会员
     * PROXY_MEMBER 代理会员
     */
    const DISTRIBUTION_MEMBER = 1;
    const SHAREHOLDER_MEMBER = 2;
    const PROXY_MEMBER = 3;
}