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
namespace app\adminapi\controller\marketing;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\marketing\DiscountLogic;
use app\adminapi\validate\marketing\DiscountValidate;

/**
 * 会员折扣
 * Class DiscountController
 * @package app\adminapi\controller\marketing
 */
class DiscountController extends BaseAdminController
{

    /**
     * @notes 折扣列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/5/5 12:04
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 返回其他状态列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/5/9 18:57
     */
    public function otherLists()
    {
        $otherLists = (new DiscountLogic())->otherLists();
        return $this->success('', $otherLists);
    }


    /**
     * @notes 参与折扣活动
     * @author cjhao
     * @date 2022/5/5 16:27
     */
    public function join()
    {
        $params = (new DiscountValidate())->post()->goCheck('join');
        (new DiscountLogic)->join($params);
        return $this->success('设置成功');
    }


    /**
     * @notes 退出折扣活动
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/5/5 17:14
     */
    public function quit()
    {
        $params = (new DiscountValidate())->post()->goCheck('join');
        (new DiscountLogic)->quit($params);
        return $this->success('设置成功');
    }

    /**
     * @notes 获取折扣商品详情
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/5/6 16:47
     */
    public function detail()
    {
        $params = (new DiscountValidate())->goCheck('detail');
        $detail = (new DiscountLogic())->detail($params);
        return $this->success('', $detail);
    }


    /**
     * @notes 设置会员价
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/5/7 9:11
     */
    public function setDiscount()
    {
        $params = (new DiscountValidate())->post()->goCheck('setDiscount');
        $result = (new DiscountLogic())->setDiscount($params);
        if (true === $result) {
            return $this->success('设置成功');
        }
        return $this->fail($result);


    }
}