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

namespace app\kefuapi\controller;


use app\kefuapi\lists\ChatRecordLists;
use app\kefuapi\logic\ChatLogic;
use app\kefuapi\lists\{ChatUserLists, ChatOrderLists, KefuLangLists};
use app\kefuapi\validate\{ChatRecordValidate, ChatOrderValidate};


/**
 * 客服聊天相关
 * Class ChatController
 * @package app\kefuapi\controller
 */
class ChatController extends BaseKefuController
{

    public array $notNeedLogin = ['config'];


    /**
     * @notes 获取文件配置
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:01
     */
    public function config()
    {
        return $this->success('', ChatLogic::getConfig());
    }


    /**
     * @notes 用户列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:02
     */
    public function user()
    {
        return $this->dataLists(new ChatUserLists());
    }



    /**
     * @notes 获取指定用户聊天记录
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:02
     */
    public function record()
    {
        (new ChatRecordValidate())->goCheck();
        return $this->dataLists(new ChatRecordLists());
    }



    /**
     * @notes 获取指定用户订单列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:03
     */
    public function order()
    {
        (new ChatOrderValidate())->goCheck();
        return $this->dataLists(new ChatOrderLists());
    }


    /**
     * @notes 获取在线客服列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:05
     */
    public function online()
    {
        $result = ChatLogic::getOnlineKefu($this->kefuId);
        return $this->success('', $result);
    }


    /**
     * @notes 获取快捷回复列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:06
     */
    public function reply()
    {
        return $this->dataLists(new KefuLangLists());
    }


    /**
     * @notes 获取用户详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:07
     */
    public function userInfo()
    {
        $user_id = $this->request->get('user_id/d');
        $result = ChatLogic::getUserInfo($user_id);
        if (false === $result) {
            return $this->fail(ChatLogic::getError() ?: '系统错误');
        }
        return $this->success('', $result);
    }


    /**
     * @notes 获取客服详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 12:10
     */
    public function kefuInfo()
    {
        $result = ChatLogic::getKefuInfo($this->kefuId);
        return $this->success('', $result);
    }

}