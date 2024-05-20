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

use app\common\logic\ChatLogic;
use app\common\model\ChatRecord;

/**
 * 用户与客服聊天记录列表
 * Class ChatRecordLists
 * @package app\shopapi\lists
 */
class ChatRecordLists extends BaseShopDataLists
{

    /**
     * @notes 设置查询条件
     * @return array[]
     * @author 段誉
     * @date 2022/3/14 14:48
     */
    public function setWhere():string
    {
        $whereSql = "(from_id = $this->userId and from_type = 'user') or (to_id = $this->userId and to_type = 'user')";
        return $whereSql;
    }


    /**
     * @notes 聊天记录列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/14 14:48
     */
    public function lists(): array
    {
        $lists = ChatRecord::whereRaw($this->setWhere())
            ->order('id desc')
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        if (!empty($lists)) {
            $lists = ChatLogic::formatChatRecords($lists);
        }

        return $lists;
    }


    /**
     * @notes 记录数量
     * @return int
     * @author 段誉
     * @date 2022/3/14 14:49
     */
    public function count(): int
    {
        return ChatRecord::whereRaw($this->setWhere())->count();
    }
}