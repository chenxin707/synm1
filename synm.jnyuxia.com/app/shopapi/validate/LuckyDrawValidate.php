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

namespace app\shopapi\validate;

use app\common\enum\LuckyDrawEnum;
use app\common\model\LuckyDraw;
use app\common\model\LuckyDrawRecord;
use app\common\model\User;
use app\common\validate\BaseValidate;

class LuckyDrawValidate extends BaseValidate
{
    protected $rule = [
        'id'      => 'require|checkActivity|checkUser',
    ];

    protected $message = [
        'id.require'      => '参数缺失',
    ];


    public function sceneLottery()
    {
        return $this->only(['id']);
    }

    public function sceneRecord()
    {
        return $this->only(['id'])
            ->remove('id', 'checkActivity|checkUser');
    }

    public function sceneActivity()
    {
        return $this->only(['id'])
            ->remove('id', 'checkActivity|checkUser');
    }

    public function sceneWinningList()
    {
        return $this->only(['id'])
            ->remove('id', 'checkActivity|checkUser');
    }

    /**
     * @notes 校验活动
     * @param $id
     * @return bool|string
     * @author Tab
     * @date 2021/11/24 17:18
     */
    public function checkActivity($value, $rule, $data)
    {

        $activity = LuckyDraw::findOrEmpty($data['id']);
        if ($activity->isEmpty()) {
            return '活动不存在';
        }
        if ($activity->status != LuckyDrawEnum::ING) {
            return '活动未开始或已结束';
        }
        if ($activity->start_time > time()) {
            return '活动未开始';
        }
        if ($activity->end_time <= time()) {
            return '活动已结束';
        }
        return true;
    }

    /**
     * @notes 校验用户
     * @author Tab
     * @date 2021/11/24 17:22
     */
    public function checkUser($value, $rule, $data)
    {
        $activity = LuckyDraw::findOrEmpty($data['id'])->toArray();
        $user = User::findOrEmpty($data['user_id']);
        if ($user->isEmpty()) {
            return '用户不存在';
        }
        $user = $user->toArray();
        // 校验参与次数
        if ($activity['frequency_type'] == 1 && (int)$activity['frequency'] <= $this->joinCount($data)) {
            return '今天抽奖次数已用完';
        }
        if ((int)$user['user_integral'] < $activity['need_integral']) {
            return '积分不足';
        }
        return true;
    }

    /**
     * @notes 当天参与次数
     * @param $userId
     * @author Tab
     * @date 2021/11/24 17:36
     */
    public function joinCount($data)
    {
        return  LuckyDrawRecord::whereDay('create_time')
            ->where('user_id', $data['user_id'])
            ->where('activity_id', $data['id'])
            ->count();
    }
}