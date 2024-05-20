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

namespace app\adminapi\controller\shop_notice;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\shop_notice\ShopNoticeLists;
use app\adminapi\logic\shop_notice\ShopNoticeLogic;
use app\adminapi\validate\shop_notice\ShopNoticeValidate;
use app\common\model\ShopNotice;

class ShopNoticeController extends BaseAdminController
{
    /**
     * @notes 添加商城公告
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/23 2:01 下午
     */
    public function add()
    {
        $params = (new ShopNoticeValidate())->post()->goCheck('add');
        (new ShopNoticeLogic())->add($params);
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 查看商城公告列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/23 2:16 下午
     */
    public function lists()
    {
        return $this->dataLists(new ShopNoticeLists());
    }

    /**
     * @notes 编辑商城公告
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 2:42 下午
     */
    public function edit()
    {
        $params = (new ShopNoticeValidate())->post()->goCheck('edit');
        (new ShopNoticeLogic())->edit($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 查看商城公告详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 2:49 下午
     */
    public function detail()
    {
        $params = (new ShopNoticeValidate())->goCheck('detail');
        $result = (new ShopNoticeLogic())->detail($params);
        return $this->success('',$result);
    }

    /**
     * @notes 修改商城公告状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 2:58 下午
     */
    public function status()
    {
        $params = (new ShopNoticeValidate())->post()->goCheck('status');
        (new ShopNoticeLogic())->status($params);
        return $this->success('修改成功',[],1,1);
    }

    /**
     * @notes 删除商城公告
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/23 3:04 下午
     */
    public function del()
    {
        $params = (new ShopNoticeValidate())->post()->goCheck('del');
        (new ShopNoticeLogic())->del($params);
        return $this->success('删除成功',[],1,1);
    }
}