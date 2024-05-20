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

use app\shopapi\lists\ArticleCategoryLists;

use app\shopapi\logic\ArticleLogic;

class ArticleController extends BaseShopController
{
    /**
     * 无需登录即可访问的方法
     * @var array|string[]
     */
    public array $notNeedLogin = ['lists', 'articleCategoryLists', 'detail'];

    /**
     * @notes 商城资讯/帮助中心列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/14 14:33
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 商城资讯/帮助中心分类列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/14 14:57
     */
    public function articleCategoryLists()
    {
        return $this->dataLists(new ArticleCategoryLists());
    }

    /**
     * @notes 获取商城资讯/帮助中心详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/14 15:06
     */
    public function detail()
    {
        $params = $this->request->get();
        $result = ArticleLogic::detail($params);
        return $this->data($result);
    }
}