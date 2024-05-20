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
namespace app\adminapi\validate\live;

use app\common\validate\BaseValidate;

/**
 * 直播间验证器
 * Class LiveRoomValidate
 * @package app\adminapi\validate\live
 */
class LiveRoomValidate extends BaseValidate
{

    protected $rule = [
        'room_id'           => 'require',
        'type'              => 'require|in:0',
        'name'              => 'require|length:3,17',
        'start_time'        => 'require|checkStartTime',
        'end_time'          => 'require|checkEndTime',
        'anchor_name'       => 'require|length:2,15',
        'anchor_wechat'     => 'require',
        'cover_img'         => 'require',
        'share_img'         => 'require',
        'feeds_img'         => 'require',
        'is_feeds_public'   => 'in:0,1',
        'close_like'        => 'require|in:0,1',
        'close_goods'       => 'require|in:0,1',
        'close_comment'     => 'require|in:0,1',
        'close_replay'      => 'require|in:0,1',
        'close_share'       => 'require|in:0,1',
        'close_kf'          => 'require|in:0,1',
    ];

    protected $message = [
        'room_id.require'           => '请选择房间',
        'type.require'              => '请选择直播类型',
        'type.in'                   => '直播类型错误',
        'name.require'              => '请输入直播间名字',
        'name.length'               => '直播间名字长度在3~17个汉字',
        'start_time.require'        => '请选择直播开始时间',
        'end_time.require'          => '请选择直播结束时间',
        'anchor_name.require'       => '请输入主播名称',
        'anchor_name.length'        => '主播名称长度在2~15个汉字',
        'anchor_wechat.require'     => '请输入主播微信号',
        'cover_img'                 => '请分享卡片封面',
        'share_img'                 => '请直播卡片封面',
        'feeds_img'                 => '请直播间背景墙',
        'close_like.require'        => '请选择是否开启点赞',
        'close_like.in'             => '点赞类型错误',
        'close_goods.require'       => '请选择是否开启货架',
        'close_goods.in'            => '货架类型错误',
        'close_comment.require'     => '请选择是否开启评论',
        'close_comment.in'          => '评论类型错误',
        'close_replay.require'      => '请选择是否开启回放',
        'close_replay.in'           => '回放类型错误',
        'close_share.require'       => '请选择是否开启分享',
        'close_share.in'            => '分享类型错误',
        'close_kf.require'          => '请选择是否开启客服',
        'close_kf.in'               => '客服类型错误',
    ];

    protected function sceneAdd(){
        return $this->remove(['room_id'=>'require']);
    }

    protected function sceneDel(){
        return $this->only(['room_id']);
    }

    protected function checkStartTime($value, $rule, $data)
    {
        $now = time();
        if (($value - $now) <= 600) {
            return '开播时间需要在当前时间的10分钟后';
        }
        if(($value - $now) >= (180*86400)){
            return '开始时间不能在6个月后';
        }
        return true;
    }

    protected function checkEndTime($value, $rule, $data)
    {

        if (($value - $data['start_time']) <= (30 * 60)) {
            return '开播时间和结束时间间隔不得短于30分钟';
        }
        if($value - $data['start_time'] >= 86400){
            return '开播时间和结束时间间隔不得超过24小时';

        }
        return true;
    }
}