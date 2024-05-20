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

namespace app\adminapi\logic\lucky_draw;

use app\common\enum\LuckyDrawEnum;
use app\common\logic\BaseLogic;
use app\common\model\LuckyDraw;
use app\common\model\LuckyDrawPrize;
use think\facade\Db;

/**
 * 幸运抽奖
 */
class LuckyDrawLogic extends BaseLogic
{
    /**
     * @notes 获取奖品类型
     * @author Tab
     * @date 2021/11/24 9:50
     */
    public static function getPrizeType()
    {
        $data = [];
        $prizeTypes = LuckyDrawEnum::getPrizeTypeDesc();
        foreach ($prizeTypes as $key => $value) {
            $temp['value'] = $key;
            $temp['label'] = $value;
            $data[] = $temp;
        }
        return $data;
    }

    /**
     * @notes 添加幸运抽奖活动
     * @param $params
     * @author Tab
     * @date 2021/11/24 11:06
     */
    public static function add($params)
    {
        Db::startTrans();
        try {
            // 添加活动
            $activity = self::addActivity($params);
            // 添加奖品
            self::addPrizes($params, $activity->id);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 添加活动
     * @param $params
     * @author Tab
     * @date 2021/11/24 11:37
     */
    private static function addActivity($params)
    {
        unset($params['prizes']);
        $params['start_time'] = strtotime($params['start_time']);
        $params['end_time'] = strtotime($params['end_time']);
        $params['sn'] = generate_sn((new LuckyDraw()), 'sn');
        $params['remark'] = $params['remark'] ?? '';
        $params['status'] = LuckyDrawEnum::WAIT;
        return LuckyDraw::duokaiCreate($params);
    }

    /**
     * @notes 添加奖品
     * @param $params
     * @param $activityId
     * @author Tab
     * @date 2021/11/24 11:50
     */
    private static function addPrizes($params, $activityId)
    {
        $data = [];
        foreach ($params['prizes'] as $key => $item) {
            $item['activity_id'] = $activityId;
            $item['location'] = $key + 1;
            $item['probability'] = (int)(round($item['probability'], 2) * 100);
            $data[] = $item;
        }
        $prizeModel = new LuckyDrawPrize();
        $prizeModel->duokaiSaveAll($data);
    }

    /**
     * @notes 查看幸运抽奖活动详情
     * @param $params
     * @author Tab
     * @date 2021/11/24 14:06
     */
    public static function detail($params)
    {
        $activity = LuckyDraw::withoutField('create_time,update_time,delete_time')
            ->append(['status_desc', 'start_time_desc', 'end_time_desc'])
            ->findOrEmpty($params['id'])->toArray();
        $activity['prizes'] = LuckyDrawPrize::withoutField('create_time,update_time,delete_time')
            ->append(['type_desc', 'probability_desc'])
            ->where('activity_id', $activity['id'])->select()->toArray();

        return $activity;
    }

    /**
     * @notes 编辑幸运抽奖活动
     * @param $params
     * @author Tab
     * @date 2021/11/24 14:23
     */
    public static function edit($params)
    {
        Db::startTrans();
        try {
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            $activity = LuckyDraw::findOrEmpty($params['id']);
            switch ($activity->status) {
                case LuckyDrawEnum::WAIT:
                    self::waitEdit($params);
                    break;
                case LuckyDrawEnum::ING:
                    self::ingEdit($activity, $params);
                    break;
                case LuckyDrawEnum::END:
                    throw new \Exception('已结束的活动不允许编辑');
            }

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 编辑未开始的幸运抽奖
     * @author Tab
     * @date 2021/11/24 14:28
     */
    private static function waitEdit($params)
    {
        $prizes = $params['prizes'];
        unset($params['prizes']);

        // 更新活动表
        LuckyDraw::duokaiUpdate($params, [], [], '', false);

        foreach ($prizes as &$item) {
            $item['probability'] = (int)(round($item['probability_desc'], 2) * 100);
        }
        // 更新奖品表
        $prizeModel =  new LuckyDrawPrize();
        $prizeModel->duokaiSaveAll($prizes,true,false,false);
    }

    /**
     * @notes 编辑进行中的幸运抽奖
     * @author Tab
     * @date 2021/11/24 14:28
     */
    private static function ingEdit($activity, $params)
    {
        // 进行中的只允许更新活动名称及时间
        $activity->name = $params['name'];
        $activity->start_time = $params['start_time'];
        $activity->end_time = $params['end_time'];
        $activity->duokaiSave();
    }

    /**
     * @notes 开始幸运抽奖活动
     * @param $params
     * @author Tab
     * @date 2021/11/24 14:59
     */
    public static function start($params)
    {
        try {
            $activity = LuckyDraw::findOrEmpty($params['id']);
            if ($activity->status != LuckyDrawEnum::WAIT) {
                throw new \Exception('只有未开始的活动才能进行开始操作');
            }
            $activity->status = LuckyDrawEnum::ING;
            $activity->duokaiSave();
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 结束活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/24 15:05
     */
    public static function end($params)
    {
        try {
            $activity = LuckyDraw::findOrEmpty($params['id']);
            if ($activity->status != LuckyDrawEnum::ING) {
                throw new \Exception('只有进行中的活动才能结束');
            }
            $activity->status = LuckyDrawEnum::END;
            $activity->remark = $activity->remark . ' 后台结束活动';
            $activity->duokaiSave();
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除幸运抽奖活动
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/24 15:15
     */
    public static function delete($params)
    {
        try {
            LuckyDraw::duokaiDestroy($params['id']);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}