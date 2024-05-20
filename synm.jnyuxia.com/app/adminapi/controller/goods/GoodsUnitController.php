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

namespace app\adminapi\controller\goods;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\goods\GoodsUnitLists;
use app\adminapi\logic\goods\GoodsUnitLogic;
use app\adminapi\validate\goods\GoodsUnitValidate;
use app\common\model\GoodsUnit;

class GoodsUnitController extends BaseAdminController
{
    /**
     * @notes 添加商品单位
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 5:49 下午
     */
    public function add()
    {
        $params = (new GoodsUnitValidate())->post()->goCheck('add');
        (new GoodsUnitLogic())->add($params);
        return $this->success('商品单位添加成功',[],1,1);
    }

    /**
     * @notes 查看商品单位列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 6:27 下午
     */
    public function lists()
    {
        return $this->dataLists(new GoodsUnitLists());
    }

    /**
     * @notes 删除商品单位
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 6:37 下午
     */
    public function del()
    {
        $params = (new GoodsUnitValidate())->post()->goCheck('del');
        (new GoodsUnitLogic())->del($params);
        return $this->success('商品单位删除成功',[],1,1);
    }

    /**
     * @notes 编辑商品单位
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 6:52 下午
     */
    public function edit()
    {
        $params = (new GoodsUnitValidate())->post()->goCheck('edit');
        (new GoodsUnitLogic())->edit($params);
        return $this->success('商品单位修改成功',[],1,1);
    }

    /**
     * @notes 查看商品单位详情
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/19 7:01 下午
     */
    public function detail()
    {
        $params = (new GoodsUnitValidate())->goCheck('detail');
        $result = (new GoodsUnitLogic())->detail($params);
        return $this->success('商品单位详情获取成功',$result,1,0);
    }
}