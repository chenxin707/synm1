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
    validate\BaseValidate,
    model\UserLevel
};

/**
 * 会员等级验证器
 * Class UserLevelValidate
 * @package app\adminapi\validate\user
 */
class UserLevelValidate extends BaseValidate
{
    protected $regex = ['condition'=>'/^[0-9]+(.[0-9]{1,2})?$/'];
    protected $rule = [
        'id'                => 'require',
        'name'              => 'require|max:32|unique:'.UserLevel::class.',name',
        'rank'              => 'require|unique:'.UserLevel::class.',rank|gt:0',
        'level_discount'    => 'require|in:0,1',
        'discount'          => 'requireIf:level_discount,1|between:0,10',
        'condition'         => 'array|checkCondition',
//        'single_money'      => 'requireIf:condition_type,1|regex:condition',
//        'total_money'       => 'requireIf:condition_type,1|regex:condition',
//        'total_num'         => 'requireIf:condition_type,1|regex:condition',
    ];

    protected $message = [
        'id.require'                => '请选择等级',
        'name.require'              => '请输入等级名称',
        'name.max'                  => '等级名称最多为32个字符',
        'name.unique'               => '等级名称已存在',
        'rank.require'              => '请输入级别',
        'rank.unique'               => '级别重复，请重新输入',
        'rank.gt'                   => '级别必须大于零',
        'level_discount.require'    => '请选择等级折扣',
        'level_discount.in'         => '等级折扣类型错误',
        'discount.requireIf'        => '请填写等级折扣',
        'discount.between'          => '等级折扣值在0~10之间',
        'single_money.requireIf'    => '请输入单笔消费金额',
        'single_money.regex'        => '单笔消费金额只能是大于零的数字,且保留两位小数',
        'total_money.requireIf'     => '请输入累计消费金额',
        'total_money.regex'         => '累计消费金额只能是大于零数字,且保留两位小数',
        'total_num.requireIf'       => '请输入累计消费次数',
        'total_num.regex'           => '累计消费次数只能是大于零数字',
        'condition.require'         => '请选择条件',
        'condition.array'           => '条件数据错误'
    ];

    //添加验证
    public function sceneAdd()
    {
        return $this->remove(['id'=>'require']);
    }
    //删除验证
    public function sceneDel(){
        return $this->only(['id'])
                ->append('id','checkLevel');
    }


    public function checkLevel($value,$rule,$data){
        $userLevel = UserLevel::find($value);
        if(1 == $userLevel->rank) {
            return '系统默认等级不允许删除';
        }
        return true;
    }

    
    public function checkCondition($value,$rule,$data)
    {
        if(1 == $data['rank']){
            return true;
        }
        if(empty($value)){
            return '请选择会员条件';
        }

        //
        $conditionType = $value['condition_type'] ?? '';
        $singleMoney = $value['single_money'] ?? '';
        $isSingleMoney = $value['is_single_money'] ?? '';
        $totalMoney = $value['total_money'] ??  '';
        $isTotalMoney = $value['is_total_money'] ??  '';
        $totalNum = $value['total_num'] ?? '';
        $isTotalNum = $value['is_total_num'] ?? '';

        //数据缺少情况
        if('' === $conditionType){
            return '请选择升级条件类型';
        }
        if('' === $isSingleMoney || '' === $isTotalMoney || '' === $isTotalNum){
            return '等级条件数据错误';
        }
        //没选择条件情况下
        if('0' === $isSingleMoney && '0' === $isTotalMoney && '0' === $isTotalNum){
            return '至少选择一个条件';
        }


        if($isSingleMoney){
            if(empty($singleMoney)){
                return '请输入单笔消费金额条件';
            }
            if($singleMoney <= 0){
                return '单笔消费金额条件必须大于零';
            }
        }

        if($isTotalMoney){
            if(empty($totalMoney)) {
                return '请输入累计消费金额条件';
            }
            if($totalMoney <= 0){
                return '单笔消费金额条件必须大于零';
            }
        }
        if($isTotalNum ){
            if(empty($totalNum)) {
                return '请输入累计消费次数条件';
            }
            if($totalNum <= 0){
                return '累计消费次数条件必须大于零';
            }
        }

        return true;
    }
}