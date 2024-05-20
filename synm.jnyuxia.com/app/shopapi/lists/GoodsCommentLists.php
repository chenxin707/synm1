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


use app\common\enum\GoodsCommentEnum;
use app\common\model\GoodsComment;
use app\common\service\FileService;

class GoodsCommentLists extends BaseShopDataLists
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author ljj
     * @date 2021/8/9 11:09 上午
     */
    public function setSearch()
    {
        $where= [];
        $where[] = ['gc.goods_id','=',$this->params['goods_id']];
        if (!isset($this->params['id']) || $this->params['id'] == '') {
            return $where;
        }
        switch ($this->params['id']){
            case 1://晒图
                $where[]= ['gci.uri','not null',''];
                break;
            case 2://好评
                $where[]= ['gc.goods_comment','>',3];
                break;
            case 3://中评
                $where[]= ['gc.goods_comment','=',3];
                break;
            case 4://差评
                $where[]= ['gc.goods_comment','<',3];
                break;
            default:
                break;
        }
        return $where;
    }

    /**
     * @notes 查看商品评论列表
     * @return array
     * @author ljj
     * @date 2021/8/9 11:09 上午
     */
    public function lists(): array
    {
        $lists = GoodsComment::withoutGlobalScope()
            ->aliasSid('gc')
            ->alias('gc')
            ->leftjoin('user u', 'gc.user_id = u.id')
            ->leftjoin('goods_item gi', 'gc.item_id = gi.id')
            ->leftjoin('goods_comment_image gci', 'gc.id = gci.comment_id')
            ->with(['goods_comment_image'])
            ->field('gc.id,gc.goods_comment,gc.service_comment,gc.express_comment,gc.description_comment,gc.comment,gc.reply,gc.create_time,gc.virtual,u.nickname,u.avatar,gi.spec_value_str')
            ->where($this->setSearch())
            ->where(['status'=>GoodsCommentEnum::APPROVED])
            ->limit($this->limitOffset, $this->limitLength)
            ->order('gc.id','desc')
            ->group('gc.id')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            //处理用户头像路径
            if (empty($list['virtual'])) {
                // 真实评价
                $list['avatar'] = empty($list['avatar']) ? '' : FileService::getFileUrl($list['avatar']);
                $list['nickname'] = hide_substr($list['nickname']);
            } else {
                // 虚拟评价
                $virtual = json_decode($list['virtual'], true);
                $list['avatar'] = FileService::getFileUrl($virtual['avatar']);
                $list['nickname'] = hide_substr($virtual['nickname']);
                $list['user_sn'] = $virtual['sn'];
                $list['goods_name'] = $virtual['goods_name'];
            }

            //处理评论图片输出
            foreach ($list['goods_comment_image'] as $val) {
                $list['image'][] = $val['uri'];
            }
            unset($list['goods_comment_image']);
        }

        return $lists;
    }

    /**
     * @notes 查看商品评论总数
     * @return int
     * @author ljj
     * @date 2021/8/9 11:08 上午
     */
    public function count(): int
    {
        return GoodsComment::withoutGlobalScope()
            ->aliasSid('gc')
            ->alias('gc')
            ->leftjoin('user u', 'gc.user_id = u.id')
            ->leftjoin('goods_item gi', 'gc.item_id = gi.id')
            ->leftjoin('goods_comment_image gci', 'gc.id = gci.comment_id')
            ->where($this->setSearch())
            ->where(['status'=>GoodsCommentEnum::APPROVED])
            ->group('gc.id')
            ->count();
    }
}
