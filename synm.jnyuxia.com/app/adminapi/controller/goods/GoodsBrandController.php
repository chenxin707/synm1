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
use app\adminapi\lists\goods\GoodsBrandLists;
use app\adminapi\logic\goods\GoodsBrandLogic;
use app\adminapi\validate\goods\GoodsBrandValidate;
use app\common\model\GoodsBrand;

class GoodsBrandController extends BaseAdminController
{
    /**
     * @notes 添加商品品牌
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/14 3:04
     */
    public function add()
    {
        $params = (new GoodsBrandValidate())->post()->goCheck('add');
        (new GoodsBrandLogic())->add($params);
        return $this->success('添加商品品牌成功',[],1,1);
    }

    /**
     * @notes 查看商品品牌列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/14 5:41
     */
    public function lists()
    {
        return $this->dataLists(new GoodsBrandLists());
    }

    /**
     * @notes 删除商品品牌
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/14 7:07
     */
    public function del()
    {
        $params = (new GoodsBrandValidate())->post()->goCheck('del');
        (new GoodsBrandLogic())->del($params);
        return $this->success('删除成功',[],1,1);
    }

    /**
     * @notes 编辑商品品牌
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/15 10:55
     */
    public function edit()
    {
        $params = (new GoodsBrandValidate())->post()->goCheck('edit');
        (new GoodsBrandLogic())->edit($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 修改商品品牌显示状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/15 11:42
     */
    public function status()
    {
        $params = (new GoodsBrandValidate())->post()->goCheck('status');
        (new GoodsBrandLogic())->status($params);
        return $this->success('状态修改成功',[],1,1);
    }

    /**
     * @notes 查看商品品牌详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 4:46 下午
     */
    public function detail()
    {
        $params = (new GoodsBrandValidate())->goCheck('detail');
        $result = (new GoodsBrandLogic())->detail($params);
        return $this->success('商品品牌详情获取成功',$result,1,0);
    }
}