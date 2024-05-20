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

namespace app\adminapi\logic\distribution;

use app\common\enum\DistributionGoodsEnum;
use app\common\logic\BaseLogic;
use app\common\model\DistributionGoods;
use app\common\model\DistributionLevel;
use app\common\model\Goods;
use app\common\model\GoodsItem;
use app\common\service\FileService;
use think\facade\Db;

/**
 * 分销商品逻辑层
 * Class DistributionGoodsLogic
 * @package app\adminapi\logic\distribution
 */
class DistributionGoodsLogic extends BaseLogic
{
    /**
     * @notes 设置佣金
     * @param $params
     * @author Tab
     * @date 2021/7/23 16:48
     */
    public static function set($params)
    {
        Db::startTrans();
        try {
            switch($params['rule']) {
                // 根据分销会员等级比例分佣
                case DistributionGoodsEnum::RULE_LEVEL:
                    self::setRuleOne($params);
                    break;

                // 单独设置
                case DistributionGoodsEnum::RULE_CUSTOMIZE:
                    self::setRuleTwo($params);
                    break;
            }

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 设置佣金 - 根据分销会员等级比例分佣
     * @param $params
     * @author Tab
     * @date 2021/7/23 16:54
     */
    public static function setRuleOne($params)
    {
        // 删除旧数据
        $deleteIds = DistributionGoods::where('goods_id', $params['goods_id'])->column('id');
        DistributionGoods::duokaiDestroy($deleteIds);

        // 生成新数据
        $data = [
            'goods_id' => $params['goods_id'],
            'is_distribution' => $params['is_distribution'],
            'rule' => $params['rule'],
        ];
        DistributionGoods::duokaiCreate($data);
    }

    /**
     * @notes 设置佣金 - 单独自定义
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/7/23 17:25
     */
    public static function setRuleTwo($params)
    {
        // 删除旧数据
        $deleteIds = DistributionGoods::where('goods_id', $params['goods_id'])->column('id');
        DistributionGoods::duokaiDestroy($deleteIds);

        // 生成新数据
        $data= [];
        foreach($params['ratio_data'] as $item) {
            $item['goods_id'] = $params['goods_id'];
            $item['is_distribution'] = $params['is_distribution'];
            $item['rule'] = $params['rule'];
            $data[] = $item;
        }
        (new DistributionGoods())->duokaiSaveAll($data);
    }

    /**
     * @notes 参与/不参与分销
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/7/23 18:01
     */
    public static function join($params)
    {
        Db::startTrans();
        try{
            $existedIds = DistributionGoods::distinct(true)->column('goods_id');
            $updateIds = array_intersect($params['ids'], $existedIds);
            $insertIds = array_diff($params['ids'], $existedIds);

            // 有分销数据，直接修改
            DistributionGoods::where('goods_id', 'in', $updateIds)->update(['is_distribution' => $params['is_distribution']]);

            // 无分销数据，新增
            $insertData = [];
            foreach($insertIds as $id) {
                $item['goods_id'] = $id;
                $item['is_distribution'] = $params['is_distribution'];
                $item['rule'] = DistributionGoodsEnum::RULE_LEVEL;
                $insertData[] = $item;
            }

            (new DistributionGoods())->duokaiSaveAll($insertData);

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 查看分销商品详情
     * @param $params
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/26 9:30
     */
    public static function detail($params)
    {
        $field = 'g.id,g.code,g.name,g.image,';
        $field .= 'dg.goods_id,dg.item_id,dg.level_id,dg.self_ratio,dg.first_ratio,dg.second_ratio,dg.is_distribution,dg.rule,';
        $field .= 'gi.spec_value_str,gi.sell_price,';
        $field .= 'dl.id as level_id';

        $distribution = DistributionGoods::withoutGlobalScope()
            ->aliasSid('dg')
            ->alias('dg')
            ->leftJoin('goods g', 'g.id = dg.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = dg.item_id')
            ->leftJoin('distribution_level dl', 'dl.id = dg.level_id')
            ->field($field)
            ->where('dg.goods_id', $params['id'])
            ->select();

        // 商品无分销数据
        if($distribution->isEmpty()) {
            $goods = Goods::field('id,code,image,name')->findOrEmpty($params['id'])->toArray();
            $goods['is_distribution'] = 0;
            $goods['rule'] = DistributionGoodsEnum::RULE_LEVEL;
            $goods['ratio_data'] = self::defaultRatio($params);
            return $goods;
        }
        $distribution = $distribution->toArray();
        // 商品根据分销会员等级分佣
        if($distribution[0]['rule'] == DistributionGoodsEnum::RULE_LEVEL) {
            unset($distribution[0]['item_id']);
            unset($distribution[0]['level_id']);
            unset($distribution[0]['self_ratio']);
            unset($distribution[0]['first_ratio']);
            unset($distribution[0]['second_ratio']);
            unset($distribution[0]['spec_value_str']);
            unset($distribution[0]['sell_price']);
            $distribution[0]['image'] = FileService::getFileUrl($distribution[0]['image']);
            $distribution[0]['ratio_data'] = self::defaultRatio($params);
            return $distribution[0];
        }
        // 商品单独设置分佣比例
        if($distribution[0]['rule'] == DistributionGoodsEnum::RULE_CUSTOMIZE) {
            $data['goods_id'] = $distribution[0]['goods_id'];
            $data['code'] = $distribution[0]['code'];
            $data['name'] = $distribution[0]['name'];
            $data['image'] = FileService::getFileUrl($distribution[0]['image']);
            $data['is_distribution'] = $distribution[0]['is_distribution'];
            $data['rule'] = $distribution[0]['rule'];
            $distributionLevelLists = DistributionLevel::field('id,name as name_desc,is_default')->order('weights', 'asc')->select()->toArray();
            foreach($distributionLevelLists as &$distributionLevel) {
                // 过滤当前分销会员等级下的分佣比例
                $distributionLevel['items'] = array_filter($distribution, function($value) use ($distributionLevel) {
                    return $distributionLevel['id'] == $value['level_id'];
                });
                // 重置索引
                $distributionLevel['items'] = array_values($distributionLevel['items']);
            }
            $data['ratio_data'] = $distributionLevelLists;
            return $data;
        }
    }

    /**
     * @notes 默认分销会员等级分销比例
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/9 17:02
     */
    public static function defaultRatio($params)
    {
        // 获取商品所有规格数据
        $items = GoodsItem::where('goods_id', $params['id'])->column('id,spec_value_str,sell_price');
        $distributionLevelLists = DistributionLevel::field('id,name as name_desc,is_default,self_ratio,first_ratio,second_ratio')->order('weights', 'asc')->select()->toArray();
        foreach($distributionLevelLists as &$distributionLevel) {
            $tempItems = $items;
            foreach($tempItems as &$subItem) {
                $subItem['item_id'] = $subItem['id'];
                $subItem['level_id'] = $distributionLevel['id'];
                $subItem['self_ratio'] = $distributionLevel['self_ratio'];
                $subItem['first_ratio'] = $distributionLevel['first_ratio'];
                $subItem['second_ratio'] = $distributionLevel['second_ratio'];
            }
            $distributionLevel['items'] = $tempItems;
        }
        return $distributionLevelLists;
    }
}