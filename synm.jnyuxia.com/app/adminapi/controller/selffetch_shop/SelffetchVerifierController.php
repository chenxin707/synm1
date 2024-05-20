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

namespace app\adminapi\controller\selffetch_shop;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\selffetch_shop\SelffetchVerifierLists;
use app\adminapi\logic\selffetch_shop\SelffetchVerifierLogic;
use app\adminapi\validate\selffetch_shop\SelffetchVerifierValidate;

/**
 * 核销员控制器
 * Class SelffetchVerifierController
 * @package app\adminapi\controller\selffetch_shop
 */
class SelffetchVerifierController extends BaseAdminController
{
    /**
     * @notes 添加核销员
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 7:24 下午
     */
    public function add()
    {
        $params = (new SelffetchVerifierValidate())->post()->goCheck('add');
        (new SelffetchVerifierLogic())->add($params);
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 编辑核销员
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 7:37 下午
     */
    public function edit()
    {
        $params = (new SelffetchVerifierValidate())->post()->goCheck('edit');
        (new SelffetchVerifierLogic())->edit($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 核销员详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 7:49 下午
     */
    public function detail()
    {
        $params = (new SelffetchVerifierValidate())->goCheck('detail');
        $result = (new SelffetchVerifierLogic())->detail($params);
        return $this->success('',$result);
    }

    /**
     * @notes 查看核销员列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 8:17 下午
     */
    public function lists()
    {
        return $this->dataLists(new SelffetchVerifierLists());
    }

    /**
     * @notes 修改核销员状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 8:21 下午
     */
    public function status()
    {
        $params = (new SelffetchVerifierValidate())->post()->goCheck('status');
        (new SelffetchVerifierLogic())->status($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 删除核销员
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/11 8:27 下午
     */
    public function del()
    {
        // TODO 暂无限制，可以任意删除
        $params = (new SelffetchVerifierValidate())->post()->goCheck('del');
        (new SelffetchVerifierLogic())->del($params);
        return $this->success('删除成功',[],1,1);
    }
}