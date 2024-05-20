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
use app\adminapi\logic\goods\GoodsCommentAssistantLogic;
use app\adminapi\validate\goods\GoodsCommentAssistantValidate;
use app\common\service\JsonService;

/**
 * 商品评价助手
 * Class GoodsCommentController
 * @package app\adminapi\controller\goods
 */
class GoodsCommentAssistantController extends BaseAdminController
{
    /**
     * @notes 商品列表
     * @return \think\response\Json
     * @author Tab
     * @datetime 2022/1/17 17:36
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 添加虚拟评价
     * @return \think\response\Json
     * @author Tab
     * @datetime 2022/1/18 9:46
     */
    public function add()
    {
        $params = (new GoodsCommentAssistantValidate())->post()->goCheck();
        $result = GoodsCommentAssistantLogic::add($params);
        if ($result) {
            return JsonService::success('添加成功');
        }
        return JsonService::fail(GoodsCommentAssistantLogic::getError());
    }
}
