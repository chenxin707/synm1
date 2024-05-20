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
use app\adminapi\lists\marketing\CouponCommenLists;
use app\adminapi\lists\marketing\CouponLists;
use app\adminapi\lists\marketing\CouponRecordLists;
use app\adminapi\logic\marketing\CouponLogic;
use app\adminapi\validate\marketing\CouponValidate;
use think\response\Json;

/**
 * 优惠券控制器
 * Class CouponController
 * @package app\adminapi\controller\marketing
 */
class CouponController extends BaseAdminController
{
    /**
     * @notes 优惠券公共接口
     * @return Json
     * @author cjhao
     * @date 2021/9/7 17:46
     */
    public function commonLists()
    {
        return $this->dataLists(new CouponCommenLists());
    }
    /**
     * @notes 优惠券列表
     * @return Json
     * @author 张无忌
     * @date 2021/7/21 15:45
     */
    public function lists()
    {
        return $this->dataLists(new CouponLists());
    }


    /**
     * @notes 获取优惠券详细
     * @return Json
     * @author 张无忌
     * @date 2021/7/20 16:28
     */
    public function detail()
    {
        $params = (new CouponValidate())->goCheck('detail');
        $detail = CouponLogic::detail($params);
        return $this->success('获取成功', $detail);
    }

    /**
     * @notes 添加优惠券
     * @return Json
     * @author 张无忌
     * @date 2021/7/20 14:40
     */
    public function add()
    {
        $params = (new CouponValidate())->post()->goCheck('add');
        CouponLogic::add($params);
        return $this->success('添加成功');
    }

    /**
     * @notes 编辑优惠券
     * @return Json
     * @author 张无忌
     * @date 2021/7/20 14:59
     */
    public function edit()
    {
        $params = (new CouponValidate())->post()->goCheck('edit');
        $result = CouponLogic::edit($params);
        if ($result === true) {
            return $this->success('编辑成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 删除优惠券
     * @return Json
     * @author 张无忌
     * @date 2021/7/20 15:45
     */
    public function delete()
    {
        $params = (new CouponValidate())->post()->goCheck('delete');
        $result = CouponLogic::delete($params);
        if ($result === true) {
            return $this->success('删除成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 优惠券基本信息
     * @author 张无忌
     * @date 2021/7/20 18:41
     */
    public function info()
    {
        $params = (new CouponValidate())->goCheck('detail');
        $result = CouponLogic::info($params);
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 开启发放优惠券
     * @author 张无忌
     * @date 2021/7/20 18:39
     */
    public function open()
    {
        $params = (new CouponValidate())->post()->goCheck('detail');
        CouponLogic::open($params);
        return $this->success('开启成功');
    }

    /**
     * @notes 结束发放优惠券
     * @author 张无忌
     * @date 2021/7/20 18:39
     */
    public function stop()
    {
        $params = (new CouponValidate())->post()->goCheck('detail');
        CouponLogic::stop($params);
        return $this->success('结束成功');
    }

    /**
     * @notes 卖家发放优惠券
     * @author 张无忌
     * @date 2021/7/20 18:40
     */
    public function send()
    {
        $params = (new CouponValidate())->post()->goCheck('send');
        $result = CouponLogic::send($params);
        if ($result === true) {
            return $this->success('发放成功');
        }
        return $this->fail($result);
    }

    /**
     * @notes 优惠券概况
     * @author 张无忌
     * @date 2021/7/21 15:46
     */
    public function survey()
    {
        $result = CouponLogic::survey();
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 领取记录
     * @author 张无忌
     * @date 2021/7/21 16:46
     */
    public function record()
    {
        return $this->dataLists(new CouponRecordLists());
    }

    /**
     * @notes 作废
     * @author 张无忌
     * @date 2021/7/21 17:12
     */
    public function void()
    {
        $params = (new CouponValidate())->post()->goCheck('void');
        CouponLogic::void($params);
        return $this->success('作废成功', [], 1);
    }
}