<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likeshop_gitee/likeshop
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------
namespace app\common\service;

use app\common\enum\NoticeEnum;
use app\common\enum\UserTerminalEnum;
use app\common\logic\NoticeLogic;
use app\common\model\Notice;
use app\common\model\NoticeSetting;
use app\common\model\UserAuth;
use EasyWeChat\Factory;

/**
 * 微信消息服务层
 * Class WechatMessageService
 * @package app\common\service
 */
class WechatMessageService
{
    /** Easychat实例
     * @var null
     */
    protected $app = null;

    protected $config = null;
    protected $openid = null;
    protected $templateId = null;
    protected $notice = null;
    protected $platform = null;

    /**
     * @notes 架构方法
     * @param $userId
     * @param $platform
     * @author Tab
     * @date 2021/8/20 14:21
     */
    public function __construct($userId, $platform)
    {
        $this->platform = $platform;
        if($platform == NoticeEnum::OA) {
            $terminal = UserTerminalEnum::WECHAT_OA;
            $this->config = [
                'app_id' => ConfigService::get('official_account','app_id'),
                'secret' => ConfigService::get('official_account','app_secret')
            ];
            $this->app = Factory::officialAccount($this->config);
        }
        if($platform == NoticeEnum::MNP) {
            $terminal = UserTerminalEnum::WECHAT_MMP;
            $this->config = [
                'app_id' => ConfigService::get('mini_program','app_id'),
                'secret' => ConfigService::get('mini_program','app_secret')
            ];
            $this->app = Factory::miniProgram($this->config);
        }
        $userAuth = UserAuth::where([
            'user_id' => $userId,
            'terminal' => $terminal
        ])->findOrEmpty()->toArray();
        $this->openid = $userAuth['openid'];
    }

    /**
     * @notes 发送消息
     * @param $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author Tab
     * @date 2021/8/20 16:42
     */
    public function send($params)
    {
        try {
            if(empty($this->openid)) {
                throw new \Exception('openid不存在');
            }
            $noticeSetting = NoticeSetting::where('scene_id', $params['scene_id'])->findOrEmpty()->toArray();
            if ($this->platform == NoticeEnum::OA) {
                $sceneConfig = $noticeSetting['oa_notice'];
                $sendType = NoticeEnum::OA;
            } else {
                $sceneConfig = $noticeSetting['mnp_notice'];
                $sendType = NoticeEnum::MNP;
            }

            if ($sceneConfig['template_id'] == '') {
                throw new \Exception('模板ID不存在');
            } else {
                $this->templateId = $sceneConfig['template_id'];
            }

            if ($this->platform == NoticeEnum::OA) {
                $template = $this->oaTemplate($params, $sceneConfig);
            } else {
                $template = $this->mnpTemplate($params, $sceneConfig);
            }

            // 添加通知记录
            $this->notice = NoticeLogic::addNotice($params, $noticeSetting, $sendType, json_encode($template, JSON_UNESCAPED_UNICODE));

            if ($this->platform  == NoticeEnum::OA) {
                $res = $this->app->template_message->send($template);
            } else if ($this->platform == NoticeEnum::MNP) {
                $res = $this->app->subscribe_message->send($template);
            }
            if(isset($res['errcode']) && $res['errcode'] != 0) {
                // 发送失败
                throw new \Exception(json_encode($res, JSON_UNESCAPED_UNICODE));
            }
            // 发送成功，记录消息结果
            Notice::where('id', $this->notice->id)->update(['extra' => json_encode($res, JSON_UNESCAPED_UNICODE)]);

            return true;
        } catch (\Exception $e) {
            // 记录消息错误信息
            Notice::where('id', $this->notice->id)->update(['extra' => $e->getMessage()]);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @notes 小程序消息模板
     * @param $params
     * @param $sceneConfig
     * @return mixed
     * @author Tab
     * @date 2021/8/20 15:05
     */
    public function mnpTemplate($params, $sceneConfig)
    {
        $tpl = [
            'touser'      => $this->openid,
            'template_id' => $this->templateId,
            'page'        => $params['page']
        ];
        return $this->tplformat($sceneConfig, $params, $tpl);
    }

    /**
     * @notes 公众号消息模板
     * @param $params
     * @param $sceneConfig
     * @return array
     * @author Tab
     * @date 2021/8/20 16:46
     */
    public function oaTemplate($params, $sceneConfig)
    {
        $domain = request()->domain();
        $tpl = [
            'touser'      => $this->openid,
            'template_id' => $this->templateId,
            'url'         => $domain.$params['url'],
            'data'        => [
                'first'  => $sceneConfig['first'],
                'remark' => $sceneConfig['remark']
            ]
        ];
        return $this->tplformat($sceneConfig, $params, $tpl);
    }

    /**
     * @notes 提取并填充微信平台变量
     * @param $sceneConfig
     * @param $params
     * @param $tpl
     * @return array
     * @author Tab
     * @date 2021/8/20 15:33
     */
    public function tplformat($sceneConfig, $params, $tpl)
    {
        foreach($sceneConfig['tpl'] as $item) {
            foreach($params['params'] as $k => $v) {
                $search = '{' . $k . '}';
                $item['tpl_content'] = str_replace($search, $v, $item['tpl_content']);
            }
            $tpl['data'][$item['tpl_keyword']] = $item['tpl_content'];
         }
        return $tpl;
    }
}