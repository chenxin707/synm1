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
namespace app\businessapi\logic;
use app\common\enum\GoodsEnum;
use app\common\logic\BaseLogic;
use app\common\logic\GoodsActivityLogic;
use app\common\model\Goods;
use app\common\model\GoodsItem;

/**
 * 商品逻辑层
 * Class GoodsLogic
 * @package app\businessapi\logic
 */
class GoodsLogic extends BaseLogic
{


    public function detail(int $id)
    {


        $goods = Goods::with(['spec_value.spec_list','spec_value_list'])
            ->field('id,name,type,code,image,video,video_cover,total_stock,click_num,virtual_sales_num+sales_num as sales_num,unit_id,spec_type,content,poster,virtual_click_num,express_type,express_money,is_express,is_selffetch,min_price,max_price')
            ->append(['goods_image'])
            ->find($id);

        if($goods->is_express){
            $goods->delivery_type = '快递配送 ';
        }

        if($goods->is_selffetch){
            $goods->delivery_type && $goods->delivery_type.='、';
            $goods->delivery_type .= '上门自提';
        }
        if(GoodsEnum::GOODS_VIRTUAL == $goods->type){
            $goods->delivery_type = '虚拟发货';
        }

        $goods->sell_price      = $goods->spec_value_list[0]->sell_price;
        $goods->lineation_price = $goods->spec_value_list[0]->lineation_price;

        $goods->price = '¥' . $goods->min_price;
        if ($goods->min_price != $goods->max_price) {
            $goods->price = '¥' . $goods->min_price . '~' . '¥' . $goods->max_price;
        }

        //获取参与活动的商品
        $goodsActivityList = GoodsActivityLogic::activityInfo([$goods['id']]);
        $goods->click_num += $goods->virtual_click_num;
        $goods->unit_name = '';
        if($goods->unit_id){
            $goods->unit_name = $goods->unit->name;
        }
        $goods->hidden(['unit_id','unit']);
        //商品参加了哪些活动
        $goods->goods_activity = array_keys($goodsActivityList[$goods['id']] ?? []);

        return $goods->toArray();


    }


    /**
     * @notes 修改商品状态
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2023/2/16 10:34
     */
    public function status(int $id)
    {
        $goods = Goods::find($id);
        $goods->status = $goods->status ? 0 : 1;
        $goods->save();

    }


    /**
     * @notes 修改商品价格
     * @param $params
     * @return bool
     * @throws \Exception
     * @author cjhao
     * @date 2023/2/16 10:52
     */
    public function edit($params)
    {
        try{
            foreach ($params['spec_value_list'] as $specValue){
                GoodsItem::where(['id'=>$specValue['id']])->update($specValue);
            }
            $totalStock = array_sum(array_column($params['spec_value_list'],'stock'));
            $spellPrice = array_column($params['spec_value_list'],'sell_price');
            $minPrice = min($spellPrice);
            $maxPrice = max($spellPrice);

            Goods::where(['id'=>$params['id']])->update(['min_price'=>$minPrice,'max_price'=>$maxPrice,'total_stock'=>$totalStock]);

            return true;
        }catch (\Exception $e) {
            return $e->getMessage();
        }


    }

}