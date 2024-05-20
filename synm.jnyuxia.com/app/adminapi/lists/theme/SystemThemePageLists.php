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
namespace app\adminapi\lists\theme;
use app\adminapi\lists\BaseAdminDataLists;
use app\common\
{
    model\SystemTheme,
    enum\ThemePageEnum,
    lists\ListsSearchInterface,
};

/**
 * 系统主题列表页
 * Class SystemThemePageLists
 * @package app\adminapi\lists\theme
 */
class SystemThemePageLists extends BaseAdminDataLists implements ListsSearchInterface
{

    /**
     * @notes 搜索条件
     * @return array
     * @author cjhao
     * @date 2021/8/5 10:24
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['name']
        ];
    }


    /**
     * @notes 页面列表
     * @return array
     * @author cjhao
     * @date 2021/8/5 10:24
     */
    public function lists(): array
    {
        //todo 现在系统主题只有主页，暂时用模型关联
        $lists = SystemTheme::with(['theme_page' => function($query){
            $query->where(['type'=>ThemePageEnum::TYPE_HOME])->withField(['id,theme_id,name,content,common']);
        }])->field('id,name,image,create_time')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
//            ->order(['id'=>'desc'])
            ->select()
            ->toArray();
        return $lists;
    }


    /**
     * @notes 查看页面数量
     * @return int
     * @author cjhao
     * @date 2021/8/5 10:25
     */
    public function count(): int
    {
        return SystemTheme::where($this->searchWhere)->count();
    }

}