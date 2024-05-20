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
namespace app\adminapi\validate\user;

use app\common\{model\User, model\UserLabel, model\UserLevel, validate\BaseValidate};

class UserValidate extends BaseValidate
{
    protected $rule = [
        'user_id'   => 'require|checkUser',
        'user_ids'  => 'require|array',
        'label_ids' => 'require|array',
        'field'     => 'require|checkField',
        'value'     => 'require',
        'type'  => 'require'
    ];

    protected $message = [
        'user_id.require'   => '请选择用户',
        'user_ids.require'  => '请选择用户',
        'user_ids.array'    => '用户数据错误',
        'label_ids.require' => '请选择标签',
        'label_ids.array'   => '标签数据错误',
        'level_id.require'  => '请选择等级',
        'field.require'     => '请选择操作',
        'value.require'     => '请输入内容',
        'type.require'     => '请选择调整方式',
    ];

    public function sceneDetail()
    {
        return $this->only(['user_id']);
    }

    //批量设置用户标签
    public function sceneSetLabel()
    {
        return $this->only(['user_ids', 'label_ids']);
    }

    public function sceneSetUserLabel()
    {
        return $this->only(['user_id', 'label_ids'])->append('label_ids','checkLabel');
    }

    //设置黑名单
    public function sceneSetInfo()
    {
        return $this->only(['user_id', 'field', 'value']);
    }

    //获取用户信息
    public function sceneFans()
    {
        return $this->only(['user_id']);
    }

    public function sceneInfo()
    {
        return $this->only(['user_id']);
    }

    public function sceneUserInviterLists()
    {
        return $this->only(['user_id']);
    }

    public function sceneAdjustFirstLeaderInfo()
    {
        return $this->only(['user_id']);
    }

    public function sceneAdjustFirstLeader()
    {
        return $this->only(['user_id', 'type']);
    }

    //用户等级
    public function checkLevel($value, $rule, $data)
    {
        if (!UserLevel::find($value)) {
            return '该等级不存在！';
        }
        return true;
    }



    //用户验证
    public function checkUser($value,$rule,$data)
    {
        $userIds = is_array($value) ? $value : [$value];

        foreach ($userIds as $item) {
            if(!User::find($item)) {
                return '用户不存在！';
            }
        }

        return true;
    }
    //验证是否可更新信息
    public function checkField($value, $rule, $data)
    {
        $allowField = ['nickname', 'sex', 'mobile', 'disable', 'level', 'birthday','real_name'];

        if (!in_array($value, $allowField)) {
            return '用户信息不允许更新';
        }


        switch ($value) {
            case 'mobile':
                if (false == $this->validate($data['value'], 'mobile', $data)) {
                    return '手机号码格式错误';
                }

                //验证手机号码是否存在
                $mobile = User::where([['id', '<>', $data['user_id']], ['mobile', '=', $data['value']]])
                    ->find();
                if ($mobile) {
                    return '手机号码已存在';
                }

                break;
            case 'level':
                if (!UserLevel::find($data['value'])) {
                    return '该等级不存在';
                }

                break;

        }
        return true;
    }

    //验证用户标签
    public function checkLabel($value,$rule,$data)
    {
        $count = UserLabel::where(['id'=>$value])->count();
        if(count($value) != $count){
            return '部分标签已不删除，请选择标签';
        }
        return true;
    }

}