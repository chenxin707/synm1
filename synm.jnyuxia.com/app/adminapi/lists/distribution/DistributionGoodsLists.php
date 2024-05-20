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
use app\common\model\Goods;

/**
 * 分销商品列表
 * Class DistributionGoodsLists
 * @package app\adminapi\lists\distribution
 */
class DistributionGoodsLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 导出字段
     * @return array
     * @author Tab
     * @date 2021/9/22 15:30
     */
    public function setExcelFields(): array
    {
        return [
            'name' => '商品信息',
            'min_price' => '最小价格',
            'max_price' => '最大价格',
            'is_distribution_desc' => '分销状态',
        ];
    }

    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/22 15:30
     */
    public function setFileName(): string
    {
        return '分销商品表';
    }

    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author Tab
     * @date 2021/7/23 16:15
     */
    public function setSearch(): array
    {
        return [
            '=' => ['supplier_id'],
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/16 19:30
     */
    public function attachSearch()
    {
        if (isset($this->params['name']) && !empty($this->params['name'])) {
            $this->searchWhere[] = ['name|code', 'like', '%'. $this->params['name'] . '%'];
        }
        if(isset($this->params['status']) && '' != $this->params['status']){
            if('-1' == $this->params['status']){
                $this->searchWhere[] = ['total_stock','=',0];
            }else{
                $this->searchWhere[] = ['status', '=', $this->params['status']];
            }
        }
    }

    /**
     * @notes 分销商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/23 16:15
     */
    public function lists(): array
    {
        $this->attachSearch();
        $field = 'id,name,image,min_price,max_price,status,id as is_distribution,id as commission,total_stock';
        $lists = Goods::field($field)
            ->with('goods_category_index.category_name')
            ->withSearch(['category_id', 'is_distribution'], $this->params)
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['is_distribution_desc'] = $item['is_distribution'] ? '参与' : '不参与';
            $item['status'] == 1 ? $item['status_desc'] = '销售中' : $item['status_desc'] = '仓库中';
            if($item['total_stock'] <= 0){
                $item['status_desc'] = '已售罄';
            }
            //商品价格
            $item['price'] = '¥' . $item['min_price'];
            if ($item['min_price'] != $item['max_price']) {
                $item['price'] = '¥' . $item['min_price'] . '~' . '¥' . $item['max_price'];
            }
        }

        return $lists;
    }

    /**
     * @notes 分销商品记录数
     * @return int
     * @author Tab
     * @date 2021/7/23 16:16
     */
    public function count(): int
    {
        $this->attachSearch();
        $count = Goods::withSearch(['category_id', 'is_distribution'], $this->params)
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}