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

use app\common\service\JsonService;
use app\shopapi\logic\LuckyDrawLogic;
use app\shopapi\validate\LuckyDrawValidate;

/**
 * 幸运抽奖
 */
class LuckyDrawController extends BaseShopController
{
    /**
     * @notes 获取活动信息
     * @author Tab
     * @date 2021/11/25 11:43
     */
    public function activity()
    {
        $params = (new LuckyDrawValidate())->goCheck('activity');
        $params['user_id'] = $this->userId;
        $result = LuckyDrawLogic::activity($params);
        return JsonService::data($result);
    }

    /**
     * @notes 抽奖
     * @author Tab
     * @date 2021/11/24 16:32
     */
    public function lottery()
    {
        $params = (new LuckyDrawValidate())->post()->goCheck('lottery', ['user_id' => $this->userId]);
        $result = LuckyDrawLogic::lottery($params);
        if ($result) {
            return JsonService::success('抽奖完成', $result, 1, 0);
        }
        return JsonService::fail(LuckyDrawLogic::getError());
    }

    /**
     * @notes 查看抽奖记录
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/25 10:49
     */
    public function record()
    {
        $params = (new LuckyDrawValidate())->goCheck('record');
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 25;
        $params['user_id'] = $this->userId;
        $result = LuckyDrawLogic::record($params);
        return JsonService::data($result);
    }

    /**
     * @notes 查看中奖名单
     * @author Tab
     * @date 2021/11/25 14:13
     */
    public function winningList()
    {
        $params = (new LuckyDrawValidate())->goCheck('winningList');
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 25;
        $result = LuckyDrawLogic::winningList($params);
        return JsonService::data($result);
    }
}