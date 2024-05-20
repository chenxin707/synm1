<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\shopapi\logic;

use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\Exception;

/**
 * 微信逻辑层
 * Class WechatLogic
 * @package app\shopapi\logic
 */
class WechatLogic extends BaseLogic
{
    /**
     * @notes 微信JSSDK授权接口
     * @param $params
     * @return array|false|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @author Tab
     * @date 2021/8/30 19:20
     */
    public static function jsConfig($params)
    {
        try {
            $config = WeChatConfigService::getOaConfig();
            $app = Factory::officialAccount($config);
            $url = urldecode($params['url']);
            $app->jssdk->setUrl($url);
            $apis = [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'onMenuShareQZone',
                'openLocation',
                'getLocation',
                'chooseWXPay',
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'openAddress',
                'scanQRCode'
            ];

            $data = $app->jssdk->getConfigArray($apis, $debug = false, $beta = false);

            return $data;
        } catch (Exception |\think\Exception $e) {

            self::setError('公众号配置出错:' . $e->getMessage());
            return false;
        }
    }
}