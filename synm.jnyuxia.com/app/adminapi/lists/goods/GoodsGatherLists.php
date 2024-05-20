<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\adminapi\lists\goods;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\YesNoEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\GoodsGather;

class GoodsGatherLists extends BaseAdminDataLists implements ListsExtendInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2023/3/14 9:59 上午
     */
    public function where()
    {
        $where[] = ['log_id','=',$this->params['log_id']];
        $status = (isset($this->params['status']) && $this->params['status'] != '') ? $this->params['status'] : 0;
        $where[] = ['status','=',$status];

        return $where;
    }

    /**
     * @notes 商品采集列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2023/3/14 10:02 上午
     */
    public function lists(): array
    {
        $lists = GoodsGather::field('id,gather_url,gather_info,gather_status,channel,create_time,status')
            ->append(['goods_info','gather_status_desc','channel_desc'])
            ->hidden(['gather_info','channel'])
            ->where(self::where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 商品采集数量
     * @return int
     * @author ljj
     * @date 2023/3/14 10:03 上午
     */
    public function count(): int
    {
        return GoodsGather::where(self::where())->count();
    }

    /**
     * @notes 数据统计
     * @return array
     * @author ljj
     * @date 2023/3/14 10:06 上午
     */
    public function extend()
    {
        return [
            'wait' => GoodsGather::where(['log_id'=>$this->params['log_id'],'status'=>YesNoEnum::NO])->count(),
            'already' => GoodsGather::where(['log_id'=>$this->params['log_id'],'status'=>YesNoEnum::YES])->count(),
        ];
    }
}