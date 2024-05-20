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

namespace app\adminapi\validate\marketing;


use app\common\enum\CouponEnum;
use app\common\validate\BaseValidate;

class CouponValidate extends BaseValidate
{
    protected $rule = [
        'id'              => 'require|number',
        'name'            => 'require|min:2',
        'money'           => 'require|float|gt:0',
        'condition_type'  => 'require|number|checkCondition',
        'send_total_type' => 'require|number|checkSendTotalType',
        'use_time_type'   => 'require|checkUseTimeType',
        'get_type'        => 'require|in:1,2',
        'get_num_type'    => 'require|checkGetNumType',
        'use_goods_type'  => 'require|checkUseGoodsType',

        'send_user_num'    => 'require|min:1|max:10',
        'send_user'        => 'require|array',
        'cl_id'            => 'require|array',
    ];

    protected $message = [
        'money.gt' => '面额须大于0'
    ];

    /**
     * @notes 新增场景
     * @return CouponValidate
     * @author 张无忌
     * @date 2021/8/6 20:14
     */
    public function sceneAdd()
    {
        return $this->remove('id', 'require')
            ->remove('send_user_num', 'require')
            ->remove('send_user', 'require')
            ->remove('cl_id', 'require');
    }

    /**
     * @notes 编辑场景
     * @return CouponValidate
     * @author 张无忌
     * @date 2021/8/6 20:14
     */
    public function sceneEdit()
    {
        return $this->remove('send_user_num', 'require')
            ->remove('send_user', 'require')
            ->remove('cl_id', 'require');
    }

    /**
     * @notes 作废场景
     * @author 张无忌
     * @date 2021/8/6 20:14
     */
    public function sceneVoid()
    {
        return $this->only(['cl_id']);
    }


    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneSend()
    {
        return $this->only(['send_user_num', 'send_user']);
    }

    /**
     * @notes 验证使用条件参数
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     * @author 张无忌
     * @date 2021/7/20 10:05
     */
    public function checkCondition($value, $rule, $data)
    {
        unset($value);
        unset($rule);
        if ($data["condition_type"] == CouponEnum::CONDITION_TYPE_FULL) {
            if (empty($data['condition_money'])) {
                return '请填写使用条件金额';
            }
            if (!is_numeric($data['condition_money'])) {
                return "使用条件金额请填写数字";
            }
            if ($data['condition_money'] <= 0) {
                return "使用条件金额需大于0元";
            }

        }
        return true;
    }


    /**
     * @notes 验证发放数量条件
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/20 10:12
     */
    public function checkSendTotalType($value, $rule, $data)
    {
        unset($value);
        unset($rule);
        if ($data["send_total_type"] == CouponEnum::SEND_TOTAL_TYPE_FIXED) {
            if (is_numeric($data['send_total']) and $data['send_total'] >= 0) {
                return true;
            }
            return "请填写发放数量";
        }
        return true;
    }

    /**
     * @notes 验证使用时间
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/20 14:14
     */
    public function checkUseTimeType($value, $rule, $data)
    {
        unset($value);
        unset($rule);
        if ($data["use_time_type"] == CouponEnum::USE_TIME_TYPE_FIXED) {
            $start = strtotime($data['use_time_start']);
            $end = strtotime($data['use_time_end']);
            if ($start >= $end) {
                return "用券开始时间必须小于结束时间";
            }
            if ($end <= time()) {
                return "用券结束时间必须大于当前时间";
            }
        } elseif ($data["use_time_type"] == CouponEnum::USE_TIME_TYPE_TODAY
            or $data["use_time_type"] == CouponEnum::USE_TIME_TYPE_TOMORROW) {
            if (!is_numeric($data['use_time'])) {
                return '用券天数必须为数字';
            }
            if ($data['use_time'] <= 0) {
                return '用券天数必须大于0';
            }
        }

        return true;
    }

    /**
     * @notes 验证领取数量限制
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/20 14:20
     */
    public function checkGetNumType($value, $rule, $data)
    {
        unset($value);
        unset($rule);
        if ($data["get_num_type"] == CouponEnum::GET_NUM_TYPE_LIMIT
            or $data["get_num_type"] == CouponEnum::GET_NUM_TYPE_DAY) {

            if (empty($data['get_num'])) {
                return "请填写领取数量";
            }

            if (!is_numeric($data['get_num'])) {
                return "领取数量必须为数字";
            }
            if ($data['get_num'] < 0) {
                return "领取数量需大于0";
            }
        }
        return true;
    }

    /**
     * @notes 验证允许使用的商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/20 14:32
     */
    public function checkUseGoodsType($value, $rule, $data)
    {
        unset($value);
        unset($rule);
        if ($data["use_goods_type"] == CouponEnum::USE_GOODS_TYPE_ALLOW
            or $data["use_goods_type"] == CouponEnum::USE_GOODS_TYPE_BAN) {

            if (empty($data['use_goods_ids'])) {
                return '请选择商品';
            }

            if (!is_array($data['use_goods_ids'])) {
                return "允许使用商品参数有误,必须为数组格式";
            }

            foreach ($data['use_goods_ids'] as $id) {
                if (!is_numeric($id)) {
                    return "选择的商品异常";
                }
            }
        }
        return true;
    }
}