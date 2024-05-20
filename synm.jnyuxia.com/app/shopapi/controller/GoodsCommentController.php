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

namespace app\shopapi\controller;


use app\shopapi\lists\CommentGoodsLists;
use app\shopapi\lists\GoodsCommentLists;
use app\shopapi\logic\GoodsCommentLogic;
use app\shopapi\validate\GoodsCommentValidate;

class GoodsCommentController extends BaseShopController
{
    /**
     * @notes 添加商品评价
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/6 8:15 下午
     */
    public function add()
    {
        $params = (new GoodsCommentValidate())->post()->goCheck('add', ['user_id' => $this->userId]);
        $result = (new GoodsCommentLogic())->add($params);
        if (false === $result) {
            return $this->fail(GoodsCommentLogic::getError());
        }
        return $this->success('添加成功',[],1,1);
    }

    /**
     * @notes 查看商品评价分类
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/6 9:04 下午
     */
    public function commentCategory()
    {
        $params = (new GoodsCommentValidate())->goCheck('CommentCategory');
        $result = (new GoodsCommentLogic())->commentCategory($params);
        return $this->success('',$result);
    }

    /**
     * @notes 查看评论列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/9 10:25 上午
     */
    public function lists()
    {
        return $this->dataLists(new GoodsCommentLists());
    }

    /**
     * @notes 评价商品列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/9 2:45 下午
     */
    public function commentGoodsLists()
    {
        return $this->dataLists(new CommentGoodsLists());
    }

    /**
     * @notes 查看评价商品信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/9 3:12 下午
     */
    public function commentGoodsInfo()
    {
        $params = (new GoodsCommentValidate())->goCheck('CommentGoodsInfo');
        $result = (new GoodsCommentLogic())->commentGoodsInfo($params);
        return $this->success('',$result);
    }
}