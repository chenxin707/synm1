<?php

namespace app\shopapi\logic;

use app\common\logic\BaseLogic;
use app\common\model\IntegralGoods;
use app\common\model\User;
use app\shopapi\lists\IntegralGoodsLists;


/**
 * 积分商品逻辑
 * Class IntegralGoodsLogic
 * @package app\api\logic
 */
class IntegralGoodsLogic extends BaseLogic
{

    /**
     * @notes 积分商品列表
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/31 9:49
     */
    public static function lists($userId)
    {
        $integral = User::findOrEmpty($userId);
        $goods = (new IntegralGoodsLists());
        return [
            'integral' => $integral['user_integral'] ?? 0,
            'goods' => [
                'lists' => $goods->lists(),
                'count' => $goods->count(),
                'page_no' => $goods->pageNo,
                'page_size' => $goods->pageSize,
                'more' => is_more($goods->count(), $goods->pageNo, $goods->pageSize)
            ]
        ];
    }



    /**
     * @notes 商品详情
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/3/31 9:49
     */
    public static function detail($params)
    {
        return IntegralGoods::where(['id' => $params['id']])->findOrEmpty()->toArray();
    }

}
