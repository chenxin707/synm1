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

use app\common\enum\YesNoEnum;
use app\common\service\FileService;

class User extends BaseModel
{
    //多对多关联
    public function userLabelIndex()
    {
        return $this->belongsToMany(UserLabel::class,UserLabelIndex::class,'label_id','user_id')->field('name');
    }


    //用户信息搜索
    public function searchKeywordAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('sn|nickname|mobile', 'like', '%' . $value . '%');
        }
    }

    //等级搜索
    public function searchLevelAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('level', '=', $value);
        }
    }

    //标签搜索
    public function searchLabelIdAttr($query, $value, $data)
    {
        if ($value) {
            $userIds = UserLabelIndex::where(['label_id' => $value])->column('user_id');
            $query->where('id', 'in', $userIds);
        }
    }

    //注册来源
    public function searchSourceAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('register_source', '=', $value);
        }
    }

    //消费金额搜索
    public function searchMinAmountAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('total_order_amount', '>=', $value);
        }
    }

    //消费金额搜索
    public function searchMaxAmountAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('total_order_amount', '<=', $value);
        }
    }

    //注册时间筛选
    public function searchCreateStartTimeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('create_time', '>=', $value);
        }
    }

    //注册时间筛选
    public function searchCreateEndTimeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('create_time', '<=', $value);
        }
    }

    //禁用状态搜索
    public function searchDisableAttr($query, $value, $data)
    {
        if (in_array($value, [0, 1])) {
            $query->where('disable', '=', $value);
        }
    }

    //关联用户授权模型
    public function userAuth()
    {
        return $this->hasOne(UserAuth::class, 'user_id');

    }

    //关联用户等级模型
    public function userLevel()
    {
        return $this->hasOne(UserLevel::class, 'id', 'level')->bind(['name', 'discount','rank']);
    }


    /**
     * @notes 头像获取器 - 用于头像地址拼接域名
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/17 14:28
     */
    public function getAvatarAttr($value)
    {
        return trim($value) ? FileService::getFileUrl($value) : '';
    }

    //最后登录时间格式化
    public function getLoginTimeAttr($value)
    {
        if($value){
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }

    /**
     * @notes 一个用户都有一个分销表，一对一关联
     * @return \think\model\relation\HasOne
     * @author Tab
     * @date 2021/7/17 14:32
     */
    public function distribution()
    {
        return $this->hasOne(Distribution::class, 'user_id', 'id')
            ->field('user_id,first_leader,code,earnings');
    }

    /**
     * @notes 获取用户昵称
     * @param $userId
     * @return mixed|string
     * @author Tab
     * @date 2021/7/27 17:12
     */
    public static function getNickname($userId)
    {
        $user = self::findOrEmpty($userId)->toArray();
        if ($user) {
            return $user['nickname'];
        }
        return '';
    }

    /**
     * @notes 获取用户粉丝数量(一级/二级)
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/7/27 17:14
     */
    public static function getFans($userId)
    {
        return self::withoutGlobalScope()->whereOr([
            'first_leader' => $userId,
            'second_leader' => $userId
        ])->count();
    }

    /**
     * @notes 粉丝中有多少人是分销商
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/9/14 17:44
     */
    public static function getFansDistribution($userId)
    {
        $userIds = self::where([
            'first_leader|second_leader' => $userId,
        ])->column('id');
        return Distribution::where([
            ['user_id', 'in', $userIds],
            ['is_distribution', '=', YesNoEnum::YES],
        ])->count();
    }

    /**
     * @notes 获取用户一级粉丝数量
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/8/5 15:07
     */
    public static function getLevelOneFans($userId)
    {
        return self::where('first_leader', $userId)->count();
    }

    /**
     * @notes 一级粉丝中有多少是分销商
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/9/14 17:49
     */
    public static function getLevelOneFansDistribution($userId)
    {
        $userIds =  self::where('first_leader', $userId)->column('id');
        return Distribution::where([
            ['user_id', 'in', $userIds],
            ['is_distribution', '=', YesNoEnum::YES],
        ])->count();
    }

    /**
     * @notes 获取用户二级粉丝数量
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/8/5 15:07
     */
    public static function getLevelTwoFans($userId)
    {
        return self::where('second_leader', $userId)->count();
    }

    /**
     * @notes 二级粉丝中有多少是分销商
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/9/14 17:50
     */
    public static function getLevelTwoFansDistribution($userId)
    {
        $userIds =  self::where('second_leader', $userId)->column('id');
        return Distribution::where([
            ['user_id', 'in', $userIds],
            ['is_distribution', '=', YesNoEnum::YES],
        ])->count();
    }

    /**
     * @notes 用户粉丝数量(一级/二级)获取器
     * @param $value
     * @return int
     * @author Tab
     * @date 2021/8/5 17:35
     */
    public function getFansAttr($userId)
    {
        return self::getFans($userId);
    }

    /** 用户已支付订单总金额获取器
     * @notes
     * @param $userId
     * @return float
     * @author Tab
     * @date 2021/8/5 18:03
     */
    public function getOrderAmountAttr($userId)
    {
        return Order::where(['user_id' => $userId, 'pay_status' => YesNoEnum::YES])->sum('order_amount');
    }

    /**
     * @notes 用户已支付订单总数量获取器
     * @param $userId
     * @return int
     * @author Tab
     * @date 2021/8/5 18:04
     */
    public function getOrderNumAttr($userId)
    {
        return Order::where(['user_id' => $userId, 'pay_status' => YesNoEnum::YES])->count();
    }

    /**
     * @notes 性别获取器
     * @param $value
     * @param $data
     * @return string
     * @author cjhao
     * @date 2021/8/18 11:47
     */
    public function getSexAttr($value, $data)
    {
        switch ($value) {
            case 0:
                return '未知';
            case 1:
                return '男';
            case 2:
                return '女';
        }
    }

    /**
     * @notes 生日获取器
     * @param $value
     * @param $data
     * @return false|string
     * @author cjhao
     * @date 2021/8/18 11:50
     */
    public function getBirthdayAttr($value, $data)
    {
        if ($value) {
            return date('Y-m-d', $value);
        }
        return '';
    }

    /**
     * @notes 头像修改器
     * @param $value
     * @return mixed
     * @author Tab
     * @date 2021/8/24 14:44
     */
    public function setAvatarAttr($value)
    {
        return FileService::setFileUrl($value);
    }

    /**
     * @notes 用户收益获取器
     * @param $value
     * @return int
     * @author Tab
     * @date 2021/9/7 15:41
     */
    public function getUserEarningsAttr($value)
    {
        return empty($value) ? 0 : $value;
    }

    /**
     * @notes 邀请人信息
     * @param $id
     * @return array
     * @author Tab
     * @date 2021/9/13 18:10
     */
    public static function getInviterInfo($inviterId, $userId)
    {
        $inviter = self::findOrEmpty($inviterId)->toArray();
        if (empty($inviter)) {
            $info = '';
        } else {
            $info = $inviter['nickname'] . '(' . $inviter['sn'] .')';
        }
        $count = self::where('inviter_id', $userId)->count();

        return [
            'name' => $info,
            'num' => $count
        ];
    }

    /**
     * @notes 获取上级分销商
     * @param $id
     * @return string[]
     * @author Tab
     * @date 2021/9/13 18:18
     */
    public static function getFirstLeader($id)
    {
        $user = self::findOrEmpty($id)->toArray();
        if (empty($user)) {
            $info = '系统';
        } else {
            $info = $user['nickname'] . '(' . $user['sn'] .')';
        }
        return [
            'name' => $info
        ];
    }

}