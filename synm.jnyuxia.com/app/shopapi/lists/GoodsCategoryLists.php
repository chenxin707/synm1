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
namespace app\shopapi\lists;
use app\common\model\GoodsCategory;

/**
 * 商品分类列表
 * Class GoodsCategoryLists
 * @package app\shopapi\lists
 */
class GoodsCategoryLists extends BaseShopDataLists{

    public function setSearch(): array
    {
        $level = $this->params['level'] ?? 3;//默认三级
        $where[] = ['is_show','=',1];
        
        //返回分类级别
        if(1 == $level){
            $where[] = ['level','=',1];
        }
        if(2 == $level){
            $where[] = ['level','<',3];
        }
        return $where;
    }
    /**
     * @notes 商品分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/12 11:57
     */
    public function lists(): array
    {
        $lists = GoodsCategory::where($this->setSearch())->field('id,pid,name,level,image')
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select()
            ->toArray();

        $lists = linear_to_tree($lists,'sons');
        return $lists;
    }


    /**
     * @notes 商品分类合集
     * @return int
     * @author cjhao
     * @date 2021/8/12 11:57
     */
    public function count(): int
    {
        return GoodsCategory::count();
    }
}