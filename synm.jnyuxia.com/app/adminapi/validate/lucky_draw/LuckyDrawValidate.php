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

namespace app\adminapi\validate\lucky_draw;

use app\common\enum\CouponEnum;
use app\common\enum\LuckyDrawEnum;
use app\common\model\Coupon;
use app\common\model\LuckyDraw;
use app\common\validate\BaseValidate;

/**
 * 幸运抽奖
 */
class LuckyDrawValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkActivity',
        'name' => 'require|max:60',
        'start_time' => 'require',
        'end_time' => 'require',
        'need_integral' => 'require|integer|egt:0',
        'frequency_type' => 'require|integer|in:0,1',
        'frequency' => 'require|integer|egt:0',
        'rule' => 'require',
        'show_winning_list' => 'require|in:0,1',
        'prizes' => 'require|array|length:8|checkPrizes'
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'name.require' => '请输入活动名称',
        'name.max' => '活动名称不能超过60个字符',
        'start_time.require' => '选择活动开始时间',
        'end_time.require' => '选择活动结束时间',
        'need_integral.require' => '请输入消耗的积分',
        'need_integral.integer' => '消耗的积分须为整型',
        'need_integral.egt' => '消耗的积分不能为负数',
        'frequency_type.require' => '请选择抽奖次数类型',
        'frequency_type.integer' => '抽奖次数类型须为整数',
        'frequency_type.in' => '抽奖次数类型值错误',
        'frequency.require' => '请输入抽奖次数',
        'frequency.integer' => '抽奖次数须数整型',
        'frequency.egt' => '抽奖次数不能为负数',
        'rule.require' => '请输入抽奖规则',
        'show_winning_list.require' => '请选择是否显示中奖名单',
        'show_winning_list.in' => '是否显示中奖名单状态值错误',
        'prizes.require' => '请填写抽奖奖品',
        'prizes.array' => '抽奖奖品须为数组格式',
        'prizes.length' => '抽奖奖品须为8个',

    ];

    public function sceneAdd()
    {
        return $this->only(['name', 'start_time', 'end_time', 'need_integral', 'frequency_type', 'frequency', 'rule', 'show_winning_list', 'prizes']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'start_time', 'end_time', 'need_integral', 'frequency_type', 'frequency', 'rule', 'show_winning_list', 'prizes']);
    }

    public function sceneStart()
    {
        return $this->only(['id']);
    }

    public function sceneEnd()
    {
        return $this->only(['id']);
    }

    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    public function sceneRecord()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 校验奖品
     * @param $value
     * @param $rule
     * @param $data
     * @author Tab
     * @date 2021/11/24 10:42
     */
    public function checkPrizes($prizes, $rule, $data)
    {
        $sumProbability = 0;
        $winPrizeCount = 0;
        foreach ($prizes as $key => $item) {
            if (isset($data['id']) && !isset($item['id'])) {
                // 编辑的时候奖品id要携带过来
                return 'error01:位置为'. ($key + 1) . '的奖品格式错误';
            }
            if (!isset($item['name'], $item['image'], $item['type'], $item['type_value'], $item['num'], $item['probability'], $item['tips'])) {
                return 'error02:位置为'. ($key + 1) . '的奖品格式错误';
            }
            if ((int)$item['type_value'] < 0 || (int)$item['num'] < 0 || (int)$item['probability'] < 0) {
                return 'error03:位置为'. ($key + 1) . '的奖品不能出现负数';
            }
            if ($item['type'] == LuckyDrawEnum::COUPON && !$this->checkCoupon($item['type_value'])) {
                return '位置为'. ($key + 1) . '的优惠券无效';
            }
            if (in_array($item['type'], LuckyDrawEnum::WIN_PRIZE_TYPE)) {
                // 累计奖品中奖概率(乘以100将小数转为整数)
                $baseNum = isset($data['id']) ? 1 : 100; // 编辑时中奖概率已乘过基数
                if(empty($item['probability'])){
                    return '请输入位置为'. ($key + 1) . '的中奖概率';
                }
                $sumProbability += (int)(round($item['probability'], 2) * $baseNum);
                $winPrizeCount ++;
            }
            if (!is_numeric($item['type_value']) || !is_numeric($item['num']) || !is_numeric($item['probability'])) {
                return '位置为'. ($key + 1) . '的奖品，奖品类型的值、数量、概率须为数字';
            }
        }

        if ($sumProbability > 10000) {
            return '奖品中奖概率不能超过100%';
        }
        if ($winPrizeCount == 8 && $sumProbability != 10000) {
            return '全部都是奖品，中奖概率之和需为100%';
        }
        return true;
    }

    /**
     * @notes 校验优惠券
     * @param $couponId
     * @return bool
     * @author Tab
     * @date 2021/11/24 11:05
     */
    public function checkCoupon($couponId) {
        $coupon =  Coupon::findOrEmpty($couponId);
        if ($coupon->isEmpty() || $coupon->get_type != CouponEnum::GET_TYPE_STORE || $coupon->status != CouponEnum::COUPON_STATUS_CONDUCT) {
            // 优惠券不存在 || 不是卖家发放形式 || 发放状态不是进行中
            return false;
        }
        return true;
    }

    /**
     * @notes 校验活动
     * @param $activityId
     * @author Tab
     * @date 2021/11/24 14:04
     */
    public function checkActivity($activityId)
    {
        $activity = LuckyDraw::findOrEmpty($activityId);
        if ($activity->isEmpty()) {
            return '活动不存在';
        }
        return true;
    }
}