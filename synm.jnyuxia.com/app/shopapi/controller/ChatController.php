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

namespace app\shopapi\controller;

use app\shopapi\logic\ChatLogic;
use app\common\logic\ChatLogic as CommonChatLogic;
use app\shopapi\validate\ChatGoodsValidate;

/**
 * 聊天相关控制器
 * Class ChatController
 * @package app\shopapi\controller
 */
class ChatController extends BaseShopController
{

    /**
     * @notes 获取与客服聊天记录
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 14:47
     */
    public function chatRecord()
    {
        return $this->success('', ChatLogic::getChatRecord($this->userId));
    }


    /**
     * @notes 聊天配置
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/14 15:01
     */
    public function chatConfig()
    {
        $result = CommonChatLogic::getConfig();
        return $this->success($result['msg'], [], $result['code']);
    }




    /**
     * @notes 商品详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/24 14:23
     */
    public function goods()
    {
        $params = (new ChatGoodsValidate())->goCheck();
        $result = CommonChatLogic::getChatGoodsDetail($params);
        return $this->success('', $result);
    }

}