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

namespace app\platformapi\validate\settings;

use app\common\validate\BaseValidate;

class PlatformValidate extends BaseValidate
{
    protected $rule = [
        'platform_name' => 'require|max:12',
        'document_status' => 'require|in:0,1|checkDocumentStatus',
    ];

    protected $message = [
        'platform_name.require' => '请输入平台名称',
        'platform_name.max' => '平台名称不能超过12个字符',
        'document_status.require' => '请选择文档信息开关',
        'document_status.in' => '文档信息值错误',
    ];

    public function sceneSetBaseConfig()
    {
        return $this->only(['platform_name','document_status']);
    }


    /**
     * @notes 校验产品授权
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2023/5/16 11:25 上午]
     */
    public function checkDocumentStatus($value,$rule,$data)
    {
        if ($value == 0) {
            $check_domain = config('project.check_domain');
            $product_code = config('project.product_code');
            $domain = $_SERVER['HTTP_HOST'];
            $result = \Requests::get($check_domain.'/api/version/productAuth?code='.$product_code.'&domain='.$domain);
            $result = json_decode($result->body,true);
            if (!$result['data']['result']) {
                return '产品未授权，要去官网授权才能操作';
            }
        }

        return true;
    }

}