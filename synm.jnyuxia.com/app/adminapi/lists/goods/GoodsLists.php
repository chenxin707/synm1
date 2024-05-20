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

use app\adminapi\{
    lists\BaseAdminDataLists,
    logic\goods\GoodsCategoryLogic,
};
use app\common\{enum\GoodsEnum,
    lists\ListsExcelInterface,
    lists\ListsExtendInterface,
    logic\GoodsActivityLogic,
    model\Goods};


/**
 * 商品列表接口
 * Class GoodsLists
 * @package app\adminapi\lists\goods
 */
class GoodsLists extends BaseAdminDataLists implements ListsExtendInterface,ListsExcelInterface
{

    /**
     * @notes 搜索条件
     * @return array
     * @author cjhao
     * @date 2021/7/22 10:51
     */
    public function setSearch(): array
    {
        $this->params['type'] = $this->params['type_lists'] ?? 0;
        return array_intersect(array_keys($this->params),['type','keyword','category_id','supplier_id','goods_type','activity_type']);
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
            ->field('count(id) as all_count,
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
            ->with(['goods_category_index'])
            ->limit($this->limitOffset, $this->limitLength)
            ->field('id,name,code,image,min_price,max_price,total_stock,virtual_sales_num+sales_num as sales_num,virtual_click_num+click_num as click_num,status,sort,spec_type,create_time')
            ->order('id desc')
            ->select()
            ->toArray();
        //获取参与活动的商品
        $goodsIds = array_column($list,'id');
        $goodsActivityList = GoodsActivityLogic::activityInfo($goodsIds);

        //获取全部商品分类
        $category_list = GoodsCategoryLogic::getCategoryNameLists();

        foreach ($list as $goodsKey => $goodsVal) {
            $list[$goodsKey]['status_desc'] = GoodsEnum::getStatusDesc($goodsVal['status']);
            //商品价格
            $list[$goodsKey]['price'] = '¥' . $goodsVal['min_price'];
            if ($goodsVal['min_price'] != $goodsVal['max_price']) {
                $list[$goodsKey]['price'] = '¥' . $goodsVal['min_price'] . '~' . '¥' . $goodsVal['max_price'];
            }
            //商品分类
            $category_name = array_map(function ($category_index) use ($category_list) {
                return $category_list[$category_index['category_id']]['category_name'] ?? '';
            }, $goodsVal['goods_category_index']);

            $list[$goodsKey]['category_name'] = $category_name;
            unset($list[$goodsKey]['goods_category_index']);
            //商品参加了哪些活动
            $list[$goodsKey]['goods_activity'] = array_keys($goodsActivityList[$goodsVal['id']] ?? []);

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

    /**
     * @notes 设置excel表名
     * @return string
     * @author cjhao
     * @date 2021/9/23 9:52
     */
    public function setFileName(): string
    {
        return '商品列表';
    }

    /**
     * @notes 设置导出字段
     * @return array
     * @author cjhao
     * @date 2021/9/23 9:59
     */
    public function setExcelFields(): array
    {
        return [
            'code'          => '商品编号',
            'name'          => '商品名称',
            'price'         => '价格',
            'total_stock'   => '库存',
            'sales_num'     => '销量',
            'click_num'     => '浏览量',
            'status_desc'   => '销售状态',
            'sort'          => '排序',
            'create_time'   => '创建时间',
        ];
    }


}