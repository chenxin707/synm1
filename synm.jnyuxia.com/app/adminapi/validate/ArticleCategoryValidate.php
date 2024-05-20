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

namespace app\adminapi\validate;

use app\common\validate\BaseValidate;
use app\common\model\ArticleCategory;
use app\common\model\Article;

class ArticleCategoryValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkArticleCategory|checkDelete',
        'name' => 'require|checkUsed',
        'is_show' => 'require|in:0,1',
        'sort' => 'require|integer|egt:0',
    ];

    protected $message = [
        'id.require' => '分类id不能为空',
        'name.require' => '请输入分类名称',
        'is_show.require' => '请选择是否显示',
        'is_show.in' => '是否显示状态有误',
        'sort.require' => '请输入排序值',
        'sort.integer' => '排序值须为整型',
        'sort.egt' => '排序值须大于或等于0',
    ];

    public function sceneAdd()
    {
        return $this->remove('id', 'require|checkArticleCategory');
    }

    public function sceneDetail()
    {
        return $this->only(['id'])
            ->remove('id', 'checkDelete');
    }

    public function sceneEdit()
    {
        return $this->remove('id', 'checkDelete');
    }

    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 显示或隐藏场景
     * @return ArticleValidate
     * @author Tab
     * @date 2021/7/22 17:07
     */
    public function sceneShow()
    {
        return $this->only(['id'])->remove('id', 'checkDelete');
    }
    /**
     * @notes 校验分类名称是否可用
     * @param $value
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/13 14:28
     */
    public function checkUsed($value, $rule, $data)
    {
        $where = [
            ['name', '=', $value]
        ];
        if(isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $articleCategory = ArticleCategory::where($where)->select()->toArray();
        if ($articleCategory) {
            return '分类名称已存在';
        }
        return true;
    }

    /**
     * @notes 校验文章分类是否存在
     * @param $value
     * @return bool|string
     * @author Tab
     * @date 2021/7/13 14:28
     */
    public function checkArticleCategory($value)
    {
        $articleCategory = ArticleCategory::findOrEmpty($value);
        if ($articleCategory->isEmpty()) {
            return '文章分类不存在';
        }
        return true;
    }

    /**
     * @notes 校验文章分类是否可删除
     * @param $value
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/13 14:28
     */
    public function checkDelete($value)
    {
        $articles = Article::where('cid', $value)->select()->toArray();
        if ($articles) {
            return '该分类下有文章,不允许删除';
        }
        return true;
    }
}