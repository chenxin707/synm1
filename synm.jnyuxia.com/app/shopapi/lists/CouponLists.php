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


use app\common\enum\CouponEnum;
use app\common\lists\ListsSearchInterface;
use app\common\model\Coupon;
use app\common\model\CouponList;
use app\common\service\TimeService;

class CouponLists extends BaseShopDataLists implements ListsSearchInterface
{
    /**
     * @notes 优惠券列表搜索条件
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:13
     */
    public function setSearch(): array
    {
        return [];
    }

    /**
     * @notes 获取优惠券列表
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/7/29 18:13
     */
    public function lists(): array
    {
        $model = new Coupon();
        $lists = $model->field(true)
            ->where(['status' => CouponEnum::COUPON_STATUS_CONDUCT])
            ->where(['get_type' => CouponEnum::GET_TYPE_USER])
            ->withAttr('is_available', function ($value, $data) {
                unset($value);
                switch ($data['get_num_type']) {
                    case CouponEnum::GET_NUM_TYPE_NOT:
                        return 0;
                    case CouponEnum::GET_NUM_TYPE_LIMIT:
                        $total = (new CouponList())
                            ->where(['coupon_id' => $data['id']])
                            ->where(['user_id' => $this->userId])
                            ->count();
                        return $total >= $data['get_num'] ? 1 : 0;
                    case CouponEnum::GET_NUM_TYPE_DAY:
                        $total = (new CouponList())
                            ->where(['coupon_id' => $data['id']])
                            ->where(['user_id' => $this->userId])
                            ->where('create_time', '>=', TimeService::today()[0])
                            ->where('create_time', '<=', TimeService::today()[1])
                            ->count();
                        return $total >= $data['get_num'] ? 1 : 0;
                }
                return 0;
            })->withAttr('is_receive', function ($value, $data) {
                unset($value);
                $couponList = (new CouponList())
                    ->where(['coupon_id' => $data['id']])
                    ->where(['user_id' => $this->userId])
                    ->findOrEmpty();
                return $couponList->isEmpty() ? 0 : 1;
            })
            ->order('id', 'desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->append(['is_receive', 'is_available'])
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['condition'] = $item['condition_type'] == 1
                ? '无金额门槛'
                : '满' . intval($item['condition_money']) . '使用';

            switch ($item['use_goods_type']) {
                case CouponEnum::USE_GOODS_TYPE_NOT:
                    $item['use_scene'] = '全场通用';
                    break;
                case CouponEnum::USE_GOODS_TYPE_ALLOW:
                case CouponEnum::USE_GOODS_TYPE_BAN:
                    $item['use_scene'] = '指定商品可用';
            }

            switch ($item['use_time_type']) {
                case CouponEnum::USE_TIME_TYPE_FIXED:
                    $item['effective_time'] =
                        date('Y.m.d H:i', $item['use_time_start']) . ' ~ ' .
                        date('Y.m.d H:i', $item['use_time_end']);
                    break;
                case CouponEnum::USE_TIME_TYPE_TODAY:
                    $item['effective_time'] = '领取' . $item['use_time'] . '天内可用';
                    break;
                case CouponEnum::USE_TIME_TYPE_TOMORROW:
                    $item['effective_time'] = '领取次日' . $item['use_time'] . '天内可用';
                    break;
            }

            unset($item['condition_type']);
            unset($item['condition_money']);
            unset($item['send_total_type']);
            unset($item['send_total']);
            unset($item['use_time_type']);
            unset($item['use_time_start']);
            unset($item['use_time_end']);
            unset($item['use_time']);
            unset($item['get_type']);
            unset($item['get_num_type']);
            unset($item['get_num']);
            unset($item['use_goods_type']);
            unset($item['use_goods_ids']);
            unset($item['create_time']);
            unset($item['update_time']);
            unset($item['delete_time']);
        }

        return $lists;
    }

    /**
     * @notes 获取优惠券数量
     * @return int
     * @author 张无忌
     * @date 2021/7/29 18:14
     */
    public function count(): int
    {
        $model = new Coupon();
        return $model->field(true)
            ->where(['status' => CouponEnum::COUPON_STATUS_CONDUCT])
            ->count();
    }
}