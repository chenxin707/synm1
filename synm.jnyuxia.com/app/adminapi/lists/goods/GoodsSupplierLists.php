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
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\GoodsSupplier;

class GoodsSupplierLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author ljj
     * @date 2021/7/17 2:04
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['code', 'name', 'contact', 'mobile', 'landline'],
            '=' => ['supplier_category_id'],
        ];
    }

    /**
     * @notes 查看供应商列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/17 2:09
     */
    public function lists(): array
    {
        $lists = GoodsSupplier::field('id,code,name,supplier_category_id,contact,mobile,landline,sort,create_time')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['sort'=>'asc','id'=>'desc'])
            ->append(['supplier_category'])
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 查看供应商总数
     * @return int
     * @author ljj
     * @date 2021/7/17 2:11
     */
    public function count(): int
    {
        return GoodsSupplier::where($this->searchWhere)->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/7/31 3:47 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'name' => '供应商名称',
            'code' => '供应商编码',
            'supplier_category' => '供应商分类',
            'contact' => '联系人',
            'mobile' => '联系电话',
            'landline' => '座机号码',
            'sort' => '排序',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/7/31 3:47 下午
     */
    public function setFileName(): string
    {
        return '商品供应商';
    }
}