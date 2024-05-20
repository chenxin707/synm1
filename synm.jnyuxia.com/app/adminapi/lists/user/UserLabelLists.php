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
namespace app\adminapi\lists\user;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\
{
    UserLabel,
    UserLabelIndex
};


/**
 * 标签列表
 * Class UserLabelLists
 * @package app\adminapi\lists\user
 */
class UserLabelLists extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{
    public function setSearch(): array
    {
        return [
            '%like%' => ['name']
        ];
    }
    /**
     * @notes 列表
     * @return array
     * @author cjhao
     * @date 2021/7/28 18:48
     */
    public function lists(): array
    {
        $lists = UserLabel::field('id,name,label_type')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        $labelCount = UserLabelIndex::group('label_id')->column('count(id) as num', 'label_id');

        foreach ($lists as $key => $label) {
            $lists[$key]['num'] = $labelCount[$label['id']] ?? 0;
        }

        return $lists;
    }

    /**
     * @notes 搜索条数
     * @return int
     * @author cjhao
     * @date 2021/7/28 18:48
     */
    public function count(): int
    {
        return UserLabel::where($this->searchWhere)->count();
    }

    /**
     * @notes 设置excel表名
     * @return string
     * @author cjhao
     * @date 2021/9/23 17:05
     */
    public function setFileName(): string
    {
        return '用户标签';
    }

    /**
     * @notes 设置导出字段
     * @return array
     * @author cjhao
     * @date 2021/9/23 17:07
     */
    public function setExcelFields(): array
    {
        return [
            'name'      => '标签名称',
            'num'       => '用户数',
        ];
    }
}