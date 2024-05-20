<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likeshop_gitee/likeshop
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\shopapi\logic\Order;

use app\common\enum\DeliveryEnum;
use app\common\enum\FreightEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Freight;
use app\common\service\ConfigService;

/**
 * 运费逻辑
 * Class FreightLogic
 * @package app\api\logic
 */
class FreightLogic extends BaseLogic
{
    /**
     * @notes 计算运费
     * @param $goods
     * @param $userAddress
     * @return float|int|mixed
     * @author 段誉
     * @date 2021/7/30 18:37
     */
    public static function calculateFreight($goods, $userAddress)
    {
        $expressPrice = 0;
        $templateList = [];

        // 配送方式配置, 如果配送方式-快递配送已关闭则不参与运费计算
        $expressConfig = ConfigService::get('delivery_type', 'is_express', 1);

        if (empty($userAddress) || $expressConfig == YesNoEnum::NO) {
            return ['goods'=>$goods,'express_price'=>$expressPrice];
        }

        foreach ($goods as &$good) {
            //统一邮费
            if ($good['express_type'] == 2) {
                $good['express_price'] = round($good['express_money'] * $good['goods_num'], 2);
                $expressPrice += $good['express_price'];
            }
            //指定运费模板
            if ($good['express_type'] == 3 && $good['express_template_id'] > 0) {
//                $templateList[$good['express_template_id']][] = $good;
                $temp = [];
                $temp['template_id'] = $good['express_template_id'];
                $temp['total_volume'] = $good['volume'] * $good['goods_num'];
                $temp['total_weight'] = $good['weight'] * $good['goods_num'];
                $temp['goods_num'] = $good['goods_num'];

                $good['express_price'] = self::calculate($temp, $userAddress);
                $expressPrice += $good['express_price'];
            }
        }

//        foreach ($templateList as $templateId => $templateGoods) {
//            $temp = [];
//            $temp['template_id'] = $templateId;
//            $temp['total_volume'] = 0;
//            $temp['total_weight'] = 0;
//            $temp['goods_num'] = 0;
//            foreach ($templateGoods as $goods) {
//                $temp['total_volume'] += $goods['volume'] * $goods['goods_num'];
//                $temp['total_weight'] += $goods['weight'] * $goods['goods_num'];
//                $temp['goods_num'] += $goods['goods_num'];
//            }
//            $expressPrice += self::calculate($temp, $userAddress);
//        }
        return ['goods'=>$goods,'express_price'=>$expressPrice];
    }


    /**
     * @notes 计算运费
     * @param $data
     * @param $userAddress
     * @return float|int|mixed
     * @author 段誉
     * @date 2021/7/30 18:37
     */
    public static function calculate($data, $userAddress)
    {
        $expressPrice = 0;
        $freight = FreightLogic::getFreightsByAddress($data['template_id'], $userAddress);

        if (empty($freight)) {
            return $expressPrice;
        }
        $unit = 0;
        //按重量计算
        if ($freight['charge_way'] == FreightEnum::CHARGE_WAY_WEIGHT) {
            $unit = $data['total_weight'];
        }

        //按件数计算
        if ($freight['charge_way'] == FreightEnum::CHARGE_WAY_PIECE) {
            $unit = $data['goods_num'];
        }

        //按体积计算
        if ($freight['charge_way'] == FreightEnum::CHARGE_WAY_VOLUME) {
            $unit = $data['total_volume'];
        }

        if ($unit > $freight['first_unit'] && $freight['continue_unit'] > 0) {
            $left = ceil(($unit - $freight['first_unit']) / $freight['continue_unit']);//取整
            return $freight['first_money'] + $left * $freight['continue_money'];
        } else {
            return $freight['first_money'];
        }
    }


    /**
     * @notes 通过用户地址获取运费模板
     * @param $templateId
     * @param $address
     * @return mixed
     * @author 段誉
     * @date 2021/7/30 18:36
     */
    public static function getFreightsByAddress($templateId, $address)
    {
        $districtId = $address['district_id'];
        $cityId = $address['city_id'];
        $provinceId = $address['province_id'];

        $freights = Freight::withoutGlobalScope()
            ->aliasSid('f')
            ->alias('f')
            ->join('freight_config c', 'c.freight_id = f.id and c.delete_time is null')
            ->where('f.id', $templateId)
            ->order(['f.id' => 'desc', 'c.id' => 'desc'])
            ->select();
      
        $nationalFreight = [];
        foreach ($freights as $freight) {
            $regionIds = explode(',', $freight['region_id']);
            if (in_array($districtId, $regionIds)) {
                return $freight;
            }

            if (in_array($cityId, $regionIds)) {
                return $freight;
            }

            if (in_array($provinceId, $regionIds)) {
                return $freight;
            }
            //全国统一运费
            if (100000 == $freight['region_id']) {
                $nationalFreight = $freight;
            }
        }

        //会员的省市区id在商家的运费模板(指定地区)中找不到,查一下商家的全国运费模板
        return $nationalFreight;
    }

    /**
     * @notes 模板中指定地区id是否存在
     * @param $freights
     * @param $regionId
     * @return bool
     * @author 段誉
     * @date 2021/7/30 18:36
     */
    public static function isExistRegionId($freights, $regionId)
    {
        foreach ($freights as $freight) {
            $regionIds = explode(',', $freight['region_id']);
            if (in_array($regionId, $regionIds)) {
                return $freight;
            }
        }
        return false;
    }
}