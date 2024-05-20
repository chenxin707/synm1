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

namespace app\adminapi\logic;

use app\common\enum\ArticleEnum;
use app\common\logic\BaseLogic;
use app\common\model\ArticleCategory;

class ArticleCategoryLogic extends BaseLogic
{
    /**
     * @notes 添加文章分类
     * @param $params
     * @author Tab
     * @date 2021/7/13 14:23
     */
    public static function add($params)
    {
        $params['create_time'] = time();
        ArticleCategory::duokaiCreate($params);
    }

    /**
     * @notes 查看文章/帮助详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/13 14:24
     */
    public static function detail($params)
    {
        return ArticleCategory::field('name,is_show,sort')->find($params['id'])->toArray();
    }

    /**
     * @notes 编辑文章分类
     * @param $params
     * @author Tab
     * @date 2021/7/13 14:25
     */
    public static function edit($params)
    {
        ArticleCategory::duokaiUpdate($params, [], [], '', false);
    }

    /**
     * @notes 删除文章分类
     * @param $params
     * @author Tab
     * @date 2021/7/13 14:25
     */
    public static function delete($params)
    {
        ArticleCategory::duokaiDestroy($params['id']);
    }

    /**
     * @notes 修改是否显示状态
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/14 11:36
     */
    public static function isShow($params)
    {
        $articleCategory = ArticleCategory::find($params['id']);
        $articleCategory->is_show = $articleCategory->getData('is_show') ? 0: 1;
        $articleCategory->save();
    }
}