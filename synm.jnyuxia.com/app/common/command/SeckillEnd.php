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
use app\common\enum\SeckillEnum;
use app\common\model\GoodsActivity;
use app\common\model\SeckillActivity;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class SeckillEnd extends Command
{

    protected function configure()
    {
        $this->setName('seckill_end')
            ->setDescription('结束过期的秒杀活动');
    }

    protected function execute(Input $input, Output $output)
    {
        // 查找已过期的秒杀活动
        $ids = SeckillActivity::withoutGlobalScope()
            ->where([
            ['status', '=', SeckillEnum::SECKILL_STATUS_CONDUCT],
            ['end_time', '<=', time()]
        ])->column('id');

        // 结束已过期的秒杀活动
        if (count($ids)) {
            SeckillActivity::duokaiDestroy($ids, false, false);
        }
        // 删除商品活动信息表的数据
        $goodsActivityIds = GoodsActivity::withoutGlobalScope()
            ->where([
            ['activity_type', '=', ActivityEnum::SECKILL],
            ['activity_id', 'in', $ids],
        ])->column('id');
        if (count($goodsActivityIds)) {
            GoodsActivity::duokaiDestroy($goodsActivityIds, false, false);
        }
    }
}