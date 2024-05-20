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

namespace app\adminapi\controller\kefu;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\kefu\KefuLangLists;
use app\adminapi\logic\kefu\KefuLangLogic;
use app\adminapi\validate\kefu\KefuLangValidate;

/**
 * 客服话术控制器
 * Class KefuController
 * @package app\adminapi\controller\kefu
 */
class KefuLangController extends BaseAdminController
{

    /**
     * @notes 获取客服话术列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/8 18:52
     */
    public function lists()
    {
        return $this->dataLists(new KefuLangLists());
    }


    /**
     * @notes 添加话术
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/8 18:53
     */
    public function add()
    {
        $params = (new KefuLangValidate())->post()->goCheck('add');
        (new KefuLangLogic())->add($params);
        return $this->success('添加成功',[],1,1);
    }


    /**
     * @notes 编辑话术
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/8 18:53
     */
    public function edit()
    {
        $params = (new KefuLangValidate())->post()->goCheck('edit');
        KefuLangLogic::edit($params);
        return $this->success('编辑成功', [], 1, 1);
    }


    /**
     * @notes 删除话术
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/8 18:53
     */
    public function del()
    {
        $params = (new KefuLangValidate())->post()->goCheck('del');
        KefuLangLogic::del($params['id']);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取话术详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/8 18:55
     */
    public function detail()
    {
        $params = (new KefuLangValidate())->goCheck('detail');
        $result = KefuLangLogic::detail($params['id']);
        return $this->success('获取成功', $result);
    }


}