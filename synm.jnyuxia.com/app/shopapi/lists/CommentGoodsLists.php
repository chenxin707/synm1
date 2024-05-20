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


use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\GoodsComment;
use app\common\model\GoodsItem;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\service\FileService;

class CommentGoodsLists extends BaseShopDataLists implements ListsExtendInterface
{
    public function extend()
    {
        $waitWhere = [
            ['o.user_id', '=', $this->userId],
            ['o.order_status', '=', 3],
            ['og.is_comment', '=', YesNoEnum::NO],
        ];
        $wait = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->where($waitWhere)
            ->count();
        $finishWhere = [
            ['o.user_id', '=', $this->userId],
            ['o.order_status', '=', 3],
            ['og.is_comment', '=', YesNoEnum::YES],
        ];
        $finish = OrderGoods::withoutGlobalScope()
            ->aliasSid('og')
            ->alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->where($finishWhere)
            ->count();
        return [
            'wait' => $wait,
            'finish' => $finish
        ];
    }

    /**
     * @notes 设置搜索条件
     * @return array
     * @author ljj
     * @date 2021/8/9 2:47 下午
     */
    public function setSearch()
    {
        $where= [];
        $where[] = ['o.user_id', '=', $this->userId];
        $where[] = ['o.order_status', '=', 3];
        if (!isset($this->params['type']) || $this->params['type'] == '') {
            $where[] = ['og.is_comment','=',0];
            return $where;
        }
        $where[] = ['og.is_comment','=',$this->params['type']];
        return $where;
    }

    /**
     * @notes 查看评价商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/9 2:47 下午
     */
    public function lists(): array
    {
        $lists = Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('goods g', 'og.goods_id = g.id')
            ->leftjoin('goods_comment gc','og.id = gc.order_goods_id')
            ->field('og.id,og.goods_id,og.item_id,g.image as goods_image,g.name as goods_name,og.goods_price,og.goods_num,og.is_comment')
            ->where($this->setSearch())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('og.id','desc')
            ->group('og.id')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            //处理商品图片路径
            $list['goods_image'] = empty($list['goods_image']) ? '' : FileService::getFileUrl($list['goods_image']);
            // 商品规格
            $list['spec_value_str'] = GoodsItem::where('id', $list['item_id'])->value('spec_value_str');
            //获取商品评价
            $list['goods_comment'] = [];
            if ($list['is_comment'] == 0) {
                continue;
            }
            $list['goods_comment'] = GoodsComment::where(['user_id'=>$this->userId,'order_goods_id'=>$list['id']])
                ->field('id,goods_comment,comment,create_time,reply')
                ->with('goods_comment_image')
                ->find();
        }

        return $lists;
    }

    /**
     * @notes 查看评价商品总数
     * @return int
     * @author ljj
     * @date 2021/8/9 2:47 下午
     */
    public function count(): int
    {
        return Order::withoutGlobalScope()
            ->aliasSid('o')
            ->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('goods g', 'og.goods_id = g.id')
            ->where($this->setSearch())
            ->limit($this->limitOffset, $this->limitLength)
            ->group('og.id')
            ->count();
    }
}