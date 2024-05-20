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

use app\common\lists\ListsSearchInterface;
use app\common\logic\ChatLogic;
use app\common\model\ChatRelation;

/**
 * 用户列表-(客服聊天页-用户列表)
 * Class ChatUserLists
 * @package app\kefuapi\lists
 */
class ChatUserLists extends BaseKefuDataLists implements ListsSearchInterface
{

    /**
     * @notes 设置搜索条件
     * @return array
     * @author 段誉
     * @date 2022/3/14 14:52
     */
    public function setSearch(): array
    {
        $this->searchWhere[] = ['kefu_id', '=', $this->kefuId];
        if (isset($this->params['nickname']) && $this->params['nickname']) {
            $this->searchWhere[] = ['nickname', 'like', '%' . $this->params['nickname'] . '%'];
        }
        return $this->searchWhere;
    }


    /**
     * @notes 获取排序
     * @param $onlineUser
     * @return string
     * @author 段誉
     * @date 2022/3/14 14:52
     */
    public function getOrderRaw($onlineUser)
    {
        $exp = 'update_time desc';
        if (!empty($onlineUser)) {
            $user_id = implode(",", $onlineUser);
            $exp = "field(user_id," . $user_id . ") desc, update_time desc";
        }
        return $exp;
    }

    /**
     * @notes 获取用户列表
     * @return array
     * @author 段誉
     * @date 2022/3/14 14:52
     */
    public function lists(): array
    {
        $this->setSearch();
        $onlineUser = ChatLogic::getOnlineUser();

        $lists = ChatRelation::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->orderRaw($this->getOrderRaw($onlineUser))
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['online'] = 0;
            if (in_array($item['user_id'], $onlineUser)) {
                $item['online'] = 1;
            }
            if (empty($item['msg'])) {
                $item['update_time'] = '';
            }
        }

        return $lists;
    }


    /**
     * @notes 列表数量
     * @return int
     * @author 段誉
     * @date 2022/3/14 14:53
     */
    public function count(): int
    {
        return ChatRelation::where($this->searchWhere)->count();
    }

}