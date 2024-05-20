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

namespace app\adminapi\logic;


use app\common\enum\SeckillEnum;
use app\common\enum\TeamEnum;
use app\common\logic\BaseLogic;
use app\common\model\BargainActivity;
use app\common\model\BargainGoods;
use app\common\model\SeckillActivity;
use app\common\model\SeckillGoods;
use app\common\model\TeamActivity;
use app\common\model\TeamGoods;
use app\common\service\FileService;

class CommonLogic extends BaseLogic
{
    public static function getActivity($get, $type)
    {
        $where = [];
        if (!empty($get['keyword']) and $get['keyword']) {
            $where[] = ['name', 'like', '%'.$get['keyword'].'%'];
        }

        switch ($type) {
            case 'team':
                $count = (new TeamActivity())->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])->count();

                $lists = (new TeamActivity())
                    ->field('id,name,start_time,end_time,status')
                    ->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])
                    ->where($where)
                    ->page($get['page_no'] ?? 1, $get['page_size'] ?? 25)
                    ->select()
                    ->toArray();

                foreach ($lists as &$item) {
                    $item['status_text'] = TeamEnum::getActivityStatusDesc($item['status']);
                    $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
                    $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
                    $item['goods_num'] = (new TeamGoods())->where(['team_id'=>$item['id']])->count();
                }

                return [
                    'count'     => $count,
                    'lists'     => $lists,
                    'page_no'   => $get['page_no'] ?? 1,
                    'page_size' => $get['page_no'] ?? 25
                ];

                break;
            case 'seckill':
                $count = (new SeckillActivity()) ->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])->count();

                $lists = (new SeckillActivity())
                    ->field('id,name,start_time,end_time,status')
                    ->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])
                    ->where($where)
                    ->select()
                    ->toArray();

                foreach ($lists as &$item) {
                    $item['status_text'] = SeckillEnum::getSeckillStatusDesc($item['status']);
                    $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
                    $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
                    $item['goods_num'] = (new SeckillGoods())->where(['seckill_id'=>$item['id']])->count();
                }

                return [
                    'count'     => $count,
                    'lists'     => $lists,
                    'page_no'   => $get['page_no'] ?? 1,
                    'page_size' => $get['page_no'] ?? 25
                ];
                break;
            case 'bargain':
                $count = (new BargainActivity())->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])->count();

                $lists = (new BargainActivity())
                    ->field('id,name,start_time,end_time,status')
                    ->where(['status' => TeamEnum::TEAM_STATUS_CONDUCT])
                    ->where($where)
                    ->select()
                    ->toArray();

                foreach ($lists as &$item) {
                    $item['status_text'] = SeckillEnum::getSeckillStatusDesc($item['status']);
                    $item['goods_num'] = (new BargainGoods())->where(['activity_id'=>$item['id']])->count();
                }

                return [
                    'count'     => $count,
                    'lists'     => $lists,
                    'page_no'   => $get['page_no'] ?? 1,
                    'page_size' => $get['page_no'] ?? 25
                ];
                break;
        }

        return [];
    }


    /**
     * @notes 获取活动商品列表数据
     * @param $type
     * @param $activity_id
     * @param $keyword
     * @return array
     * @author 张无忌
     * @date 2021/10/9 18:41
     */
    public static function getActivityGoods($type, $activity_id, $keyword='')
    {
        switch ($type) {
            case 'team':
                $where = [];
                if ($keyword) {
                    $where[] = ['G.name', 'like', '%'.$keyword.'%'];
                }

                $lists = (new TeamGoods())->duokaiWithoutGlobalScope()
                    ->aliasSid('TG')
                    ->alias('TG')
                    ->field('TG.*,G.total_stock,G.name,G.image,G.min_price,G.max_price')
                    ->join('goods G', 'G.id = TG.goods_id')
                    ->where('TG.team_id', '=', intval($activity_id))
                    ->where($where)
                    ->select()->toArray();

                $data = [];
                foreach ($lists as $item) {
                    $data[] = [
                        'id' => $item['id'],
                        'goods_id'    => $item['goods_id'],
                        'name'        => $item['name'],
                        'image'       => FileService::getFileUrl($item['image']),
                        'total_stock' => $item['total_stock'],
                        'min_price'   => $item['min_price'],
                        'max_price'   => $item['max_price'],
                        'min_activity_price' => $item['min_team_price'],
                        'max_activity_price' => $item['max_team_price']
                    ];
                }

                return $data;
                break;
            case 'seckill':
                $where = [];
                if ($keyword) {
                    $where[] = ['G.name', 'like', '%'.$keyword.'%'];
                }

                $lists = (new SeckillGoods())->duokaiWithoutGlobalScope()
                    ->aliasSid('SG')
                    ->alias('SG')
                    ->field('SG.*,G.total_stock,G.name,G.image,G.min_price,G.max_price')
                    ->join('goods G', 'G.id = SG.goods_id')
                    ->where('SG.seckill_id', '=', intval($activity_id))
                    ->where($where)
                    ->select()->toArray();

                $data = [];
                foreach ($lists as $item) {
                    $data[] = [
                        'id' => $item['id'],
                        'goods_id'    => $item['goods_id'],
                        'name'        => $item['name'],
                        'image'       => FileService::getFileUrl($item['image']),
                        'total_stock' => $item['total_stock'],
                        'min_price'   => $item['min_price'],
                        'max_price'   => $item['max_price'],
                        'min_activity_price' => $item['min_seckill_price'],
                        'max_activity_price' => $item['max_seckill_price'],
                    ];
                }

                return $data;
                break;
            case 'bargain':
                $where = [];
                if ($keyword) {
                    $where[] = ['G.name', 'like', '%'.$keyword.'%'];
                }

                $lists = (new BargainGoods())->duokaiWithoutGlobalScope()
                    ->aliasSid('BG')
                    ->alias('BG')
                    ->field('BG.*,G.total_stock,G.name,G.image,G.min_price,G.max_price')
                    ->join('goods G', 'G.id = BG.goods_id')
                    ->where('BG.activity_id', '=', intval($activity_id))
                    ->where($where)
                    ->select()->toArray();

                $data = [];
                foreach ($lists as $item) {
                    $data[] = [
                        'id' => $item['id'],
                        'goods_id'    => $item['goods_id'],
                        'name'        => $item['name'],
                        'image'       => FileService::getFileUrl($item['image']),
                        'total_stock' => $item['total_stock'],
                        'min_price'   => $item['min_price'],
                        'max_price'   => $item['max_price'],
                        'min_activity_price' => $item['min_price'],
                        'max_activity_price' => $item['max_price']
                    ];
                }
                return $data;
                break;

        }

        return [];
    }
}