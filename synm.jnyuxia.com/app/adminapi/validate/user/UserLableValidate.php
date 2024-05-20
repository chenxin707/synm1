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
    model\UserLabel,
    model\UserLabelIndex,
    validate\BaseValidate,
};

/**
 * 会员标签验证器
 * Class UserLableValidate
 * @package app\adminapi\validate\user
 */
class UserLableValidate extends BaseValidate
{
    protected $rule = [
        'id'                => 'require',
        'ids'               => 'require',
        'name'              => 'require|max:32|unique:'.UserLabel::class.',name',
        'label_type'        => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'                => '请选择标签',
        'ids.require'               => '请选择标签',
        'name.require'              => '请输入标签名称',
        'name.max'                  => '标签名称最多为32个字符',
        'name.unique'               => '标签名称已存在',
        'label_type.require'        => '请选择标签类型',
        'label_type.in'             => '标签类型错误',
    ];

    //商品添加验证
    public function sceneAdd()
    {
        return $this->remove(['id'=>'require','ids'=>'require']);
    }

    public function sceneDel(){
        return $this->only(['ids']);
//            ->append('ids','checkLable');
    }
    public function sceneEdit()
    {
        return $this->remove(['ids'=>'require']);
    }


//    public function checkLable($value,$rule,$data){
//
//        $user = UserLabelIndex::where(['label_id'=>$value])->select()->toArray();
//
//        if($user){
//            $labelIds = array_column($user,'label_id');
//            $labelName = UserLabel::where(['id'=>$labelIds])->column('name');
//            return implode('、',$labelName).'标签已有用户使用，无法删除';
//        }
//        return true;
//    }
}