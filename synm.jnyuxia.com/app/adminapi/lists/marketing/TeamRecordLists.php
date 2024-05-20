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

namespace app\adminapi\lists\marketing;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\CouponEnum;
use app\common\enum\TeamEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\CouponList;
use app\common\model\TeamFound;
use app\common\model\Goods;
use app\common\service\FileService;

class TeamRecordLists extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{
    /**
     * @notes 设置导出字段
     * @return array
     * @author suny
     * @date 2021/9/23 2:57 下午
     */
    public function setExcelFields(): array
    {

        return [
            'found_sn' => '拼团编号',
            'goods_name' => '商品名称',
            'nickname' => '团长',
            'people' => '成团人数',
            'join' => '参团人数',
            'status_text' => '拼团状态',
            'kaituan_time' => '开团时间',
        ];
    }

    /**
     * @notes 设置导出文件名
     * @return string
     * @author suny
     * @date 2021/9/23 2:57 下午
     */
    public function setFileName(): string
    {

        return '拼团记录表';
    }

    /**
     * @notes 设置搜索
     * @return array
     * @author suny
     * @date 2021/9/23 2:58 下午
     */
    public function setSearch(): array
    {

        return [
            '=' => ['TF.status'],
            '%like%' => ['U.nickname', 'U.sn','TA.name'],
            "between_time" => 'TF.kaituan_time'
        ];
    }

    /**
     * @notes 拼团记录搜索条件
     * @return array
     * @author heshihu
     * @date 2021/9/30 11:58
     *
     */
    public function queryWhere()
    {
        $where = [];
        if (!empty($this->params['name']) and $this->params['name']) {
            $where[] = ['TA.name', 'like', '%' .  $this->params['name'] . '%'];
        }
        if (!empty($this->params['user_info']) and $this->params['user_info']) {
            $where[] = ['U.nickname|U.sn', 'like', '%' . $this->params['user_info'] . '%'];
        }
        if ($this->params['status'] != '') {
            $where[] = ['TF.status', '=', $this->params['status']];
        }
        if (!empty($this->params['start_time']) and $this->params['start_time']) {
            $where[] = ['TF.kaituan_time', '>=', strtotime($this->params['start_time'])];
        }
        if (!empty($this->params['end_time']) and $this->params['end_time']) {
            $where[] = ['TF.kaituan_time', '<=', strtotime($this->params['end_time'])];
        }

        return $where;
    }

    /**
     * @notes 拼团记录原生搜索条件
     * @return string
     * @author heshihu
     * @date 2021/9/30 15:02
     */
    public function queryWhereRaw()
    {
        $whereRaw = "TF.id > 0";
        if (!empty($this->params['goods_info']) and $this->params['goods_info']) {
            $goods_ids = (new Goods())->where([
                ['name|code' ,'like', '%' . $this->params['goods_info'] . '%']
            ])
                ->column('id');
            if(!$goods_ids){
                return 'TF.id < 0';
            }
            $whereRaw = "json_extract(`TF`.`goods_snap`, '$.id') in (".implode(',', $goods_ids).")";
        }

        return $whereRaw;
    }

    /**
     * @notes 获取拼团记录列表
     * @return array
     * @author suny
     * @date 2021/9/23 3:00 下午
     */
    public function lists(): array
    {

        $lists = (new TeamFound())->duokaiWithoutGlobalScope()
            ->aliasSid('TF')
            ->alias('TF')
            ->field([
                'TA.name',
                'TF.id,TF.found_sn,TF.people,TF.join,TF.status,TF.kaituan_time',
                'TF.goods_snap',
                'U.avatar,U.nickname'
            ])
            ->where($this->queryWhere())
            ->whereRaw($this->queryWhereRaw())
            ->limit($this->limitOffset, $this->limitLength)
            ->join('user U', 'U.id = TF.user_id')
            ->join('team_activity TA', 'TF.team_id = TA.id')
            ->json(['TF.goods_snap'])
            ->order('TF.id desc')
            ->select()->toArray();


        foreach ($lists as &$list) {
            $list['found_sn'] = $list['found_sn']." ";
            $list['kaituan_time'] = date('Y-m-d H:i:s',$list['kaituan_time']);
            $list['avatar'] = FileService::getFileUrl($list['avatar']);
            $list['status_text']   = TeamEnum::getStatusDesc($list['status']);
            $list['goods_snap'] = json_decode($list['goods_snap'],true);
            $list['goods_image'] = $list['goods_snap']['image'];
            $list['goods_name'] = $list['goods_snap']['name'];
            unset($list['goods_snap']);
        }

        return $lists;
    }

    /**
     * @notes 获取拼团记录数量
     * @return int
     * @author suny
     * @date 2021/9/23 2:59 下午
     */
    public function count(): int
    {

        return (new TeamFound())->duokaiWithoutGlobalScope()
            ->aliasSid('TF')
            ->alias('TF')
            ->field([
                'TA.name',
                'TF.id,TF.found_sn,TF.people,TF.join,TF.status,TF.kaituan_time',
                'TF.goods_snap',
                'U.avatar,U.nickname'
            ])
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->join('user U', 'U.id = TF.user_id')
            ->join('team_activity TA', 'TF.team_id = TA.id')
            ->order('TF.id desc')
            ->count();
    }
}