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

namespace app\adminapi\validate\bargain;

use app\common\enum\BargainEnum;
use app\common\enum\GoodsEnum;
use app\common\model\BargainActivity;
use app\common\model\Goods;
use app\common\validate\BaseValidate;

/**
 * 砍价活动验证器
 * Class BargainActivityValidate
 * @package app\adminapi\validate\bargain
 */
class BargainActivityValidate extends BaseValidate
{
    protected $rule = [
        'goods_ids' => 'require|array|checkCount',
        'name' => 'require|length:1,100',
        'start_time' => 'require|checkTime',
        'end_time' => 'require|checkTime',
        'remark' => 'length:1,100',
//        'is_distribution' => 'require|in:0,1',
        'buy_condition' => 'require|in:1,2',
        'valid_period' => 'require|integer|gt:0',
        'help_num' => 'require|integer|gt:0',
        'knife_amount_type' => 'require|in:1,2',
        'self' => 'require|in:0,1',
        'count' => 'require|integer|gt:0',
//        'buy_limit' => 'require|integer',
        'order_limit' => 'require|integer',
//        'use_coupon' => 'require|in:0,1',
        'goods' => 'require|array|checkGoods',
        'id' => 'require'
    ];

    protected $message = [
        'goods_ids.require' => '请选择商品',
        'goods_ids.array' => '商品数据须为数组格式',
        'name.require' => '请输入砍价活动名称',
        'name.length' => '砍价活动名称长度过长',
        'start_time.require' => '请选择活动开始时间',
        'end_time.require' => '请选择活动结束时间',
        'remark.length' => '备注长度过长',
        'is_distribution.require' => '请选择是否参与分销',
        'is_distribution.in' => '分销状态值错误',
        'buy_condition.require' => '请选择购买条件',
        'buy_condition.in' => '购买条件状态值错误',
        'valid_period.require' => '请填写有效期',
        'valid_period.integer' => '有效期须为整型',
        'valid_period.gt' => '有效期须大于0',
        'help_num.require' => '请填写帮砍人数',
        'help_num.integer' => '帮砍人数须为整型',
        'help_num.gt' => '帮砍人数须大于0',
        'knife_amount_type.require' => '请选择每刀金额类型',
        'knife_amount_type.in' => '每刀金额类型值有误',
        'self.require' => '请选择自己是否能砍价',
        'self.in' => '自已砍价状态值错误',
        'count.require' => '请填写最大可发起砍价次数',
        'count.integer' => '最大可发起砍价次数须为整型',
        'count.gt' => '最大可发起砍价次数须大于0',
        'buy_limit.require' => '请选择起购限制',
        'buy_limit.integer' => '起购限制值须为整型',
        'order_limit.require' => '请选择每单限制',
        'order_limit.integer' => '每单限制须为整型',
        'use_coupon.require' => '请选择是否可使用优惠券',
        'use_coupon.in' => '优惠券类型状态值错误',
        'goods.require' => '请选择活动商品',
        'goods.array' => '活动商品格式须为数组',
        'id.require' => '参数缺失',
    ];

    /**
     * @notes 选择商品场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/26 16:15
     */
    public function sceneChooseGoods()
    {
        return $this->only(['goods_ids']);
    }

    /**
     * @notes 添加砍价活动场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 11:00
     */
    public function sceneAdd()
    {
        return $this->remove('goods_ids', 'require|array|checkCount')
            ->remove('id', 'require');
    }

    /**
     * @notes 查看砍价详情场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 11:57
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 编辑场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 15:12
     */
    public function sceneEdit()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 编辑进行中的砍价活动场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 16:01
     */
    public function sceneEditIng()
    {
        return $this->only(['id', 'name', 'end_time', 'remark']);
    }

    /**
     * @notes 编辑未开始的砍价活动场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 16:18
     */
    public function sceneEditWait()
    {
        return $this->remove('goods_ids', 'require|array|checkCount');
    }

    /**
     * @notes 确认砍价活动场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 17:10
     */
    public function sceneConfirm()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 结束砍价活动场景
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 17:27
     */
    public function sceneStop()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 删除砍价活动
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/8/27 17:40
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 活动数据
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/9/23 18:54
     */
    public function sceneActivityData()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 活动记录
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/9/24 15:37
     */
    public function sceneActivityRecord()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 结束砍价记录
     * @return BargainActivityValidate
     * @author Tab
     * @date 2021/9/24 18:11
     */
    public function sceneStopInitiate()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验所选商品数量
     * @param $goodsIds
     * @return bool|string
     * @author Tab
     * @date 2021/8/26 16:03
     */
    public function checkCount($goodsIds)
    {
        if(count($goodsIds) > 25) {
            return '最多可选25个商品';
        }
        return true;
    }

    /**
     * @notes 校验时间格式
     * @param $time
     * @return bool|string
     * @author Tab
     * @date 2021/8/27 10:19
     */
    public function checkTime($time)
    {
        if(!strtotime($time)) {
            return '时间格式错误';
        }
        return true;
    }

    /**
     * @notes 校验参与活动的砍价商品
     * @param $goods
     * @return bool|string
     * @author Tab
     * @date 2021/8/27 14:28
     */
    public function checkGoods($goods, $rule, $data)
    {
        if(!count($goods) > 25) {
            return '最多可选25个商品';
        }
        $goodsIds = array_column($goods, 'goods_id');
        $where = [
            ['ba.status', 'in', [BargainEnum::ACTIVITY_STATUS_ING, BargainEnum::ACTIVITY_STATUS_WAIT]],
            ['bg.goods_id', 'in', $goodsIds],
        ];
        // 编辑时排除当前活动
        if (isset($data['id'])) {
            $where[] = ['ba.id', '<>', $data['id']];
        }
        $goodsVirtual = Goods::where(['id'=>$goodsIds,'type'=>GoodsEnum::GOODS_VIRTUAL])->find();
        if($goodsVirtual){
            return '虚拟商品不能参加营销活动';
        }
        $count = BargainActivity::withoutGlobalScope()
            ->aliasSid('ba')
            ->alias('ba')
            ->leftJoin('bargain_goods bg', 'bg.activity_id = ba.id')
            ->where($where)
            ->count();
        if($count > 0) {
            return '部分商品正在参与砍价活动中';
        }
        return true;
    }
}