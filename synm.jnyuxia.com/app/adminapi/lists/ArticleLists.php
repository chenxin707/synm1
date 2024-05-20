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

namespace app\adminapi\lists;

use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\Article;
use app\common\model\ArticleCategory;

class ArticleLists extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{
    /**
     * @notes 设置导出字段
     * @return string[]
     * @author Tab
     * @date 2021/7/30 15:37
     */
    public function setExcelFields(): array
    {
        return [
            'title' => '标题',
            'cid_desc' => '分类',
            'is_show_desc' => '文章状态',
            'visit' => '浏览量',
            'sort' => '排序',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author Tab
     * @date 2021/7/30 15:37
     */
    public function setFileName(): string
    {
        return '文章帮助表';
    }

    /**
     * @notes 设置搜索
     * @return \string[][]
     * @author Tab
     * @date 2021/7/14 9:48
     */
    public function setSearch(): array
    {
        return [
            '=' => ['cid'],
            '%like%' => ['title']
        ];
    }

    /**
     * @notes 文章/帮助列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/14 9:48
     */
    public function lists(): array
    {
        $lists = Article::field('id,title,image,cid,is_show,is_show as is_show_desc,visit,sort,create_time')
            ->where($this->searchWhere)
            ->order('sort desc,id desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();
        $categoryList = ArticleCategory::column('name','id');
        foreach ($lists as $key => $list){
            $lists[$key]['cid_desc'] = $categoryList[$list['cid']] ?? '';
        }
        return $lists;
    }

    /**
     * @notes 文章/帮助总记录数
     * @return int
     * @author Tab
     * @date 2021/7/14 9:48
     */
    public function count(): int
    {
        return Article::where($this->searchWhere)->count();
    }
}