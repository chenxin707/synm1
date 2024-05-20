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

namespace app\adminapi\lists\goods;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\GoodsSupplierCategory;

class GoodsSupplierCategoryLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author ljj
     * @date 2021/7/16 5:42
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['name']
        ];
    }

    /**
     * @notes 查看供应商分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/16 5:47
     */
    public function lists(): array
    {
        $lists = GoodsSupplierCategory::field('id,name,sort,create_time')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 查看供应商分类总数
     * @return int
     * @author ljj
     * @date 2021/7/16 5:48
     */
    public function count(): int
    {
        return GoodsSupplierCategory::where($this->searchWhere)->count();
    }
}