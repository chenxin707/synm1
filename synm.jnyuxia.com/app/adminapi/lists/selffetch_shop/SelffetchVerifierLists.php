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
use app\common\model\SelffetchVerifier;
use app\common\service\FileService;

/**
 * 核销员列表
 * Class SelffetchVerifierLists
 * @package app\adminapi\lists\selffetch_shop
 */
class SelffetchVerifierLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author ljj
     * @date 2021/8/11 7:51 下午
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['sv.name'],
            '=' => ['sv.status'],
        ];
    }

    public function attachSearch()
    {
        if (isset($this->params['shop_name']) && !empty($this->params['shop_name'])) {
            $this->searchWhere[] = ['ss.name', 'like', '%' . $this->params['shop_name'] . '%'];
        }
    }

    /**
     * @notes 查看核销员列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 7:51 下午
     */
    public function lists(): array
    {
        $this->attachSearch();
        $lists = SelffetchVerifier::withoutGlobalScope()
            ->aliasSid('sv')
            ->alias('sv')
            ->join('user u', 'u.id = sv.user_id')
            ->join('selffetch_shop ss', 'ss.id = sv.selffetch_shop_id')
            ->field('sv.id,sv.name,sv.status,sv.sn,u.nickname,u.avatar,ss.name as selffetch_shop_name,sv.create_time')
            ->append(['status_desc'])
            ->order('sv.id','desc')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        if (empty($lists)) {
            return [];
        }

        foreach ($lists as &$list) {
            $list['avatar'] = empty($list['avatar']) ? '' : FileService::getFileUrl($list['avatar']);
        }

        return $lists;
    }

    /**
     * @notes 查看核销员总数
     * @return int
     * @author ljj
     * @date 2021/8/11 7:51 下午
     */
    public function count(): int
    {
        $this->attachSearch();
        return SelffetchVerifier::withoutGlobalScope()->aliasSid('sv')
        ->alias('sv')
        ->join('user u', 'u.id = sv.user_id')
        ->join('selffetch_shop ss', 'ss.id = sv.selffetch_shop_id')
        ->where($this->searchWhere)
        ->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/8/11 8:14 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'id' => 'ID',
            'name' => '核销员名称',
            'nickname' => '用户名称',
            'selffetch_shop_name' => '自提门店名称',
            'status_desc' => '状态',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/8/11 8:14 下午
     */
    public function setFileName(): string
    {
        return '核销员';
    }
}