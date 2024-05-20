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

namespace app\adminapi\logic\free_shipping;

use app\common\enum\FreeShippingEnum;
use app\common\logic\BaseLogic;
use app\common\model\FreeShipping;

/**
 * 包邮活动
 */
class FreeShippingLogic extends BaseLogic
{

    /**
     * @notes 添加包邮活动
     */
    public static function add($params)
    {
        try {
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            $params['status'] = FreeShippingEnum::WAIT;
            $params['region'] = json_encode($params['region']);
            return FreeShipping::duokaiCreate($params);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 包邮活动详情
     */
    public static function detail($params)
    {
        $activity = FreeShipping::withoutField('create_time,update_time,delete_time')
            ->findOrEmpty($params['id'])->toArray();

        return $activity;
    }

    /**
     * @notes 编辑包邮活动
     */
    public static function edit($params)
    {
        try {
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            $params['region'] = json_encode($params['region']);
            $activity = FreeShipping::findOrEmpty($params['id']);
            switch ($activity->getData('status')) {
                case FreeShippingEnum::WAIT:
                    FreeShipping::duokaiUpdate($params);
                    break;
                case FreeShippingEnum::ING:
                    // 只允许编辑名称
                    FreeShipping::duokaiUpdate(['id' => $params['id'], 'name'=> $params['name']]);
                    break;
                case FreeShippingEnum::END:
                    throw new \Exception('进行中和已结束的活动不允许编辑');
            }

            return true;
        } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 开始包邮活动
     */
    public static function start($params)
    {
        try {
            $activity = FreeShipping::findOrEmpty($params['id']);
            if ($activity->getData('status') != FreeShippingEnum::WAIT) {
                throw new \Exception('只有未开始的活动才能进行开始操作');
            }
            $activity->status = FreeShippingEnum::ING;
            $activity->duokaiSave();
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 结束包邮活动
     */
    public static function end($params)
    {
        try {
            $activity = FreeShipping::findOrEmpty($params['id']);
            if ($activity->getData('status') != FreeShippingEnum::ING) {
                throw new \Exception('只有进行中的活动才能结束');
            }
            $activity->status = FreeShippingEnum::END;
            $activity->duokaiSave();
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除包邮活动
     */
    public static function delete($params)
    {
        try {
            FreeShipping::duokaiDestroy($params['id']);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}
