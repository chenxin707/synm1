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
namespace app\kefuapi\lists;


use app\common\enum\ChatRecordEnum;
use app\common\logic\ChatLogic;
use app\common\model\ChatRecord;

/**
 * 聊天记录(客服页面中聊天记录)
 * Class ChatOrderLists
 * @package app\kefuapi\lists
 */
class ChatRecordLists extends BaseKefuDataLists
{

    /**
     * @notes 设置查询条件
     * @return array[]
     * @author 段誉
     * @date 2022/3/14 14:51
     */
    public function setWhere(): string
    {
//        $map1 = [
//            ['from_id', '=', $this->kefuId],
//            ['from_type', '=', 'kefu'],
//            ['to_id', '=', $this->params['user_id']],
//            ['type', '=', ChatRecordEnum::TYPE_NORMAL]
//        ];
//        $map2 = [
//            ['to_id', '=', $this->kefuId],
//            ['to_type', '=', 'kefu'],
//            ['from_id', '=', $this->params['user_id']],
//            ['type', '=', ChatRecordEnum::TYPE_NORMAL]
//        ];
        $userId = $this->params['user_id'];
        $whereSql = "(from_id = $this->kefuId and from_type = 'kefu' and to_id = $userId and type = ".ChatRecordEnum::TYPE_NORMAL .") or (to_id = $this->kefuId and to_type = 'kefu' and from_id = $userId and type = ".ChatRecordEnum::TYPE_NORMAL .")";
        return $whereSql;
    }


    /**
     * @notes 聊天记录列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/14 14:51
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
     * @date 2022/3/14 14:52
     */
    public function count(): int
    {
        return ChatRecord::whereRaw($this->setWhere())->count();
    }

}