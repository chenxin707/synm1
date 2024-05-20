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
namespace app\adminapi\validate\theme;

use app\common\enum\ThemePageEnum;
use app\common\model\DecorateThemePage;
use app\common\validate\BaseValidate;

/**
 * 主题页面验证器
 * Class ThemePageValidate
 * @package app\adminapi\validate\decorate
 */
class DecoratePageValidate extends BaseValidate
{

    protected $rule = [
        'id'        => 'require',
        'type'      => 'require|in:'.ThemePageEnum::TYPE_HOME.','.ThemePageEnum::TYPE_MEMBER_CENTRE.','.ThemePageEnum::TYPE_GOODS_CATEGORY,
        'content'   => 'require',
        'common'    => 'require',
    ];

    protected $message = [
        'id.require'        => '请选择主题页面',
        'type.require'      => '请选择页面类型',
        'type.int'          => '页面类型错误',
        'content'           => '请设置页面内容',
        'common'            => '请设置公告配置',
    ];

    public function sceneDel(){
        return $this->only(['id'])->append('checkDelPage');
    }
    public function sceneSetHome(){
        return $this->only(['id'])->append('checkPage');
    }

    public function sceneEdit(){
        return $this->only(['id','content','common']);
    }


    //删除主页
    public function checkDelPage($value,$rule,$data){
        $page = DecorateThemePage::find($value);
        if($page->is_home){
            return '该页面设置为首页，无法删除';
        }
        return true;
    }

    //验证主题页面
    public function checkPage($value,$rule,$data)
    {
        $page = DecorateThemePage::find($value);
        if(!$page){
            return '页面不存在';
        }
        if(ThemePageEnum::TYPE_HOME !== $page['type']){
            return '该页面不是首页类型，不能设置为主页';
        }
        return true;
    }

}