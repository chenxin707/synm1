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

use app\common\model\Article;
use app\common\model\ArticleCategory;
use app\common\validate\BaseValidate;

class ArticleValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require',
        'cid' => 'require|checkCid',
        'is_show' => 'in:0,1',
    ];

    protected $message = [
        'title.require' => '标题不能为空',
        'cid.require' => '请选择分类',
        'is_show.in' => '是否显示类型错误',
    ];

    /**
     * @notes 添加文章/帮助场景
     * @return ArticleValidate
     * @author Tab
     * @date 2021/7/22 17:06
     */
    public function sceneAdd()
    {
        return $this->remove('id', 'require');
    }

    /**
     * @notes 获取文章/帮助详情场景
     * @return ArticleValidate
     * @author Tab
     * @date 2021/7/22 17:06
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 删除文章/帮助场景
     * @return ArticleValidate
     * @author Tab
     * @date 2021/7/22 17:06
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 文章显示或隐藏场景
     * @return ArticleValidate
     * @author Tab
     * @date 2021/7/22 17:07
     */
    public function sceneShow()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验文章/帮助分类
     * @param $value
     * @return bool|string
     * @author Tab
     * @date 2021/7/22 17:08
     */
    public function checkCid($value)
    {
        $articleCategory = ArticleCategory::findOrEmpty($value);
        if($articleCategory->isEmpty()) {
            return '分类不存在';
        }
        return true;
    }
}