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
namespace app\shopapi\lists;

use app\common\enum\LiveEnum;
use app\common\service\WeChatService;


/**
 * 直播间列表
 * Class LiveRoomLists
 * @package app\shopapi\lists
 */
class LiveRoomLists extends BaseShopDataLists
{

    public function lists(): array
    {


        $result = WeChatService::getLiveRoom($this->limitOffset, $this->limitLength);

        if (!is_array($result)) {
            return [];
        }

        $data = [];
        foreach ($result['room_info'] as $item) {

            $data[] = [
                'name' => $item['name'],
                'room_id' => $item['roomid'],
                'cover_img' => $item['cover_img'],
                'anchor_name' => $item['anchor_name'],
                'status' => $item['live_status'],
                'live_status' => LiveEnum::getLiveStatus($item['live_status']),
                'goods' => count($item['goods']),
                'start_time' => date('Y-m-d H:i:s', $item['start_time']),
                'end_time' => date('Y-m-d H:i:s', $item['end_time'])
            ];
        }
        $this->total = $result['total'];

        return $data;

    }


    public function count(): int
    {
        return $this->total;
    }
}