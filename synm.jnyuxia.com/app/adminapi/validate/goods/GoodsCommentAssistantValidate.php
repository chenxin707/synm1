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

namespace app\adminapi\validate\goods;

use app\common\model\UserLevel;
use app\common\validate\BaseValidate;

class GoodsCommentAssistantValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require',
        'goods_comment' => 'require|in:1,2,3,4,5',
        'comment' => 'require',
        'avatar' => 'require',
        'nickname' => 'require',
        'level_id' => 'require|checkLevel',
        'comment_time' => 'require',
        'comment_images' => 'array|max:6',
    ];

    protected $message = [
        'goods_id.require' => '商品id缺失',
        'goods_comment.require' => '请选择评分',
        'goods_comment.in' => '评分值错误',
        'comment.require' => '请填写商品评论',
        'avatar.require' => '请选择会员头像',
        'nickname.require' => '请填写会员昵称',
        'level_id.require' => '请选择会员等级',
        'comment_time.require' => '请选择评论时间',
        'comment_images.array' => '评价图片须为数组格式',
        'comment_images.length' => '评价图片最多为6张',
    ];

    public function checkLevel($value)
    {
        $level = UserLevel::findOrEmpty($value);
        if ($level->isEmpty()) {
            return '等级不存在';
        }
        return true;
    }
}
