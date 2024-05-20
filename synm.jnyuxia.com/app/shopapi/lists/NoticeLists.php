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

namespace app\shopapi\lists;

use app\common\enum\NoticeEnum;
use app\common\lists\BaseDataLists;
use app\common\model\Notice;

/**
 * 通知列表
 * Class NoticeLists
 * @package app\shopapi\lists
 */
class NoticeLists extends BaseShopDataLists
{
    public function setSearch()
    {
        $this->searchWhere = [
            ['user_id', '=', $this->userId],
            ['send_type', '=', NoticeEnum::SYSTEM]
        ];
        // 系统通知
        if (isset($this->params['type']) && $this->params['type'] == 'system') {
            $this->searchWhere[] = [
                ['scene_id', '<>', NoticeEnum::EARNINGS_NOTICE]
            ];
        }
        // 收益通知
        if (isset($this->params['type']) && $this->params['type'] == 'earnings') {
            $this->searchWhere[] = [
                ['scene_id', '=', NoticeEnum::EARNINGS_NOTICE]
            ];
        }
    }

    /**
     * @notes 列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/25 10:39
     */
    public function lists(): array
    {
        $this->setSearch();
        $lists = Notice::field('id,title,content,create_time')
            ->where($this->searchWhere)
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/8/25 10:39
     */
    public function count(): int
    {
        $this->setSearch();
        $count = Notice::where($this->searchWhere)->count();

        return $count;
    }
}
