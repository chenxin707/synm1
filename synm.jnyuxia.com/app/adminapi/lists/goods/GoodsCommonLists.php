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
use app\common\{enum\ActivityEnum,
    enum\GoodsEnum,
    enum\YesNoEnum,
    model\DiscountGoods,
    model\DistributionGoods,
    model\Goods,
    model\GoodsItem,
    lists\ListsSearchInterface};

/**
 * 商品公共列表
 * Class GoodsCommonLists
 * @package app\adminapi\lists\goods
 */
class GoodsCommonLists  extends BaseAdminDataLists
{
    function where(): array
    {
        $where[] = ['status','=',1];
        if (isset($this->params['name']) && $this->params['name'] != '') {
            $where[] = ['name','like','%'.$this->params['name'].'%'];
        }
        if (isset($this->params['is_distribution']) && $this->params['is_distribution'] != '') {
            // 获取已开启分销的商品id
            $joinIds = DistributionGoods::where('is_distribution', YesNoEnum::YES)->column('goods_id');
            if ($this->params['is_distribution'] == 1) {
                $where[] = ['id','in',$joinIds];
            }
            if ($this->params['is_distribution'] == 0) {
                $where[] = ['id','not in',$joinIds];
            }
        }
        if (isset($this->params['is_discount']) && $this->params['is_discount'] != '') {
            // 获取已开启会员折扣的商品id
            $joinIds = DiscountGoods::where('is_discount', YesNoEnum::YES)->column('goods_id');
            if ($this->params['is_discount'] == 1) {
                $where[] = ['id','in',$joinIds];
            }
            if ($this->params['is_discount'] == 0) {
                $where[] = ['id','not in',$joinIds];
            }
        }

        return $where;
    }


    public function lists(): array
    {
        $lists = Goods::where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->field('id,code,name,image,total_stock,spec_type,virtual_sales_num+sales_num as sales_num,max_price,max_lineation_price,min_price as sell_price,min_lineation_price as lineation_price')
            ->append(['is_distribution','is_discount'])
            ->select()
            ->toArray();
        $lists = array_column($lists,null,'id');
        //显示规格信息
        $isSpec = $this->params['is_spec'] ?? 0;
        foreach ($lists as $goodskey => $goodsVal){
            $isSpec && $lists[$goodskey]['item'] = [];
            $lists[$goodskey]['price'] = '¥'.$goodsVal['sell_price'].'~'.'¥'.$goodsVal['max_price'];
            if($goodsVal['sell_price'] !== $goodsVal['max_price']){
                $lists[$goodskey]['price'] = '¥'.$goodsVal['sell_price'];
            }
        }

        if($isSpec){

            $goodsIds = array_keys($lists);
            //显示商品规格
            if($goodsIds){
                $goodsItemList = GoodsItem::where(['goods_id'=>$goodsIds])
                    ->field('id,image,goods_id,spec_value_ids,spec_value_str,stock,sell_price')
                    ->select();

                foreach ($goodsItemList as $item){
//                    if(isset($lists[$item['goods_id']]) && GoodsEnum::SEPC_TYPE_MORE == $lists[$item['goods_id']]['spec_type']){
                        $lists[$item['goods_id']]['item'][] = $item;
//                    }
                }
            }
        }


        return array_values($lists);
    }

    public function count(): int
    {
        $count = Goods::where($this->where())->count();
        return $count;
    }

}