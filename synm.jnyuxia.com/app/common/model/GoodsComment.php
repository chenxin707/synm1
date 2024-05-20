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

namespace app\common\model;

use app\common\enum\GoodsCommentEnum;

class GoodsComment extends BaseModel
{
    /**
     * @notes 用户评论获取器
     * @param $value
     * @return string
     * @author ljj
     * @date 2021/8/9 10:25 上午
     */
    public function getCommentAttr($value)
    {
        if (!$value) {
            return '此用户没有填写评价';
        }
        return $value;
    }

    /**
     * @notes 一对多关联商品评论图片模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2021/8/9 10:28 上午
     */
    public function goodsCommentImage()
    {
        return $this->hasMany(GoodsCommentImage::class, 'comment_id','id');
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id')->bind(['nickname','avatar']);
    }

    /**
     * @notes 评价登记获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/8/12 3:11 下午
     */
    public function getCommentLevelAttr($value,$data)
    {
        if ($data['goods_comment'] > 3) {
            return '好评';
        }
        if ($data['goods_comment'] == 3) {
            return '中评';
        }
        if ($data['goods_comment'] < 3) {
            return '差评';
        }
        return '未知';
    }

    /**
     * @notes 状态获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/12 3:18 下午
     */
    public function getStatusDescAttr($value,$data)
    {
        return GoodsCommentEnum::getStatusDesc($data['status']);
    }

    /**
     * @notes 回复状态获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/9/9 11:12 上午
     */
    public function getReplyStatusDescAttr($value,$data)
    {
        if ($data['reply'] == null) {
            return '待回复';
        }
        return '已回复';
    }
}