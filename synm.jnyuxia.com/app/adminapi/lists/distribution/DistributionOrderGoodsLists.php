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

namespace app\adminapi\lists\distribution;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\service\FileService;

/**
 * 分销订单列表
 * Class DistributionOrderGoodsLists
 * @package app\adminapi\lists\distribution
 */
class DistributionOrderGoodsLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author Tab
     * @date 2021/7/27 18:54
     */
    public function setSearch(): array
    {
        return [
            '=' => ['o.sn', 'dog.status'],
            'between_time' => 'o.create_time'
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/22 16:30
     */
    public function attachSearch()
    {

        //买家信息
        if (isset($this->params['keyword']) && !empty($this->params['keyword']))
        {
            $this->searchWhere[] = ['du.sn|du.nickname', 'like', '%' . $this->params['keyword'] . '%'];
        }
        //分销商信息
        if (isset($this->params['dkeyword']) && !empty($this->params['dkeyword']))
        {
            $this->searchWhere[] = ['u.sn|u.nickname', 'like', '%' . $this->params['dkeyword'] . '%'];
        }

        // 商品信息
        if (isset($this->params['name']) && !empty($this->params['name']))
        {
            $this->searchWhere[] = ['g.name|g.code', 'like', '%' . $this->params['name'] . '%'];
        }

    }

    /**
     * @notes 设置导出表名
     * @return string
     * @author Tab
     * @date 2021/8/5 15:29
     */
    public function setFileName(): string
    {
        return '分销订单表';
    }

    /**
     * @notes 设置导出字段名
     * @return array
     * @author Tab
     * @date 2021/8/5 15:29
     */
    public function setExcelFields(): array
    {
        return [
            'sn' => '订单编号',
            'buyer_sn' => '买家编号',
            'buyer_nickname' => '买家昵称',
            'goods_name' => '商品名称',
            'spec_value_str' => '商品规格',
            'goods_price' => '商品价格',
            'goods_num' => '购买数量',
            'total_pay_price' => '实付金额',
            'distribution_member_sn' => '分销商编号',
            'distribution_member_nickname' => '分销商昵称',
            'level_desc' => '分销等级',
            'ratio' => '佣金比例',
            'earnings' => '佣金金额',
            'status_desc' => '佣金状态',
            'settlement_time' => '结算时间',
            'order_create_time' => '下单时间'
        ];
    }

    /**
     * @notes 分销订单列表
     * @return array
     * @author Tab
     * @date 2021/7/28 9:29
     */
    public function lists(): array
    {
        $this->attachSearch();

        $field = 'dog.id, dog.level_id,dog.level,dog.level as level_desc,dog.ratio, dog.earnings, dog.status, dog.status as status_desc, dog.settlement_time';
        $field .= ',og.goods_num, og.goods_price, og.total_pay_price';
        $field .= ',o.sn,o.user_id as buyer_id,o.create_time as order_create_time';
        $field .= ',g.image as goods_image, g.name as goods_name';
        $field .= ',gi.spec_value_str, gi.image as item_image';
        $field .= ',u.id as distribution_member_id,u.avatar as distribution_member_avatar, u.nickname as distribution_member_nickname,u.sn as distribution_member_sn';

        $this->sortOrder = ['dog.id' => 'desc'];

        $lists = DistributionOrderGoods::withoutGlobalScope()
            ->aliasSid('dog')
            ->alias('dog')
            ->leftJoin('order_goods og', 'og.id = dog.order_goods_id')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'g.id = dog.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = dog.item_id')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->leftJoin('user du','du.id = o.user_id')
            ->field($field)
            ->withSearch('user_id', $this->params)
            ->where($this->searchWhere)
            ->order($this->sortOrder)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $buyer = DistributionOrderGoods::getBueyer($item['buyer_id']);
            $item['buyer_nickname'] = $buyer['nickname'];
            $item['buyer_sn'] = $buyer['sn'];
            $item['buyer_avatar'] = $buyer['avatar'];
            $item['distribution_level_name'] = DistributionLevel::getLevelName($item['level_id']);
            $item['distribution_member_avatar'] = FileService::getFileUrl($item['distribution_member_avatar']);
            $item['image'] = $item['item_image'] ? $item['item_image'] : $item['goods_image'];
            $item['image'] = FileService::getFileUrl($item['image']);
            $item['order_create_time'] = $item['order_create_time'] ? date('Y-m-d H:i:s', $item['order_create_time']) : '';

        }

        return $lists;
    }

    /**
     * @notes 分销订单总记录数
     * @return int
     * @author Tab
     * @date 2021/7/28 9:29
     */
    public function count(): int
    {
        $this->attachSearch();

        $count = DistributionOrderGoods::withoutGlobalScope()
            ->aliasSid('dog')
            ->alias('dog')
            ->leftJoin('order_goods og', 'og.id = dog.order_goods_id')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'g.id = dog.goods_id')
            ->leftJoin('goods_item gi', 'gi.id = dog.item_id')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->leftJoin('user du','du.id = o.user_id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}