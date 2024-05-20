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
use app\common\enum\DistributionApplyEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\DistributionApply;
use app\common\model\UserLevel;
use app\common\service\FileService;
use app\common\service\RegionService;

/**
 * 分销商申请列表
 * Class DistributionApplyLists
 * @package app\adminapi\lists\distribution
 */
class DistributionApplyLists extends BaseAdminDataLists implements ListsSearchInterface,ListsExtendInterface,ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author Tab
     * @date 2021/7/27 15:02
     */
    public function setSearch(): array
    {
        return [
            '=' => ['da.status'],
            'between_time' => 'da.create_time'
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/22 15:00
     */
    public function attachSearch()
    {
        // 用户信息
        if (isset($this->params['user_info']) && !empty($this->params['user_info'])) {
            $this->searchWhere[] = ['u.sn|u.nickname', 'like', '%' . $this->params['user_info'] . '%'];
        }
    }

    /**
     * @notes 统计数据
     * @return array
     * @author Tab
     * @date 2021/8/4 16:37
     */
    public function extend(): array
    {
        $all = DistributionApply::count();
        $wait = DistributionApply::where('status', DistributionApplyEnum::AUDIT_WAIT)->count();
        $pass = DistributionApply::where('status', DistributionApplyEnum::AUDIT_PASS)->count();
        $refuse = DistributionApply::where('status', DistributionApplyEnum::AUDIT_REFUSE)->count();

        return [
            'all' => $all,
            'wait' => $wait,
            'pass' => $pass,
            'refuse' => $refuse,
        ];
    }

    /**
     * @notes 设置默认导出表名
     * @return string
     * @author Tab
     * @date 2021/8/4 16:40
     */
    public function setFileName(): string
    {
        return '分销商申请表';
    }

    /**
     * @notes 设置导出字段名
     * @return string[]
     * @author Tab
     * @date 2021/8/4 16:47
     */
    public function setExcelFields(): array
    {
        return [
            'user_info' => '用户信息',
            'real_name' => '真实姓名',
            'mobile' => '联系手机',
            'address' => '所属区域',
            'reason' => '申请原因',
            'status_desc' => '申请状态',
            'audit_remark' => '审核说明',
            'create_time' => '申请时间',
        ];
    }

    /**
     * @notes 列表
     * @return array
     * @author Tab
     * @date 2021/7/27 15:18
     */
    public function lists(): array
    {
        $this->attachSearch();

        $field = 'da.id, da.real_name, da.mobile, da.province, da.city, da.district, da.reason, da.status, da.status as status_desc, da.audit_remark, da.create_time';
        $field .= ',u.id as user_id,u.avatar,u.sn,u.nickname, u.level';

        $this->sortOrder = ['id' => 'desc'];

        $lists = DistributionApply::withoutGlobalScope()
            ->aliasSid('da')
            ->alias('da')
            ->leftJoin('user u', 'u.id = da.user_id')
            ->field($field)
            ->where($this->searchWhere)
            ->order($this->sortOrder)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        $userLevelLists = UserLevel::column('name', 'id');
        foreach ($lists as &$item) {
            $item['address'] = RegionService::getAddress([$item['province'], $item['city'], $item['district']]);
            $item['level_name'] = $userLevelLists[$item['level']] ?? '无等级';
            $item['avatar'] = trim($item['avatar']) ? FileService::getFileUrl($item['avatar']) : '';
            $item['user_info'] = $item['nickname'] . '(' . $item['sn'] . ')';
        }

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/7/27 15:19
     */
    public function count(): int
    {
        $this->attachSearch();

        $count = DistributionApply::withoutGlobalScope()
            ->aliasSid('da')
            ->alias('da')
            ->leftJoin('user u', 'u.id = da.user_id')
            ->where($this->searchWhere)
            ->count();

        return $count;
    }

}