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

namespace app\adminapi\validate\free_shipping;

use app\common\enum\FreeShippingEnum;
use app\common\model\FreeShipping;
use app\common\model\Region;
use app\common\validate\BaseValidate;

/**
 * 包邮活动
 */
class FreeShippingValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkActivity',
        'name' => 'require|max:120',
        'start_time' => 'require|dateFormat:Y-m-d H:i:s',
        'end_time' => 'require|dateFormat:Y-m-d H:i:s|checkTime',
        'target_user_type' => 'require|in:1',
        'target_goods_type' => 'require|in:1',
        'condition_type' => 'require|in:1,2',
        'region' => 'require|array|checkRegion',

    ];

    protected $message = [
        'id.require' => 'id参数缺失',
        'name.require' => '请输入活动名称',
        'name.max' => '活动名称不能超过120个字符',
        'start_time.require' => '请选择开始时间',
        'start_time.dateFormat' => '开始时间格式错误，须为Y-m-d H:i:s',
        'end_time.require' => '请选择结束时间',
        'end_time.dateFormat' => '结束时间格式错误，须为Y-m-d H:i:s',
        'target_user_type.require' => '请选择活动对象类型',
        'target_user_type.in' => '非法的活动对象类型',
        'target_goods_type.require' => '请选择活动商品类型',
        'target_goods_type.in' => '非法的活动商品类型',
        'condition_type.require' => '请选择活动规则类型',
        'condition_type.in' => '非法的活动规则类型',
        'region.require' => '请填写包邮信息',
        'region.array' => '包邮信息须为数组格式',
    ];

    /**
     * @notes 添加场景
     */
    public function sceneAdd()
    {
        return $this->only(['name', 'start_time', 'end_time', 'target_user_type', 'target_goods_type', 'condition_type', 'region']);
    }

    /**
     * @notes 详情场景
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 开始活动场景
     */
    public function sceneStart()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 结束活动场景
     */
    public function sceneEnd()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 删除活动场景
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 编辑场景
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'start_time', 'end_time', 'target_user_type', 'target_goods_type', 'condition_type', 'region']);
    }

    /**
     * @notes 验证活动时间
     */
    public function checkTime($region, $rule, $data) {
        $startTime = strtotime($data['start_time']);
        $endTime = strtotime($data['end_time']);
        if ($startTime >= $endTime) {
            return '开始时间须小于结束时间';
        }
        $where = [
            ['status', 'in', [FreeShippingEnum::WAIT, FreeShippingEnum::ING]]
        ];
        if (isset($data['id'])) {
            // 编辑场景
            $where[] = ['id', '<>', $data['id']];
        }
        $activities = FreeShipping::field('start_time, end_time')
            ->where($where)
            ->select()
            ->toArray();
        if (!$activities) {
            // 未有活动的情况
            return true;
        }
        foreach($activities as $item) {
            if (
                ($startTime >= strtotime($item['start_time']) && $startTime <= strtotime($item['end_time']))
            ||
                ($endTime >= strtotime($item['start_time']) && $endTime <= strtotime($item['end_time']))
            ) {
                return '所选活动时间与现有活动有重叠';
            }
        }
        return true;
    }

    /**
     * @notes 验证包邮规则
     */
    public function checkRegion($region, $rule, $data) {
        $regonIds = Region::column('id');
        foreach($region as $key =>  $item) {
            $item = (array)$item;
            if (!isset($item['region_id']) || !isset($item['region_name']) || !isset($item['threshold'])) {
                return '规则'. ($key + 1) . '参数缺失' ;
            }
            if (!is_numeric($item['threshold'])) {
                if ($data['condition_type'] == 1) {
                    return '规则'. ($key + 1) . '订单金额须为数字';
                } else {
                    return '规则'. ($key + 1) . '购买件数须为数字';
                }
            }
            if ($item['threshold'] < 0) {
                if ($data['condition_type'] == 1) {
                    return '规则' . ($key + 1) . '订单金额不能小于0';
                } else {
                    return '规则' . ($key + 1) . '购买件数不能小于0';
                }
            }
            $targetRegion = explode(',', $item['region_id']);
            $interset = array_intersect($regonIds, $targetRegion);
            $targetRegionLen = count($targetRegion);
            $interset = count($interset);
            if ($targetRegionLen != $interset) {
                return '规则'. ($key + 1) . '存在无法识别的区域';
            }
        }
        return true;
    }

    /**
     * @notes 验证活动
     */
    public function checkActivity($activityId)
    {
        $activity = FreeShipping::findOrEmpty($activityId);
        if ($activity->isEmpty()) {
            return '活动不存在';
        }
        return true;
    }
}
