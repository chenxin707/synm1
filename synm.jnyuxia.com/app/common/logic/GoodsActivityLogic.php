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

namespace app\common\logic;

use app\common\enum\ActivityEnum;
use app\common\model\GoodsActivity;

/**
 * 商品活动信息
 */
class GoodsActivityLogic extends BaseLogic
{
    /**
     * @notes 获取商品活动信息
     * @param $goodsIds
     * @param $type 活动类型
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/10/11 18:16
     */
    public static function activityInfo($goodsIds = [],$type = 0)
    {
        // 转数组
        if (!is_array($goodsIds)) {
            $goodsIds = [$goodsIds];
        }
        $where = [];
        if($goodsIds){
            $where[] = ['goods_id','in',$goodsIds];
        }
        if($type){
            $where[] = ['activity_type','=',$type];
        }
        // 获取活动信息
       $lists =  GoodsActivity::where($where)->select()->toArray();

        if (empty($lists)) {
            return [];
        }
        // 提取有参与活动的商品ids
        $goodsIds = array_column($lists, 'goods_id');
        $goodsIds = array_unique($goodsIds);

        // 生成初始化数据
        $data = [];
        foreach ($goodsIds as $goodsId) {
            $data[$goodsId] = [];
            foreach (ActivityEnum::TYPE as $type) {
                $data[$goodsId][$type]['type'] = $type;
                $data[$goodsId][$type]['activity_id'] = null;
                $data[$goodsId][$type]['item_id'] = [];
            }
        }

        // 填充活动信息
        foreach ($lists as $item) {
            $data[$item['goods_id']][$item['activity_type']]['activity_id'] = $item['activity_id'];
            $data[$item['goods_id']][$item['activity_type']]['item_id'][] = $item['item_id'];
        }

        // 去除没有活动信息的初始数据
        foreach($data as $key => $item) {
            foreach (ActivityEnum::TYPE as $type) {
                if (is_null($item[$type]['activity_id'])) {
                    unset($data[$key][$type]);
                }
            }
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}