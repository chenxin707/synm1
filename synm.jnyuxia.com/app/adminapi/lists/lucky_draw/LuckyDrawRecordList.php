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

namespace app\adminapi\lists\lucky_draw;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\LuckyDrawEnum;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsExcelInterface;
use app\common\model\Coupon;
use app\common\model\LuckyDrawRecord;
use app\common\model\User;
use app\common\service\FileService;

/**
 * 幸运抽奖记录
 */
class LuckyDrawRecordList extends BaseAdminDataLists implements ListsExcelInterface
{
    public function setFileName(): string
    {
        return '抽奖记录';
    }

    public function setExcelFields(): array
    {
        return [
            'id' => '编号',
            'nickname' => '用户昵称',
            'name' => '奖品名称',
            'prize_type_desc' => '奖品类型',
            'prize_content' => '奖品内容',
            'status_desc' => '中奖状态',
            'create_time' => '中奖时间',
        ];
    }

    public function setSearch()
    {
        // 具体某场活动
        $this->searchWhere[] = ['ldr.activity_id', '=', $this->params['id']];

        // 根据用户信息查找
        if (isset($this->params['user_info']) && !empty($this->params['user_info'])) {
            $this->searchWhere[] = [
                'u.nickname|u.sn|u.mobile', 'like', '%'. trim($this->params['user_info']) . '%'
            ];
        }
        // 根据奖品类型查找
        if (isset($this->params['prize_type']) && $this->params['prize_type'] != '') {
            $this->searchWhere[] = ['ldr.prize_type', '=', $this->params['prize_type']];
        }
        // 根据中奖状态查找
        if (isset($this->params['status']) && $this->params['status'] == YesNoEnum::NO) {
            $this->searchWhere[] = ['ldr.prize_type', '=', LuckyDrawEnum::NOT_WIN];
        }
        if (isset($this->params['status']) && $this->params['status'] == YesNoEnum::YES) {
            $this->searchWhere[] = ['ldr.prize_type', '<>', LuckyDrawEnum::NOT_WIN];
        }
        // 根据抽奖时间查找
        if (
            isset($this->params['start_time']) && isset($this->params['end_time'])
            && !empty($this->params['start_time']) && !empty($this->params['end_time'])
        ) {
            $this->searchWhere[] = ['ldr.create_time', '>=', strtotime($this->params['start_time'])];
            $this->searchWhere[] = ['ldr.create_time', '<=', strtotime($this->params['end_time'])];
        }

    }

    public function lists(): array
    {
        $this->setSearch();

        $field = [
            'u.avatar',
            'u.nickname',
            'ldp.name',
            'ldp.type',
            'ldp.type_value',
            'ldr.create_time',
            'ldr.id',
        ];
        $lists = LuckyDrawRecord::withoutGlobalScope()
            ->aliasSid('ldr')
            ->alias('ldr')
            ->leftJoin('user u', 'u.id = ldr.user_id')
            ->leftJoin('lucky_draw_prize ldp', 'ldp.id = ldr.prize_id')
            ->field($field)
            ->where($this->searchWhere)
            ->order('ldr.id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['avatar'] = empty($item['avatar']) ? '' : FileService::getFileUrl($item['avatar']) ;
            $item['prize_type_desc'] = LuckyDrawEnum::getPrizeTypeDesc($item['type']);
            $item['prize_content'] = $this->getPrizeContent($item);
            $item['status_desc'] = in_array($item['type'], LuckyDrawEnum::WIN_PRIZE_TYPE) ? '中奖' : '未中奖';
        }

        return $lists;
    }


    public function count(): int
    {
        $this->setSearch();
        $count = LuckyDrawRecord::withoutGlobalScope()
            ->aliasSid('ldr')
            ->alias('ldr')
            ->leftJoin('user u', 'u.id = ldr.user_id')
            ->leftJoin('lucky_draw_prize ldp', 'ldp.id = ldr.prize_id')
            ->where($this->searchWhere)
            ->count();
        return $count;
    }

    /**
     * @notes 获取奖品内容
     * @param $item
     * @return string
     * @author Tab
     * @date 2021/11/25 16:44
     */
    public function getPrizeContent($item)
    {
        if ($item['type'] == LuckyDrawEnum::INTEGRAL) {
            return $item['type_value'] . '积分';
        }
        if ($item['type'] == LuckyDrawEnum::COUPON) {
            $couponName = Coupon::where('id', $item['type_value'])->value('name');
            return  empty($couponName) ? '-' : $couponName;
        }
        if ($item['type'] == LuckyDrawEnum::BALANCE) {
            return $item['type_value'] . '元';
        }

        return '-';
    }
}