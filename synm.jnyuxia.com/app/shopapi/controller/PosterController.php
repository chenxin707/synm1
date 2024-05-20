<?php
namespace app\shopapi\controller;

use app\common\logic\PosterLogic;
use app\common\model\Goods;
use app\common\model\User;
use app\shopapi\logic\GoodsLogic;

/**
 * 自定义海报
 */
class PosterController extends BaseShopController
{
    public array $notNeedLogin = ['getGoodsConfig', 'getDistributionConfig'];

    /**
     * @notes 获取商品海报配置
     */
    public function getGoodsConfig()
    {
        $goodsId = request()->get('goods_id', 0);
        $activityId = request()->get('activity_id', 0);
        $type = request()->get('type', 1);
        $config = PosterLogic::getGoodsConfig(0);
        $goods = GoodsLogic::getGoodsByTypeId($type, $activityId, $goodsId, $this->userId);
        $user = User::field('nickname, avatar')->where('id', $this->userId)->findOrEmpty()->toArray();
        $nickname = empty($user['nickname']) ? '' : $user['nickname'] ;
        $avatar = empty($user['avatar']) ? '' : $user['avatar'] ;
        $data = [
            'config' => $config,
            'goods' => $goods,
            'nickname' => $nickname,
            'avatar' => $avatar,
        ];
        return $this->data($data);
    }


    /**
     * @notes 获取邀请海报配置
     */
    public function getDistributionConfig()
    {
        $user = User::field('nickname, avatar,code')->where('id', $this->userId)->findOrEmpty()->toArray();
        $nickname = empty($user['nickname']) ? '' : $user['nickname'] ;
        $avatar = empty($user['avatar']) ? '' : $user['avatar'] ;
        $code = empty($user['code']) ? '' : $user['code'] ;
        $config = PosterLogic::getDistributionConfig();
        $data = [
            'config' => $config,
            'nickname' => $nickname,
            'avatar' => $avatar,
            'code' => $code,
        ];
        return $this->data($data);
    }

}
