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


use app\shopapi\lists\SeckillLists;
use app\shopapi\logic\SeckillLogic;
use app\shopapi\validate\SeckillValidate;

class SeckillController extends BaseShopController
{
    public array $notNeedLogin = ['lists', 'detail'];

    /**
     * @notes 秒杀活动列表
     * @author 张无忌
     * @date 2021/7/27 15:12
     */
    public function lists()
    {
        return $this->dataLists(new SeckillLists());
    }

    /**
     * @notes 秒杀活动详细
     * @author 张无忌
     * @date 2021/7/27 15:15
     */
    public function detail()
    {
        $params = (new SeckillValidate())->goCheck('id');
        $params['user_id'] = $this->userId;
        $result = SeckillLogic::detail($params);
        if (is_array($result)) {
            return $this->success('获取成功', $result);
        }
        return $this->fail($result);
    }

    /**
     * @notes 秒杀下单
     * @return \think\response\Json
     * @throws @\Exception
     * @author 张无忌
     * @date 2021/8/5 17:28
     */
    public function buy()
    {
        $params = (new SeckillValidate())->post()->goCheck('buy');
        $info = SeckillLogic::settlement($params, $this->userId);

        if ($params['action'] == 'settle') {
            if (is_array($info)) {
                return $this->success('获取成功', $info);
            }
            return $this->fail($info);
        }

        if (!is_array($info)) {
            return $this->fail($info);
        }

        $result = SeckillLogic::buy($info, $this->userId);

        if (is_array($result)) {
            return $this->success('下单成功', $result);
        }
        return $this->fail($result);
    }
}