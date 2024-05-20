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
namespace app\adminapi\logic\live;

use app\common\enum\LiveEnum;
use app\common\exception\WechatException;
use app\common\service\WeChatConfigService;
use app\common\service\WeChatService;
use EasyWeChat\Factory;
use think\Exception;

/**
 * 直播间逻辑层
 * Class LiveRoomLogic
 * @package app\adminapi\logic\live
 */
class LiveRoomLogic
{

    /**
     * @notes 直播间列表
     * @param int $limitOffset
     * @param int $limitLength
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author cjhao
     * @date 2021/11/27 10:27
     */
    public function lists(int $limitOffset,int $limitLength)
    {
        $result = WeChatService::getLiveRoom($limitOffset-1, $limitLength);
        if (!is_array($result)) {
            return $result;
        }
        $data = [];
        foreach ($result['room_info'] as $item) {

            $data[] = [
                'name'          => $item['name'],
                'room_id'       => $item['roomid'],
                'cover_img'     => $item['cover_img'],
                'anchor_name'   => $item['anchor_name'],
                'live_status'   => LiveEnum::getLiveStatus($item['live_status']),
                'goods'         => count($item['goods']),
                'start_time'    => date('Y-m-d H:i:s', $item['start_time']),
                'end_time'      => date('Y-m-d H:i:s', $item['end_time'])
            ];
        }

        $list = [
            'lists'         => $data,
            'count'         => $result['total'],
            'page_no'       => $limitOffset,
            'page_size'     => $limitLength,
        ];
        return $list;
    }
    /**
     * @notes 创建直播间
     * @param array $post
     * @return bool|string
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @author cjhao
     * @date 2021/11/22 18:26
     */
    public function add(array $post)
    {
        try {
            $data = [
                'name'              => $post['name'],
                'coverImg'          => $post['cover_img'],
                'startTime'         => $post['start_time'],
                'endTime'           => $post['end_time'],
                'anchorName'        => $post['anchor_name'],
                'anchorWechat'      => $post['anchor_wechat'],
                'subAnchorWechat'   => $post['sub_anchor_wechat'] ?? '',
                'createrWechat'     => $post['anchor_wechat'],
                'shareImg'          => $post['share_img'],
                'feedsImg'          => $post['feeds_img'],
                'type'              => $post['type'],
                'isFeedsPublic'     => $post['is_feeds_public'],
                'closeLike'         => $post['close_like'],
                'closeGoods'        => $post['close_goods'],
                'closeComment'      => $post['close_comment'],
                'closeReplay'       => $post['close_replay'],
                'closeShare'        => $post['close_share'],
                'closeKf'           => $post['close_kf'],
            ];
            $config = WeChatConfigService::getMnpConfig();

            $app = Factory::miniProgram($config);
            $result = $app->broadcast->createLiveRoom($data);

            if (0 != $result['errcode']) {
                throw new WechatException($result['errmsg'],$result['errcode'] );
            }
            return true;
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }


    /**
     * @notes 删除直播间
     * @param int $roomId
     * @return bool|string
     * @author cjhao
     * @date 2021/11/23 10:35
     */
    public function del(int $roomId)
    {
        try {
            $config = WeChatConfigService::getMnpConfig();
            $app = Factory::miniProgram($config);
            $result = $app->broadcast->deleteLiveRoom(['id'=>$roomId]);
            if ($result['errcode'] != 0) {
                throw new WechatException($result['errmsg'],$result['errcode'] );
            }

            return true;

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }




}