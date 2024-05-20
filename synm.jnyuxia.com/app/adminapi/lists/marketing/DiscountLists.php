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
namespace app\adminapi\lists\marketing;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\GoodsEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\Goods;

/**
 * 折扣列表
 * Class DiscountLists
 * @package app\adminapi\lists\marketing
 */
class DiscountLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return array
     * @author cjhao
     * @date 2022/5/5 12:09
     */
    public function setSearch(): array
    {
        return array_intersect(array_keys($this->params), ['name', 'status', 'goods_type', 'discount']);

    }

    /**
     * @notes 折扣商品列表
     * @return array
     * @author cjhao
     * @date 2022/5/5 12:09
     */
    public function lists(): array
    {
        $lists = (new Goods())->duokaiWithoutGlobalScope()->aliasSid('G')->alias('G')
            ->leftJoin('discount_goods dg', 'G.id = dg.goods_id')
            ->withSearch($this->setSearch(), $this->params)
            ->field('G.id,image,name,type,status,spec_type,min_price,max_price,total_stock,sales_num+virtual_click_num as sales_num,is_discount')
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id desc')
            ->select()->toArray();

        foreach ($lists as $key => $goods) {
            $lists[$key]['status_desc'] = GoodsEnum::getStatusDesc($goods['status']);
            $lists[$key]['price'] = $goods['min_price'];
            if ($goods['min_price'] != $goods['max_price']) {
                $lists[$key]['price'] = $goods['min_price'] . '~' . $goods['max_price'];
            }
            if (null == $goods['is_discount']) {
                $lists[$key]['is_discount'] = 0;
            }
        }

        return $lists;

    }

    /**
     * @notes 折扣商品统计
     * @return int
     * @author cjhao
     * @date 2022/5/5 12:09
     */
    public function count(): int
    {
        return (new Goods())->duokaiWithoutGlobalScope()->aliasSid('G')->alias('G')
            ->leftJoin('discount_goods dg', 'G.id = dg.goods_id')
            ->withSearch($this->setSearch(), $this->params)
            ->count();
    }
}