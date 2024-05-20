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

namespace app\adminapi\logic\integral;


use app\common\enum\IntegralGoodsEnum;
use app\common\logic\BaseLogic;
use app\common\model\IntegralGoods;


/**
 * 积分商品
 * Class IntegralGoodsLogic
 * @package app\adminapi\logic\integral
 */
class IntegralGoodsLogic extends BaseLogic
{


    /**
     * @notes 添加积分商品
     * @param array $params
     * @return IntegralGoods|\think\Model
     * @author 段誉
     * @date 2022/3/30 12:17
     */
    public static function add(array $params)
    {
        return IntegralGoods::duokaiCreate([
            'name' => $params['name'],
            'code' => $params['code'] ?? '',
            'image' => $params['image'],
            'type' => $params['type'],
            'market_price' => $params['market_price'] ?? '',
            'stock' => $params['stock'],
            'status' => $params['status'],
            'exchange_way' => $params['exchange_way'] ?? 1,
            'need_integral' => $params['need_integral'],
            'need_money' => $params['need_money'] ?? 0,
            'delivery_way' => $params['delivery_way'] ?? 0,
            'balance' => $params['balance'] ?? 0,
            'express_type' => $params['express_type'] ?? 0,
            'express_money' => $params['express_money'] ?? 0,
            'content' => $params['content'] ?? '',
            'sort' => $params['sort'] ?? 0,
        ]);
    }


    /**
     * @notes 编辑积分商品
     * @param array $params
     * @author 段誉
     * @date 2022/3/30 14:08
     */
    public static function edit(array $params)
    {
        // 包邮或无需快递,运费重置为0
        if ($params['delivery_way'] == IntegralGoodsEnum::DELIVERY_NO_EXPRESS
            || (isset($params['express_type']) && $params['express_type'] == IntegralGoodsEnum::EXPRESS_TYPE_FREE)
        ) {
            $params['express_money'] = 0;
            $params['express_type'] = IntegralGoodsEnum::EXPRESS_TYPE_FREE;
        }

        IntegralGoods::update($params, ['id' => $params['id']], [
            'name','code', 'image', 'market_price', 'stock', 'status','exchange_way',
            'need_integral', 'need_money', 'delivery_way', 'balance', 'express_type',
            'express_money', 'content', 'sort'
        ]);
    }



    /**
     * @notes 删除积分商品
     * @param int $id
     * @return bool
     * @author 段誉
     * @date 2022/3/30 14:11
     */
    public static function del(int $id): bool
    {
        return IntegralGoods::destroy($id);
    }



    /**
     * @notes 积分商品详情
     * @param int $id
     * @return array
     * @author 段誉
     * @date 2022/3/30 14:14
     */
    public static function detail(int $id)
    {
        return IntegralGoods::findOrEmpty($id)->toArray();
    }



    /**
     * @notes  设置积分商品状态
     * @param array $params
     * @return IntegralGoods
     * @author 段誉
     * @date 2022/3/30 14:15
     */
    public static function setStatus(array $params)
    {
        return IntegralGoods::duokaiUpdate(['status' => $params['status']],['id'=>$params['id']], [], '', false);
    }


}