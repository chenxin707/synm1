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

namespace app\common\command;

use app\common\enum\ActivityEnum;
use app\common\enum\BargainEnum;
use app\common\model\BargainActivity;
use app\common\model\BargainInitiate;
use app\common\model\GoodsActivity;
use think\console\Command;
use think\console\Input;
use think\console\Output;


class BargainClose extends Command
{
    protected function configure()
    {
        $this->setName('bargain_close')
            ->setDescription('结束过期的砍价活动');
    }

    protected function execute(Input $input, Output $output)
    {
        $lists = BargainActivity::withoutGlobalScope()->where('status', BargainEnum::ACTIVITY_STATUS_ING)->select()->toArray();
        $ids = [];
        $updateActivityData = [];
        $updateInitiateData = [];
        foreach ($lists as $item) {
            if (strtotime($item['end_time']) <= time()) {
                // 活动结束
                $ids[] = $item['id'];
                $updateActivityData[] = ['id' => $item['id'], 'status' => BargainEnum::ACTIVITY_STATUS_END];
            }
        }
        // 结束已过期的砍价活动
        if (count($updateActivityData)) {
            (new BargainActivity)->duokaiSaveAll($updateActivityData, true, false, false);
        }

        // 标记未砍价成功的记录为砍价失败
        $bargainInitiateIds = BargainInitiate::withoutGlobalScope()->where([
            ['activity_id', 'in', $ids],
            ['status', '=', BargainEnum::STATUS_ING]
        ])->column('id');

        foreach ($bargainInitiateIds as $id) {
            $updateInitiateData[] = ['id' => $id, 'status' => BargainEnum::STATUS_FAIL];
        }
        if (count($updateInitiateData)) {
            (new BargainInitiate())->duokaiSaveAll($updateInitiateData, true, false, false);
        }

        // 删除商品活动信息表的数据
        $goodsActivityIds = GoodsActivity::withoutGlobalScope()->where([
            ['activity_type', '=', ActivityEnum::BARGAIN],
            ['activity_id', 'in', $ids],
        ])->column('id');
        if (count($goodsActivityIds)) {
            GoodsActivity::duokaiDestroy($goodsActivityIds, false, false);
        }

        // 标识已过砍价有效期的砍价记录为砍价失败
        $ids = BargainInitiate::withoutGlobalScope()->where([
            ['end_time','<=', time()],
            ['status', '=', BargainEnum::STATUS_ING]
        ])->column('id');
        $updateInitiateData = [];
        foreach($ids as $id) {
            $updateInitiateData[] = ['id' => $id, 'status' => BargainEnum::STATUS_FAIL];
        }
        if (count($updateInitiateData)) {
            (new BargainInitiate())->duokaiSaveAll($updateInitiateData, true, false, false);
        }
    }
}