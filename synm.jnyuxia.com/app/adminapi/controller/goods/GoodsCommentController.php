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
use app\adminapi\lists\goods\GoodsCommentLists;
use app\adminapi\logic\goods\GoodsCommentLogic;
use app\adminapi\validate\goods\GoodsCommentValidate;

class GoodsCommentController extends BaseAdminController
{
    /**
     * @notes 查看商品评价列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/12 5:00 下午
     */
    public function lists()
    {
        return $this->dataLists(new GoodsCommentLists());
    }

    /**
     * @notes 商家回复
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/12 5:26 下午
     */
    public function reply()
    {
        $params = (new GoodsCommentValidate())->post()->goCheck('reply');
        (new GoodsCommentLogic())->reply($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 删除评价
     * @return \think\response\Json
     * @author ljj
     * @date 2021/9/9 11:20 上午
     */
    public function del()
    {
        $params = (new GoodsCommentValidate())->post()->goCheck('del');
        (new GoodsCommentLogic())->del($params);
        return $this->success('删除成功',[],1,1);
    }

    /**
     * @notes 修改评价审核状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/9/9 2:41 下午
     */
    public function status()
    {
        $params = (new GoodsCommentValidate())->post()->goCheck('status');
        (new GoodsCommentLogic())->status($params);
        return $this->success('操作成功',[],1,1);
    }
}