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
use app\common\{
    model\User,
    validate\BaseValidate
};

/**
 * 调整用户钱包验证器
 * Class AdjustUserEarnings
 * @package app\adminapi\validate\user
 */
class adjustUserWallet extends BaseValidate
{
    protected $rule = [
        'user_id'   => 'require',
        'type'      => 'require|in:1,2,3',
        'action'    => 'require|in:0,1',
        'num'       => 'require|gt:0|checkNum',
        'remark'    => 'max:128',
    ];
    protected $message = [
        'id.require'        => '请选择用户',
        'type.require'      => '请选择变动类型',
        'type.in'           => '变动类型错误',
        'action.require'    => '请选择调整类型',
        'action.in'         => '调整类型错误',
        'num.require'       => '请输入调整数量',
        'num.gt'            => '调整余额必须大于零',
        'remark'            => '备注不可超过128个符号',
    ];


    protected function checkNum($vaule,$rule,$data){

        $user = User::find($data['user_id']);

        if(empty($user)){
            return '用户不存在';
        }

        if(1 == $data['action']){
            return true;
        }
        switch ($data['type']){
            case 1:
                $surplusMoeny = $user->user_money - $vaule;
                if($surplusMoeny < 0){
                    return '用户可用余额仅剩'.$user->user_money;
                }
                break;
            case 2:
                $surplusMoeny = $user->user_earnings - $vaule;
                if($surplusMoeny < 0){
                    return '用户可提现金额仅剩'.$user->user_earnings;
                }
                break;
            case 3:
                $surplusIntegral = $user->user_integral - $vaule;
                if($surplusIntegral < 0){
                    return '用户积分仅剩'.$user->user_earnings;
                }
                break;
        }

        return true;
    }


}