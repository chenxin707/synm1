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

namespace app\adminapi\lists\notice;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\NoticeEnum;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\NoticeSetting;

/**
 * 通知设置
 * Class NoticeSettingLists
 * @package app\adminapi\lists\notice
 */
class NoticeSettingLists extends BaseAdminDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['recipient', 'type']
        ];
    }

    /**
     * @notes 通知设置列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/18 16:35
     */
    public function lists(): array
    {
        $lists = NoticeSetting::field('*,type as type_desc,recipient as recipient_desc')
            ->where($this->searchWhere)
            ->order([
                'recipient' => 'asc',
                'type' => 'asc',
            ])
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            if(empty($item['system_notice'])) {
                $item['system_notice']['status'] = YesNoEnum::NO;
                $item['sms_notice']['status'] = YesNoEnum::NO;
                $item['oa_notice']['status'] = YesNoEnum::NO;
                $item['mnp_notice']['status'] = YesNoEnum::NO;
            }
        }

        return $lists;
    }

    /**
     * @notes 通知设置数量
     * @return int
     * @author Tab
     * @date 2021/8/18 16:44
     */
    public function count(): int
    {
        $count = NoticeSetting::where($this->searchWhere)->count();

        return $count;
    }
}