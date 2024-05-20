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

namespace app\shopapi\controller;

use app\shopapi\logic\WechatLogic;
use app\shopapi\validate\WechatValidate;

/**
 * 微信控制器
 * Class WechatController
 * @package app\shopapi\controller
 */
class WechatController extends BaseShopController
{
    public array $notNeedLogin = ['jsConfig'];
    /**
     * @notes 微信JSSDK授权接口
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/30 19:20
     */
    public function jsConfig()
    {
        $params = (new WechatValidate())->goCheck('jsConfig');
        $result = WechatLogic::jsConfig($params);
        if ($result === false) {
            return $this->fail(WechatLogic::getError(),[],0,0);
        }
        return $this->data($result);
    }
}