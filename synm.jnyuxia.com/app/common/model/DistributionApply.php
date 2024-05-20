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

class DistributionApply extends BaseModel
{
    /**
     * @notes 审核状态获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/17 11:52
     */
    public function getStatusDescAttr($value)
    {
        $desc = ['待审核', '审核通过', '审核失败'];
        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取审核状态提示信息
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/17 11:57
     */
    public static function getStatusTips($value)
    {
        $tips = ['已提交,等待客服审核', '恭喜您，审核通过', '审核失败，请重新提交申请'];
        return $tips[$value] ?? '';
    }
}