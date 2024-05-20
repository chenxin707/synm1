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

namespace app\common\model;


use think\model\relation\HasMany;

class SeckillActivity extends BaseModel
{
    /**
     * @notes 关联秒杀商品模型
     * @return HasMany
     * @author 张无忌
     * @date 2021/8/2 19:39
     */
    public function goods()
    {
        return $this->hasMany(SeckillGoods::class, 'seckill_id', 'id');
    }

    /**
     * @notes 商品
     * @param $query
     * @param $value
     * @param $data
     * @return false
     * @author suny
     * @date 2021/9/23 5:45 下午
     */
    public function searchGoodsAttr($query, $value, $data)
    {
        if(!isset($data['goods']) || empty($data['goods'])) {
            return false;
        }
        $goods_ids = Goods::where('name|code', 'like', '%'.$data['goods'].'%')->column('id');
        $seckill_ids = SeckillGoods::where([['goods_id', 'in', $goods_ids]])->column('seckill_id');
        $query->where('SA.id', 'in', $seckill_ids);
    }
}