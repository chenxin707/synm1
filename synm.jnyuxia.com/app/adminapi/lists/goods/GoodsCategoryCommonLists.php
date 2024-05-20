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
use app\common\{
    model\GoodsCategory,
    model\GoodsCategoryIndex,
    lists\ListsSearchInterface,
};

/**
 * 商品分类公共列表
 * Class GoodsCommonLists
 * @package app\adminapi\lists\goods
 */
class GoodsCategoryCommonLists  extends BaseAdminDataLists implements ListsSearchInterface
{
    function setSearch(): array
    {
        return [
            '%like%'    => ['name'],
            '='         => ['level'],
        ];
    }


    public function lists(): array
    {

        $lists = GoodsCategory::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'asc')
            ->column('id,name,image,level,0 as num','id');

        $categoryIds = array_keys($lists);

        if($categoryIds){
            $categoryCountLists = GoodsCategoryIndex::where(['category_id'=>$categoryIds])
                                ->group('category_id')
                                ->field('count(goods_id) as num,category_id')
                                ->select()->toArray();

            foreach ($categoryCountLists as $countList){
                if(isset($lists[$countList['category_id']])){
                    $lists[$countList['category_id']]['num'] = $countList['num'];
                }
            }

        }
        return array_values($lists);
    }

    public function count(): int
    {
        $count = GoodsCategory::where($this->searchWhere)
            ->count();
        return $count;
    }

}