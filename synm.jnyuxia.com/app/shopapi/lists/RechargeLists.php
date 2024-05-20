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

use app\common\enum\PayEnum;
use app\common\model\RechargeOrder;
use app\common\model\RechargeTemplate;

/**
 * 充值记录列表
 * Class RechargeLists
 * @package app\shopapi\lists
 */
class RechargeLists extends BaseShopDataLists
{
    /**
     * @notes 充值成功记录列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/8/11 15:14
     */
    public function lists(): array
    {
        $lists = RechargeOrder::field('order_amount,award,create_time')
            ->where([
                'user_id' => $this->userId,
                'pay_status' => PayEnum::ISPAID
            ])
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach($lists as &$item) {
            $item['tips'] = $this->getTips($item);
        }

        return $lists;
    }

    /**
     * @notes 充值成功记录数
     * @return int
     * @author Tab
     * @date 2021/8/11 15:15
     */
    public function count(): int
    {
        $count = RechargeOrder::field('order_amount,award,create_time')
            ->where([
                'user_id' => $this->userId,
                'pay_status' => PayEnum::ISPAID
            ])
            ->count();

        return $count;
    }

    /**
     * @notes 获取充值赠送提示语
     * @param $item
     * @return string
     * @author Tab
     * @date 2021/8/11 10:13
     */
    public function getTips(&$item)
    {
        if(empty($item['award']) || !is_array($item['award'])) {
            return '充值' . clear_zero($item['order_amount']) . '元';
        }
        foreach($item['award'] as $subItem) {
            $tips = isset($subItem['give_money']) && $subItem['give_money'] > 0 ? '充' . clear_zero($item['order_amount']) . '送' . clear_zero($subItem['give_money']) . '元' : '';
            $item['order_amount'] += $subItem['give_money'];
            return $tips;
        }
    }
}