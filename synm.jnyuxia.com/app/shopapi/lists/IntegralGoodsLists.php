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

namespace app\shopapi\lists;


use app\common\enum\IntegralGoodsEnum;
use app\common\model\IntegralGoods;

/**
 * 积分商品列表
 * Class IntegralGoodsLists
 * @package app\shopapi\lists
 */
class IntegralGoodsLists extends BaseShopDataLists
{

    /**
     * @notes 设置排序
     * @return array
     * @author 段誉
     * @date 2022/3/31 9:40
     */
    public function setSort()
    {
        $sort = [];

        // 兑换积分排序
        if (!empty($this->params['sort_integral'])) {
            $sort['need_integral'] = $this->params['sort_integral'];
        }

        // 销量排序
        if (!empty($this->params['sort_sales'])) {
            $sort['sales'] = $this->params['sort_sales'];
        }

        // 最新排序
        $sort['id'] = 'desc';
        if (!empty($this->params['sort_new'])) {
            $sort['id'] = $this->params['sort_new'];
        }

        return $sort;
    }


    /**
     * @notes 积分商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/31 9:40
     */
    public function lists(): array
    {
        $field = ['id', 'image', 'name', 'sales', 'need_integral', 'need_money', 'exchange_way'];
        $lists = IntegralGoods::where(['status' => IntegralGoodsEnum::STATUS_SHELVES])
            ->order(['sort' => 'desc'])
            ->field($field)
            ->order($this->setSort())
            ->limit($this->limitOffset, $this->limitLength)
            ->select()->toArray();
        return $lists;
    }


    /**
     * @notes 积分商品数量
     * @return int
     * @author 段誉
     * @date 2022/3/31 9:40
     */
    public function count(): int
    {
        return IntegralGoods::where(['status' => IntegralGoodsEnum::STATUS_SHELVES])->count();
    }
}