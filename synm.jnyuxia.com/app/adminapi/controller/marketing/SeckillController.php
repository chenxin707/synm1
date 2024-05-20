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
use app\adminapi\lists\marketing\SeckillLists;
use app\adminapi\logic\marketing\SeckillLogic;
use app\adminapi\validate\marketing\SeckillValidate;


class SeckillController extends BaseAdminController
{
    /**
     * @notes 秒杀活动列表
     * @author 张无忌
     * @date 2021/7/23 16:56
     */
    public function lists()
    {
        return $this->dataLists(new SeckillLists());
    }

    /**
     * @notes 秒杀概况
     * @author 张无忌
     * @date 2021/7/26 16:44
     */
    public function survey()
    {
        $result = SeckillLogic::survey();
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 秒杀详细信息
     * @author 张无忌
     * @date 2021/7/23 17:00
     */
    public function detail()
    {
        $params = (new SeckillValidate())->goCheck('id');
        $result = SeckillLogic::detail($params);
        return $this->success('获取成功', $result);
    }

    /**
     * @notes 秒杀数据信息
     * @author 张无忌
     * @date 2021/7/23 17:00
     */
    public function info()
    {
        $params = (new SeckillValidate())->goCheck('id');
        $result = SeckillLogic::info($params);
        return $this->success('获取成功', $result, 1, 1);
    }

    /**
     * @notes 添加秒杀活动
     * @author 张无忌
     * @date 2021/7/23 16:57
     */
    public function add()
    {
        $params = (new SeckillValidate())->post()->goCheck('add');
        $result = SeckillLogic::add($params);
        if ($result === true) {
            return $this->success('添加成功', [],1, 1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 编辑秒杀活动
     * @author 张无忌
     * @date 2021/7/23 16:57
     */
    public function edit()
    {
        $params = (new SeckillValidate())->post()->goCheck('edit');
        $result = SeckillLogic::edit($params);
        if ($result === true) {
            return $this->success('编辑成功', [],1, 1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 删除秒杀活动
     * @author 张无忌
     * @date 2021/7/23 16:57
     */
    public function delete()
    {
        $params = (new SeckillValidate())->post()->goCheck('id');
        $result = SeckillLogic::delete($params);
        if ($result === true) {
            return $this->success('删除成功', [], 1, 1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 确认开启活动
     * @param $params
     * @return \think\response\Json
     * @author 张无忌
     * @date 2021/7/23 16:57
     */
    public function open($params)
    {
        $result = SeckillLogic::open($params);
        if ($result === true) {
            return $this->success('确认成功', [], 1, 1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 停止关闭活动
     * @param $params
     * @return \think\response\Json
     * @author 张无忌
     * @date 2021/7/23 16:58
     */
    public function stop($params)
    {
        $result = SeckillLogic::stop($params);
        if ($result === true) {
            return $this->success('结束成功', [],1, 1);
        }
        return $this->fail($result);
    }
}