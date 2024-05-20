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
namespace app\shopapi\logic;

use app\shopapi\lists\ChatRecordLists;
use app\common\{enum\ChatGoodsEnum,
    logic\ChatLogic as CommonChatlogic,
    model\ChatRelation,
    model\Goods,
    model\Kefu,
    logic\BaseLogic};


/**
 * 用户聊天
 * Class ChatLogic
 * @package app\shopapi\logic
 */
class ChatLogic extends BaseLogic
{
    /**
     * @notes 获取与客服聊天记录
     * @param $userId
     * @return array
     * @author 段誉
     * @date 2022/3/14 14:47
     */
    public static function getChatRecord($userId): array
    {
        // 聊天记录
        $records = (new ChatRecordLists());
        $records = [
            'lists' => $records->lists(),
            'count' => $records->count(),
            'page_no' => $records->pageNo,
            'page_size' => $records->pageSize,
            'more' => is_more($records->count(), $records->pageNo,  $records->pageSize)
        ];

        // 当前在线的所有客服
        $online = CommonChatlogic::getOnlineKefu();
        // 后台在线客服状态 0-关闭 1-开启
        $config = CommonChatlogic::getConfigSetting();

        // 没有在线客服或者后台配置为 人工客服
        if (empty($online) || $config != 1) {
            return ['config' => $config, 'kefu' => [], 'record' => $records];
        }

        // 上一个客服关系
        $kefu = ChatRelation::where(['user_id' => $userId])
            ->order('update_time desc')
            ->findOrEmpty();

        $kefuId = $kefu['kefu_id'] ?? 0;

        // 没有聊天记录(未与客服聊天) 或者 曾经的聊天客服不在线
        if (empty($kefu) || !in_array($kefuId, $online)) {
            // 随机分配客服
            $rand = rand(0, count($online) - 1);
            $kefuId = $online[$rand];
        }

        $kefu = Kefu::where(['id' => $kefuId])
            ->field(['id', 'nickname', 'avatar'])
            ->findOrEmpty();

        return ['config' => $config, 'kefu' => $kefu, 'record' => $records];
    }





}