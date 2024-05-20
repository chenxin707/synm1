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

use app\common\enum\LuckyDrawEnum;

class LuckyDraw extends BaseModel
{
    /**
     * @notes 获取状态描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author Tab
     * @date 2021/11/24 14:16
     */
    public function getStatusDescAttr($value, $data)
    {
        return LuckyDrawEnum::getStatusDesc($data['status']);
    }

    /**
     * @notes 获取参与人次
     * @param $value
     * @param $data
     * @return int
     * @author Tab
     * @date 2021/11/24 15:42
     */
    public function getJoinNumAttr($value, $data)
    {
       return LuckyDrawRecord::where('activity_id', $data['id'])->count();
    }

    /**
     * @notes 获取中奖人次
     * @param $value
     * @param $data
     * @return int
     * @author Tab
     * @date 2021/11/24 15:43
     */
    public function getWinNumAttr($value, $data)
    {
        return LuckyDrawRecord::where([
            ['activity_id', '=' , $data['id']],
            ['prize_type', '<>' , LuckyDrawEnum::NOT_WIN],
        ])->count();
    }

    /**
     * @notes 获取开始时间描述
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/11/24 15:43
     */
    public function getStartTimeDescAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['start_time']);
    }

    /**
     * @notes 获取结束时间描述
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/11/24 15:43
     */
    public function getEndTimeDescAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['end_time']);
    }
}