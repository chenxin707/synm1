<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\platformapi\controller\shop;


use app\platformapi\controller\BasePlatformController;
use app\platformapi\lists\shop\SetMealLists;
use app\platformapi\logic\shop\SetMealLogic;
use app\platformapi\validate\shop\SetMealValidate;

class SetMealController extends BasePlatformController
{
    /**
     * @notes 查看套餐列表
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/3 6:28 下午
     */
    public function lists()
    {
        return $this->dataLists(new SetMealLists());
    }

    /**
     * @notes 添加套餐
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/4 9:35 上午
     */
    public function add()
    {
        $params = (new SetMealValidate())->post()->goCheck('add');
        $result = (new SetMealLogic())->add($params);
        if ($result !== true) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 套餐详情
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/4 10:53 上午
     */
    public function detail()
    {
        $params = (new SetMealValidate())->get()->goCheck('detail');
        $result = (new SetMealLogic())->detail($params['id']);
        return $this->success('',$result);
    }

    /**
     * @notes 编辑套餐
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/4 10:56 上午
     */
    public function edit()
    {
        $params = (new SetMealValidate())->post()->goCheck('edit');
        $result = (new SetMealLogic())->edit($params);
        if ($result !== true) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 删除套餐
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/4 3:24 下午
     */
    public function del()
    {
        $params = (new SetMealValidate())->post()->goCheck('del');
        (new SetMealLogic())->del($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 更新套餐状态
     * @return \think\response\Json
     * @author ljj
     * @date 2022/3/4 3:36 下午
     */
    public function status()
    {
        $params = (new SetMealValidate())->post()->goCheck('status');
        (new SetMealLogic())->status($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 获取套餐可关联的营销应用
     */
    public function getMealModule() {
        $data = SetMealLogic::getMealModule();
        return $this->data($data);
    }
}
