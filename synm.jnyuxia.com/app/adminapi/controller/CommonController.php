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


use app\adminapi\logic\CommonLogic;

/**
 * 公共控制器
 * Class CommonController
 * @package app\adminapi\controller
 */
class CommonController extends BaseAdminController
{
    /**
     * @notes 获取活动列表
     * @author 张无忌
     * @date 2021/10/12 10:05
     */
    public function activity()
    {
        $get = $this->request->get();
        $type = $this->request->get( 'type');
        $lists = CommonLogic::getActivity($get, $type);
        return $this->success('获取成功', $lists);
    }

    /**
     * @notes 获取活动商品列表
     * @author 张无忌
     * @date 2021/10/9 18:42
     */
    public function activityGoods()
    {
        $type        = $this->request->get('type');
        $activity_id = $this->request->get('activity_id');
        $keyword     = $this->request->get('keyword', '');
        $lists = CommonLogic::getActivityGoods($type, $activity_id, $keyword);

        return $this->success('获取成功', $lists);
    }
}