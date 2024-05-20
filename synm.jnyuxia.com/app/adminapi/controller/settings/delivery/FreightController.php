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

namespace app\adminapi\controller\settings\delivery;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\settings\delivery\FreightLists;
use app\adminapi\logic\settings\delivery\FreightLogic;
use app\adminapi\validate\settings\delivery\FreightValidate;

class FreightController extends BaseAdminController
{
    /**
     * @notes 添加运费模版
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/30 2:48 下午
     */
    public function add()
    {
        $params = (new FreightValidate())->post()->goCheck('add');
        $result = (new FreightLogic())->add($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 查看运费模版列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/30 4:56 下午
     */
    public function lists()
    {
        return $this->dataLists(new FreightLists());
    }

    /**
     * @notes 编辑运费模版
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/30 6:06 下午
     */
    public function edit()
    {
        $params = (new FreightValidate())->post()->goCheck('edit');
        $result = (new FreightLogic())->edit($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 查看运费模版详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/30 6:52 下午
     */
    public function detail()
    {
        $params = (new FreightValidate())->goCheck('detail');
        $result = (new FreightLogic())->detail($params);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 删除运费模版
     * @return \think\response\Json
     * @author ljj
     * @date 2021/7/30 7:00 下午
     */
    public function del()
    {
        $params = (new FreightValidate())->post()->goCheck('del');
        (new FreightLogic())->del($params);
        return $this->success('删除成功',[],1,1);
    }
}