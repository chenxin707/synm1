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

namespace app\adminapi\lists\selffetch_shop;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\SelffetchShop;

class SelffetchShopLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author ljj
     * @date 2021/8/11 3:49 下午
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['name'],
            '=' => ['status'],
        ];
    }

    /**
     * @notes 查看自提门店列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 3:54 下午
     */
    public function lists(): array
    {
        $lists = SelffetchShop::field('id,image,name,contact,mobile,business_start_time,business_end_time,status,create_time,province,city,district,address')
            ->append(['detailed_address','status_desc'])
            ->hidden(['province','city','district','address'])
            ->order('id','desc')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 查看自提门店总数
     * @return int
     * @author ljj
     * @date 2021/8/11 3:55 下午
     */
    public function count(): int
    {
        return SelffetchShop::where($this->searchWhere)->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/8/11 8:34 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'id' => 'ID',
            'name' => '门店名称',
            'contact' => '联系人',
            'mobile' => '联系电话',
            'detailed_address' => '门店地址',
            'business_start_time' => '营业开始时间',
            'business_end_time' => '营业结束时间',
            'status_desc' => '状态',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/8/11 8:34 下午
     */
    public function setFileName(): string
    {
        return '自提门店';
    }

}