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
namespace app\kefuapi\lists;

use app\common\lists\ListsSearchInterface;
use app\common\model\KefuLang;

/**
 * 快捷回复列表
 * Class KefuLangLists
 * @package app\kefuapi\lists
 */
class KefuLangLists extends BaseKefuDataLists implements ListsSearchInterface
{

    /**
     * @notes 设置搜索条件
     * @return array
     * @author 段誉
     * @date 2022/3/14 14:53
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['title']
        ];
    }

    /**
     * @notes 获取话术列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/14 14:53
     */
    public function lists(): array
    {

        $lists = KefuLang::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('sort')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 记录数量
     * @return int
     * @author 段誉
     * @date 2022/3/14 14:53
     */
    public function count(): int
    {
        return KefuLang::where($this->searchWhere)->count();
    }

}