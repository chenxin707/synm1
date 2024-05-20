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

use app\common\enum\NoticeEnum;
use app\common\enum\YesNoEnum;
use app\common\model\Notice;
use app\common\model\NoticeSetting;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\User;
use app\common\service\sms\SmsMessageService;
use app\common\service\WechatMessageService;

/**
 * 通知逻辑层
 * Class NoticeLogic
 * @package app\common\logic
 */
class NoticeLogic extends BaseLogic
{
    /**
     * @notes 根据不同的场景发送通知
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/19 9:26
     */
    public static function noticeByScene($params)
    {
        try {
            $noticeSetting = NoticeSetting::where('scene_id', $params['scene_id'])->findOrEmpty()->toArray();
            if(empty($noticeSetting)) {
                throw new \Exception('找不到对应场景的配置');
            }
            // 合并额外参数
            $params = self::mergeParams($params);
            $res = false;
            // 系统通知
            if(isset($noticeSetting['system_notice']['status']) &&  $noticeSetting['system_notice']['status'] == YesNoEnum::YES) {
                $content = self::contentFormat($noticeSetting['system_notice']['content'], $params);
                $notice = self::addNotice($params, $noticeSetting, NoticeEnum::SYSTEM, $content);
                if($notice) {
                    $res = true;
                }
            }
            // 短信通知
            if (isset($noticeSetting['sms_notice']['status']) &&  $noticeSetting['sms_notice']['status'] == YesNoEnum::YES) {
                $res = (new SmsMessageService())->send($params);
            }
            // 公众号消息
            if (isset($noticeSetting['oa_notice']['status']) &&  $noticeSetting['oa_notice']['status'] == YesNoEnum::YES) {
                $res = (new WechatMessageService($params['params']['user_id'], NoticeEnum::OA))->send($params);
            }
            // 微信小程序
            if (isset($noticeSetting['mnp_notice']['status']) &&  $noticeSetting['mnp_notice']['status'] == YesNoEnum::YES) {
                $res = (new WechatMessageService($params['params']['user_id'], NoticeEnum::MNP))->send($params);
            }

            return $res;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 拼装额外参数
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/8/19 9:25
     */
    public static function mergeParams($params)
    {
        // 订单相关
        if(!empty($params['params']['order_id'])) {
            $order = Order::findOrEmpty($params['params']['order_id'])->toArray();
            $order_goods = OrderGoods::field('goods_name,goods_snap')
                ->where('order_id', $params['params']['order_id'])
                ->findOrEmpty()
                ->toArray();
            $params['params']['goods_name'] = $order_goods['goods_name'] ?? '商品';
            if(mb_strlen($params['params']['goods_name']) > 8) {
                $params['params']['goods_name'] = mb_substr($params['params']['goods_name'], 0, 8) . '...';
            }
            $params['params']['order_sn'] = $order['sn'];
            $params['params']['create_time'] = $order['create_time'];
            $params['params']['pay_time'] = $order['pay_time'];
            $params['params']['total_num'] = $order['total_num'];
            $params['params']['order_amount'] = $order['order_amount'];
        }

        // 用户相关
        if(!empty($params['params']['user_id'])) {
            $user = User::findOrEmpty($params['params']['user_id'])->toArray();
            $params['params']['nickname'] = $user['nickname'];
            $params['params']['user_name'] = $user['nickname'];
            $params['params']['user_sn'] = $user['sn'];
            $params['params']['mobile'] = $params['params']['mobile'] ?? $user['mobile'];
        }

        // 粉丝
        if(!empty($params['params']['fans_id'])) {
            $user = User::findOrEmpty($params['params']['fans_id'])->toArray();
            $params['params']['fans_name'] = $user['nickname'];
            $params['params']['fans_sn'] = $user['sn'];
        }

        // 跳转路径
        $jumpPath = self::getPathByScene($params['scene_id'], $params['params']['order_id'] ?? 0);
        $params['url'] = $jumpPath['url'];
        $params['page'] = $jumpPath['page'];

        return $params;
    }

    /**
     * @notes 根据场景获取跳转链接
     * @param $sceneId
     * @param $extraId
     * @return string[]
     * @author Tab
     * @date 2021/8/19 9:26
     */
    public static function getPathByScene($sceneId, $extraId)
    {
        // 小程序主页路径
        $page = '/pages/index/index';
        // 公众号主页路径
        $url = '/mobile/pages/index/index';
        if(in_array($sceneId, NoticeEnum::ORDER_SCENE)) {
            $url = '/mobile/pages/order_detail/order_detail?order_id='.$extraId;
            $page = '/pages/order_detail/order_detail?order_id='.$extraId;
        }
        return [
            'url' => $url,
            'page' => $page,
        ];
    }

    /**
     * @notes 格式化消息内容(替换内容中的变量占位符)
     * @param $content
     * @param $params
     * @return array|mixed|string|string[]
     * @author Tab
     * @date 2021/8/19 9:39
     */
    public static function contentFormat($content, $params)
    {
        foreach($params['params'] as $k => $v) {
            $search = '{' . $k . '}';
            $content = str_replace($search, $v, $content);
        }
        return $content;
    }

    /**
     * @notes 添加通知记录
     * @param $params
     * @param $noticeSetting
     * @param $sendType
     * @param $content
     * @param string $extra
     * @return Notice|\think\Model
     * @author Tab
     * @date 2021/8/19 10:07
     */
    public static function addNotice($params, $noticeSetting, $sendType, $content, $extra = '')
    {
        $data = [
            'user_id' => $params['params']['user_id'] ?? 0,
            'title' => self::getTitleByScene($sendType, $noticeSetting),
            'content' => $content,
            'scene_id' => $noticeSetting['scene_id'],
            'read' => YesNoEnum::NO,
            'recipient' => $noticeSetting['recipient'],
            'send_type' => $sendType,
            'notice_type' => $noticeSetting['type'],
            'extra' => $extra,
        ];

        return Notice::duokaiCreate($data);
    }

    /**
     * @notes 根据场景获取标题
     * @param $sendType
     * @param $noticeSetting
     * @return string
     * @author Tab
     * @date 2021/8/19 9:51
     */
    public static function getTitleByScene($sendType, $noticeSetting)
    {
        switch ($sendType)
        {
            case NoticeEnum::SYSTEM:
                $title = $noticeSetting['system_notice']['title'] ?? '';
                break;
            case NoticeEnum::SMS:
                $title = '';
                break;
            case NoticeEnum::OA:
                $title = $noticeSetting['oa_notice']['name'] ?? '';
                break;
            case NoticeEnum::MNP:
                $title = $noticeSetting['mnp_notice']['name'] ?? '';
                break;
            default:
                $title = '';
        }

        return $title;
    }

}