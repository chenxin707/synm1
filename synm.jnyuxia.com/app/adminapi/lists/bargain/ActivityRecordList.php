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

namespace app\adminapi\lists\bargain;

use app\adminapi\lists\BaseAdminDataLists;

use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsExtendInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\BargainInitiate;
use app\common\service\FileService;


/**
 * 砍价活动列表
 * Class BargainActivityLists
 * @package app\adminapi\lists\bargain
 */
class ActivityRecordList extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{
    /**
     * @notes 导出字段
     * @return string[]
     * @author Tab
     * @date 2021/9/24 17:50
     */
    public function setExcelFields(): array
    {
        return [
            'sn' => '记录编号',
            'goods_name' => '商品名称',
            'item_spec_value_str' => '商品规格',
            'nickname' => '发起用户',
            'help_num' => '帮砍次数',
            'current_price' => '当前价格',
            'status_desc' => '砍价状态',
            'create_time' => '发起时间',
        ];
    }

    /**
     * @notes 导出表名
     * @return string
     * @author Tab
     * @date 2021/9/24 17:50
     */
    public function setFileName(): string
    {
        return '活动记录';
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author Tab
     * @date 2021/9/24 17:30
     */
    public function setSearch(): array
    {
        return [
            '=' => ['status'],
            'between_time' => 'create_time'
        ];
    }

    /**
     * @notes 附加搜索
     * @author Tab
     * @date 2021/9/24 15:54
     */
    public function attachSearch()
    {
        $this->searchWhere[] = ['bi.activity_id', '=', $this->params['id']];
    }

    /**
     * @notes 列表
     * @return array
     * @author Tab
     * @date 2021/9/24 17:50
     */
    public function lists() : array
    {
        $field = [
            'bi.id',
            'bi.sn',
            'bi.goods_snapshot',
            'bi.bargain_snapshot',
            'bi.help_num',
            'bi.current_price',
            'bi.status',
            'bi.status' => 'status_desc',
            'bi.create_time',
            'u.avatar',
            'u.nickname',
        ];

        $lists = BargainInitiate::withoutGlobalScope()
            ->aliasSid('bi')
            ->alias('bi')
            ->leftJoin('user u', 'u.id = bi.user_id')
            ->withSearch(['goods_info', 'user_info', 'activity_info'], $this->params)
            ->field($field)
            ->order('bi.create_time', 'desc')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['goods_snapshot']['goods_image'] = FileService::getFileUrl($item['goods_snapshot']['goods_image']);
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
            $item['goods_name'] = $item['goods_snapshot']['goods_name'];
            $item['item_spec_value_str'] = $item['goods_snapshot']['item_spec_value_str'];
        }

        return $lists;
    }

    /**
     * @notes 记录数
     * @return int
     * @author Tab
     * @date 2021/9/24 17:51
     */
    public function count() : int
    {
        $count = BargainInitiate::withoutGlobalScope()
            ->aliasSid('bi')
            ->alias('bi')
            ->leftJoin('user u', 'u.id = bi.user_id')
            ->withSearch(['goods_info', 'user_info'], $this->params)
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}