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
namespace app\businessapi\lists;

use app\common\{enum\GoodsEnum,
    lists\ListsExtendInterface,
    model\Goods};


/**
 * 商品列表接口
 * Class GoodsLists
 * @package app\adminapi\lists\goods
 */
class GoodsLists extends BaseBusinesseDataLists implements ListsExtendInterface
{

    /**
     * @notes 搜索条件
     * @return array
     * @author cjhao
     * @date 2021/7/22 10:51
     */
    public function setSearch(): array
    {

        return array_intersect(array_keys($this->params),['keyword','type']);
    }

    /**
     * @notes 统计信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/7/22 10:51
     */
    public function extend(): array
    {
        $statistics = (new Goods())
            ->withSearch(array_diff(['type'],$this->setSearch()), $this->params)
            ->field('
                    IFNULL(sum(if(status = 1,1,0)),0) as sales_count,
                    IFNULL(sum(if(status = 1 and stock_warning > 0 and total_stock > 0 and stock_warning > total_stock,1,0)),0) as warning_count,
                    IFNULL(sum(if(status = 1 and total_stock = 0,1,0)),0) as sellout_count,
                    IFNULL(sum(if(status = 0,1,0)),0) as storage_count')
            ->select()->toArray();
        
        return array_shift($statistics);
    }


    /**
     * @notes 商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/7/21 18:31
     */
    public function lists(): array
    {

        $list = (new Goods())
            ->withSearch($this->setSearch(), $this->params)
            ->limit($this->limitOffset, $this->limitLength)
            ->field('id,name,code,image,min_price,max_price,total_stock,virtual_sales_num+sales_num as sales_num,virtual_click_num+click_num as click_num,status,sort,spec_type,create_time')
            ->order('id desc')
            ->select()
            ->toArray();


        foreach ($list as $goodsKey => $goodsVal) {
            $list[$goodsKey]['status_desc'] = GoodsEnum::getStatusDesc($goodsVal['status']);
            //商品价格
            $list[$goodsKey]['price'] = '¥' . $goodsVal['min_price'];
            if ($goodsVal['min_price'] != $goodsVal['max_price']) {
                $list[$goodsKey]['price'] = '¥' . $goodsVal['min_price'] . '~' . '¥' . $goodsVal['max_price'];
            }

        }
        return $list;

    }


    /**
     * @notes 商品总数
     * @return int
     * @author cjhao
     * @date 2021/7/21 18:32
     */
    public function count(): int
    {
        return (new Goods())
            ->withSearch($this->setSearch(), $this->params)
            ->count();
    }



}