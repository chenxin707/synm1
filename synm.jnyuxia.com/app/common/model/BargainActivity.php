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

namespace app\common\model;

use app\common\enum\BargainEnum;

class BargainActivity extends BaseModel
{

    /**
     * @notes 开始时间修改器
     * @param $time
     * @return false|int
     * @author Tab
     * @date 2021/8/27 11:32
     */
    public function setStartTimeAttr($time)
    {
        return strtotime($time);
    }

    /**
     * @notes 结束时间修改器
     * @param $time
     * @return false|int
     * @author Tab
     * @date 2021/8/27 11:32
     */
    public function setEndTimeAttr($time)
    {
        return strtotime($time);
    }

    /**
     * @notes 开始时间获取器
     * @param $time
     * @return string
     * @author Tab
     * @date 2021/8/27 14:33
     */
    public function getStartTimeAttr($time)
    {
        return date('Y-m-d H:i:s', $time);
    }

    public function getStartTimeDescAttr($time)
    {
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * @notes 结束时间获取器
     * @param $time
     * @return string
     * @author Tab
     * @date 2021/8/27 14:33
     */
    public function getEndTimeAttr($time)
    {
        return date('Y-m-d H:i:s', $time);
    }

    public function getEndTimeDescAttr($time)
    {
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * @notes 手动结束活动时间
     * @param $time
     * @author Tab
     * @date 2021/9/24 11:23
     */
    public function getCloseTimeAttr($time)
    {
        return is_null($time) ? '' : date('Y-m-d H:i:s', $time);
    }

    /**
     * @notes 活动状态获取器
     * @param $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/27 14:42
     */
    public function getStatusDescAttr($value, $data)
    {
        return BargainEnum::getActivityStatusDesc($data['status']);
    }

    /**
     * @notes 活动时间搜索器
     * @author Tab
     * @date 2021/8/28 11:23
     */
    public function searchActivityTimeAttr($query, $value, $data)
    {
        if (isset($data['start_time']) && isset($data['end_time']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            $startTime = strtotime($data['start_time']);
            $endTime = strtotime($data['end_time']);
            $leftIds = self::where([
                ['start_time', '<=', $startTime],
                ['end_time', '<=', $startTime],
            ])->column('id');
            $rightIds = self::where([
                ['start_time', '>=', $endTime],
                ['end_time', '>=', $endTime],
            ])->column('id');
            $allIds =  array_merge($leftIds, $rightIds);
            $query->where('id', 'not in', $allIds);
        } else if (isset($data['start_time']) && !empty($data['start_time'])) {
            $startTime = strtotime($data['start_time']);
            $query->where('start_time', '<=', $startTime);
        } else if (isset($data['end_time'])  && !empty($data['end_time'])) {
            $endTime = strtotime($data['end_time']);
            $query->where('end_time', '<=', $endTime);
        }
    }

    /**
     * @notes 商品信息搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/8/28 11:39
     */
    public function searchGoodsInfoAttr($query, $value, $data)
    {
        if (isset($data['goods_info']) && !empty($data['goods_info'])) {
            $activityIds = BargainGoods::withoutGlobalScope()
                ->aliasSid('bg')
                ->alias('bg')
                ->leftJoin('goods g', 'g.id = bg.goods_id')
                ->where('g.name|g.code', 'like', '%' . $data['goods_info'] . '%')
                ->column('bg.activity_id');
            $query->where('id', 'in', $activityIds);
        }
    }
}