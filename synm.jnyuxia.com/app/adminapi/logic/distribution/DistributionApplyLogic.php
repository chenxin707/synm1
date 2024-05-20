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

namespace app\adminapi\logic\distribution;

use app\common\enum\DistributionApplyEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\DistributionApply;
use app\common\model\Distribution;
use app\common\model\UserLevel;
use app\common\service\RegionService;
use think\facade\Db;

/**
 * 分销申请逻辑层
 * Class DistributionApplyLogic
 * @package app\adminapi\logic\distribution
 */
class DistributionApplyLogic extends BaseLogic
{
    /**
     * @notes 查看分销申请详情
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/7/27 15:49
     */
    public static function detail($params)
    {
        $field = 'da.real_name, da.mobile, da.province, da.city, da.district, da.reason, da.status as status_desc, da.audit_remark, da.update_time';
        $field .= ',u.sn, u.nickname, u.level';

        $detail =DistributionApply::withoutGlobalScope()
            ->aliasSid('da')
            ->alias('da')
            ->leftJoin('user u', 'u.id = da.user_id')
            ->field($field)
            ->findOrEmpty($params['id'])
            ->toArray();

        if($detail) {
            $detail['address'] = RegionService::getAddress([$detail['province'], $detail['city'], $detail['district']]);
            $detail['level_name'] = UserLevel::getLevelName($detail['level']);
        }

        return $detail;
    }

    /**
     * @notes 审核通过
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/7/27 16:00
     */
    public static function pass($params)
    {
        Db::startTrans();
        try {
            // 更新【分销申请表】状态
            $distributionAplly = DistributionApply::where('id', $params['id'])->findOrEmpty();
            $distributionAplly->status = DistributionApplyEnum::AUDIT_PASS;
            $distributionAplly->audit_remark = $params['audit_remark'] ?? '';
            $distributionAplly->duokaiSave();
            // 更新【分销基础信息表】状态
            Distribution::where('user_id', $distributionAplly['user_id'])
                ->update(['is_distribution' => YesNoEnum::YES, 'distribution_time' => time()]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 审核拒绝
     * @param $params
     * @author Tab
     * @date 2021/7/27 16:07
     */
    public static function refuse($params)
    {
        $data = [
            'id' => $params['id'],
            'status' => DistributionApplyEnum::AUDIT_REFUSE,
            'audit_remark' => $params['audit_remark'] ?? ''
        ];
        DistributionApply::duokaiUpdate($data, [], [], '', false);
    }
}