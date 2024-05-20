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
use app\common\enum\DefaultEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Article;
use app\common\service\FileService;

class ArticleLogic extends BaseLogic
{
    /**
     * @notes 添加文章/帮助
     * @param $params
     * @author Tab
     * @date 2021/7/13 15:59
     */
    public static function add($params)
    {
        $image = isset($params['image']) && !empty($params['image']) ? FileService::setFileUrl($params['image']) : '';
        $data = [
            'title' => $params['title'],
            'cid' => $params['cid'],
            'synopsis' => $params['synopsis'] ?? '',
            'image' => $image,
            'content' => $params['content'] ?? '',
            'sort' => $params['sort'] ?? DefaultEnum::SORT,
            'is_show' => $params['is_show'] ?? YesNoEnum::YES,
            'create_time' => time(),
            'type' => $params['type'] ?? ArticleEnum::ARTICLE,
        ];

        Article::duokaiCreate($data);
    }

    /**
     * @notes 查看文章/帮助详情
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/7/13 16:53
     */
    public static function detail($params)
    {
        return Article::field('title,cid,synopsis,image,content,sort,is_show')->findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes 编辑文章/帮助
     * @param $params
     * @author Tab
     * @date 2021/7/14 9:21
     */
    public static function edit($params)
    {
        $image = isset($params['image']) && !empty($params['image']) ? FileService::setFileUrl($params['image']) : '';

        $data = [
            'id' => $params['id'],
            'title' => $params['title'],
            'cid' => $params['cid'],
            'synopsis' => $params['synopsis'] ?? '',
            'image' => $image,
            'content' => $params['content'] ?? '',
            'sort' => $params['sort'] ?? DefaultEnum::SORT,
            'is_show' => $params['is_show'] ?? YesNoEnum::YES,
        ];

        Article::duokaiUpdate($data, [], [], '', false);
    }

    /**
     * @notes 删除文章/帮助
     * @param $params
     * @author Tab
     * @date 2021/7/14 9:23
     */
    public static function delete($params)
    {
        Article::duokaiDestroy($params['id']);
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
        $article = Article::find($params['id']);
        $article->is_show = $article->getData('is_show') ? 0: 1;
        $article->duokaiSave();
    }
}