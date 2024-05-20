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
namespace app\businessapi\controller;
use app\businessapi\logic\FinanceLogic;

/**
 * 财务控制器类
 * Class FinanceController
 * @package app\businessapi\controller
 */
class FinanceController extends BaseBusinesseController
{
    /**
     * @notes
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/17 16:43
     */
    public function dataCenter()
    {
        $result = (new FinanceLogic())->dataCenter();
        return $this->data($result);
    }


    /**
     * @notes 交易分析
     * @return \think\response\Json
     * @author cjhao
     * @date 2023/2/17 16:43
     */
    public function dealCenter()
    {
        $result = (new FinanceLogic())->dealCenter();
        return $this->data($result);
    }


    /**
     * @notes 商品销售分析
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2023/2/17 16:58
     */
    public function goodsCenter()
    {
        $result = (new FinanceLogic())->goodsCenter();
        return $this->data($result);
    }

    /**
     * @notes 访客分析
     * @return \think\response\Json
     * @throws \Exception
     * @author cjhao
     * @date 2023/2/17 17:01
     */
    public function visitCenter()
    {
        $result = (new FinanceLogic())->visitCenter();
        return $this->data($result);

    }



}