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

namespace app\adminapi\validate\kefu;

use app\common\model\KefuLang;
use app\common\validate\BaseValidate;

/**
 * 客服话术验证器
 * Class KefuLangValidate
 * @package app\adminapi\validate\kefu
 */
class KefuLangValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkLang',
        'title' => 'require|unique:' . KefuLang::class . ',title',
        'content' => 'require|unique:' . KefuLang::class . ',content',
        'sort' => 'egt:0|integer',
    ];

    protected $message = [
        'title.require' => '请输入标题',
        'title.unique' => '标题已存在',
        'content.require' => '请输入内容',
        'content.unique' => '内容已存在',
        'sort.egt'      => '排序不能小于零',
        'sort.integer'  => '排序值只能是整数',
    ];


    public function sceneAdd()
    {
        $this->remove('id', true);
    }

    public function sceneDel()
    {
        $this->only(['id']);
    }


    public function checkLang($value, $rule, $data)
    {
        $lang = KefuLang::findOrEmpty($value);

        if ($lang->isEmpty()) {
            return '话术不存在';
        }

        return true;
    }
}