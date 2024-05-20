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
use app\common\model\GoodsCategory;

class GoodsCategoryLists extends BaseAdminDataLists implements ListsExcelInterface
{
    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/7/31 3:07 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'name' => '分类名称',
            'level' => '分类等级',
            'image' => '分类图标',
            'is_show_desc' => '状态',
            'sort' => '排序',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/7/31 3:07 下午
     */
    public function setFileName(): string
    {
        return '商品分类';
    }

    /**
     * @notes 查看商品分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 11:31 上午
     */
    public function lists(): array
    {
        $lists = GoodsCategory::field('id,pid,name,level,image,is_show,is_recommend,sort,create_time')
            ->order(['sort'=>'asc','id'=>'desc'])
            ->append(['is_show_desc','goods_num'])
            ->select()
            ->toArray();

        $lists = linear_to_tree($lists,'sons');

        //分页
//        $page_no = $this->pageNo * $this->pageSize - $this->pageSize;
//        $lists = array_slice($lists, $page_no, $this->pageSize);

        return $lists;
    }

    /**
     * @notes 查看商品分类总数
     * @return int
     * @author ljj
     * @date 2021/7/17 6:01 下午
     */
    public function count(): int
    {
        return GoodsCategory::count();
    }
}