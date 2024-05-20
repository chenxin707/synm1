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

class Distribution extends BaseModel
{
    /**
     * @notes 是否为分销会员 获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/20 17:13
     */
    public function getIsDistributionDescAttr($value)
    {
        $desc = ['普通会员', '分销商'];
        return $desc[$value];
    }

    /**
     * @notes 成为分销会员时间获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/5 15:11
     */
    public function getDistributionTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
}