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
namespace app\shopapi\validate;
use app\common\model\User;
use app\common\validate\BaseValidate;

/**
 * 设置用户基础信息验证器
 * Class SetUserInfoValidate
 * @package app\shopapi\validate
 */
class SetUserInfoValidate extends BaseValidate
{
    protected $rule = [
        'field'             => 'require|checkField',
        'value'             => 'require',
    ];

    protected $message = [
        'field.require'     => '参数缺失',
        'value.require'     => '值不存在',
    ];


    /**
     * @notes 校验字段
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/8/24 15:31
     */
    protected function checkField($value,$rule,$data)
    {
        $allowField = [
            'nickname','sex','avatar','mobile','real_name'
        ];
        if(!in_array($value,$allowField)){
            return '参数错误';
        }
        if($value != 'mobile') {
            return true;
        }
        $user = User::where([
            ['mobile', '=', $data['value']],
            ['id', '<>', $data['id']]
        ])->findOrEmpty();
        if($user->isEmpty()) {
            return true;
        }
        return '该手机号已被绑定';
    }



}