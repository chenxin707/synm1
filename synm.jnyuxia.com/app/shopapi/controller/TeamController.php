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

namespace app\shopapi\controller;


use app\shopapi\lists\TeamLists;
use app\shopapi\lists\TeamRecordLists;
use app\shopapi\logic\TeamLogic;
use app\shopapi\validate\TeamValidate;

class TeamController extends BaseShopController
{
    public array $notNeedLogin = ['lists', 'detail'];

    /**
     * @notes 拼团活动商品列表
     * @author 张无忌
     * @date 2021/8/3 14:09
     */
    public function lists()
    {
        return $this->dataLists(new TeamLists());
    }

    /**
     * @notes 拼团商品详细
     * @author 张无忌
     * @date 2021/8/3 15:20
     */
    public function detail()
    {
        $params = (new TeamValidate())->goCheck('id');
        $result = TeamLogic::detail(intval($params['id']), $this->userId);
        if (is_array($result)) {
            return $this->success('获取成功', $result);
        }
        return $this->fail($result);
    }

    /**
     * @notes 拼团记录列表
     * @author 张无忌
     * @date 2021/8/4 17:40
     */
    public function record()
    {
        return $this->dataLists(new TeamRecordLists());
    }
}
