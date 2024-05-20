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

namespace app\adminapi\lists\goods;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\lists\ListsExcelInterface;
use app\common\model\GoodsComment;
use app\common\service\FileService;
use think\facade\Db;

class GoodsCommentLists extends BaseAdminDataLists implements ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author ljj
     * @date 2021/8/12 5:01 下午
     */
    public function setSearch(): array
    {
        $where = [];

        if (isset($this->params['goods_info']) && !empty($this->params['goods_info'])) {
            $where[] = ['og.goods_name|g.code', 'like', '%'.$this->params['goods_info'].'%'];
        }
        if (isset($this->params['user_info']) && !empty($this->params['user_info'])) {
            $where[] = ['u.nickname|u.sn', 'like', '%'.$this->params['user_info'].'%'];
        }
        if (isset($this->params['reply_status'])) {
            switch ($this->params['reply_status']){
                case '0'://全部
                    break;
                case '1'://待回复
                    $where[]= ['gc.reply', '=', null];
                    break;
                case '2'://已回复
                    $where[]= ['gc.reply', 'exp', Db::raw('is not null')];
                    break;
            }
        }
        if (isset($this->params['verify_status'])) {
            switch ($this->params['verify_status']){
                case '0'://全部
                    break;
                case '1'://待审核
                    $where[]= ['gc.status', '=', 0];
                    break;
                case '2'://审核通过
                    $where[]= ['gc.status', '=', 1];
                    break;
                case '3'://审核拒绝
                    $where[]= ['gc.status', '=', 2];
                    break;
            }
        }

        if (isset($this->params['comment_type']) && $this->params['comment_type'] == 1) {
            $where[] = ['gc.virtual', 'null', null];
        }

        if (isset($this->params['comment_type']) && $this->params['comment_type'] == 2) {
            $where[] = ['gc.virtual', 'not null', null];
        }

        return $where;
    }

    /**
     * @notes 查看商品评价列表
     * @return array
     * @author ljj
     * @date 2021/8/12 5:01 下午
     */
    public function lists(): array
    {
        $lists = GoodsComment::withoutGlobalScope()
            ->aliasSid('gc')
            ->alias('gc')
            ->leftjoin('user u', 'gc.user_id = u.id')
            ->leftjoin('order_goods og', 'gc.order_goods_id = og.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->with('goods_comment_image')
            ->field('gc.id,u.avatar,u.nickname,u.sn as user_sn,og.goods_snap,og.goods_name,gc.goods_comment,gc.comment,gc.reply,gc.status,gc.create_time,gc.virtual,gc.spec_value_str')
            ->append(['comment_level','status_desc','reply_status_desc'])
            ->json(['goods_snap'])
            ->where($this->setSearch())
            ->whereBetweenTime('gc.create_time', (isset($this->params['start_time']) && !empty($this->params['start_time'])) ? $this->params['start_time'] : '1970-01-01', (isset($this->params['end_time']) && !empty($this->params['end_time'])) ? $this->params['end_time'] : time())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id','desc')
            ->select()
            ->toArray();

        if (empty($lists)) {
            return [];
        }

        foreach ($lists as &$list) {
            //处理用户头像路径
            if (empty($list['virtual'])) {
                // 真实评价
                $list['avatar'] = empty($list['avatar']) ? '' : FileService::getFileUrl($list['avatar']);
                //处理商品信息
                $list['goods_image'] = empty($list['goods_snap']->image) ? '' : FileService::getFileUrl($list['goods_snap']->image);
                $list['spec_value_str'] = $list['goods_snap']->spec_value_str ?? '';
                unset($list['goods_snap']);
                $list['comment_type_desc'] = '真实评价';
            } else {
                // 虚拟评价
                $virtual = json_decode($list['virtual'], true);
                $list['avatar'] = FileService::getFileUrl($virtual['avatar']);
                $list['nickname'] = $virtual['nickname'];
                $list['user_sn'] = $virtual['sn'];
                $list['goods_name'] = $virtual['goods_name'];
                $list['goods_image'] = FileService::getFileUrl($virtual['goods_image']);
                $list['comment_type_desc'] = '虚拟评价';
            }
        }

        return $lists;
    }

    /**
     * @notes 查看商品评价总数
     * @return int
     * @author ljj
     * @date 2021/8/12 5:01 下午
     */
    public function count(): int
    {
        return GoodsComment::withoutGlobalScope()
            ->aliasSid('gc')
            ->alias('gc')
            ->leftjoin('user u', 'gc.user_id = u.id')
            ->leftjoin('order_goods og', 'gc.order_goods_id = og.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->where($this->setSearch())
            ->whereBetweenTime('gc.create_time', (isset($this->params['start_time']) && !empty($this->params['start_time'])) ? $this->params['start_time'] : '1970-01-01', (isset($this->params['end_time']) && !empty($this->params['end_time'])) ? $this->params['end_time'] : time())
            ->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/8/12 5:41 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'id' => 'ID',
            'nickname' => '用户昵称',
            'goods_name' => '商品名称',
            'spec_value_str' => '商品规格',
            'comment_level' => '评价等级',
            'comment' => '买家评价',
            'reply' => '商家回复',
            'reply_status_desc' => '回复状态',
            'status_desc' => '显示状态',
            'create_time' => '评价时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/8/12 5:41 下午
     */
    public function setFileName(): string
    {
        return '商品评价';
    }
}
