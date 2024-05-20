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
namespace app\adminapi\lists\goods;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\DeliveryEnum;
use app\common\enum\GoodsEnum;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\Goods;

/**
 * 商品列表接口
 * Class GoodsCommentAssistantLists
 * @package app\adminapi\lists\goods
 */
class GoodsCommentAssistantLists extends BaseAdminDataLists
{
    /**
     * @notes 搜索条件
     * @author Tab
     * @datetime 2022/1/18 9:06
     */
    public function setSearch()
    {
        // 商品名称
        if (isset($this->params['goods_name']) && $this->params['goods_name'] != '') {
            $this->searchWhere[] = ['name', 'like', '%' . $this->params['goods_name'] . '%'];
        }

        // 销售状态
        if (isset($this->params['status']) && in_array($this->params['status'], [GoodsEnum::STATUS_STORAGE, GoodsEnum::STATUS_SELL])) {
            $this->searchWhere[] = ['status', '=', $this->params['status']];
        }

        // 配送方式
        if (isset($this->params['delivery_type']) && $this->params['delivery_type'] == DeliveryEnum::EXPRESS_DELIVERY) {
            $this->searchWhere[] = ['is_express', '=', YesNoEnum::YES];
        }

        if (isset($this->params['delivery_type']) && $this->params['delivery_type'] == DeliveryEnum::SELF_DELIVERY) {
            $this->searchWhere[] = ['is_selffetch', '=', YesNoEnum::YES];
        }
    }

    public function lists(): array
    {
        $this->setSearch();

        $field = [
            'id',
            'image',
            'name',
            'spec_type',
            'min_price',
            'max_price',
            'total_stock',
            'sales_num',
            'status',
            'create_time',
        ];
        $lists = Goods::field($field)
            ->append(['price_text', 'category_text', 'status_text', 'comment_text'])
            ->withSearch(['category_id'], $this->params)
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;

    }


    public function count(): int
    {
       $this->setSearch();

       $field = [
        'id',
        'image',
        'name',
        'spec_type',
        'min_price',
        'max_price',
        'total_stock',
        'sales_num',
        'status',
        'create_time',
        ];
        $count = Goods::field($field)
            ->append(['price_text', 'category_text', 'status_text', 'comment_text'])
            ->where($this->searchWhere)
            ->count();

       return $count;
    }


}
