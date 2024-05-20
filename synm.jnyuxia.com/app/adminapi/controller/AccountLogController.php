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

namespace app\adminapi\controller;

use app\common\enum\AccountLogEnum;

/***
 * 账户流水控制器
 * Class AccountLogController
 * @package app\adminapi\controller
 */
class AccountLogController extends BaseAdminController
{
    /**
     * @notes 查看账户流水列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 15:18
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 获取变动类型
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/12 15:41
     */
    public function getChangeType()
    {
        return $this->data(AccountLogEnum::getChangeTypeDesc('',true));
    }

    /**
     * @notes 获取不可提现余额变动类型
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/25 20:29
     */
    public function getBnwChangeType()
    {
        return $this->data(AccountLogEnum::getBnwChangeTypeDesc());
    }

    /**
     * @notes 获取积分类型描述
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/25 20:29
     */
    public function getIntegralChangeType()
    {
        return $this->data(AccountLogEnum::getIntegralChangeTypeDesc());
    }

    /**
     * @notes 获取可提现余额变动类型
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/28 11:14
     */
    public function getBwChangeType()
    {
        return $this->data(AccountLogEnum::getBWChangeTypeDesc());
    }
}