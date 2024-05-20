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

namespace app\adminapi\validate\express_assistant;

use app\common\enum\DeliveryEnum;
use app\common\model\Express;
use app\common\model\FaceSheetTemplate;
use app\common\validate\BaseValidate;

/**
 * 电子面单模板验证器
 */
class FaceSheetTemplateValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkTemplate',
        'name' => 'require|checkName',
        'template_id' => 'require',
        'express_id' => 'require|checkExpress',
//        'partner_id' => 'require',
//        'partner_key' => 'require',
        'net' => 'require',
        'pay_type' => 'require|checkPayType',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'name.require' => '请输入模板名称',
        'template_id.require' => '请输入模板ID',
        'express_id.require' => '请选择快递公司',
//        'partner_id.require' => '请输入电子面单客户账号',
//        'partner_key.require' => '请输入电子面单密码',
        'net.require' => '请输入网点标识',
        'pay_type.require' => '请选择运费支付方式',
    ];

    public function sceneAdd()
    {
        return $this->only(['name', 'template_id', 'express_id', 'net', 'pay_type']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'template_id', 'express_id', 'net', 'pay_type']);
    }

    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验模板名称
     * @param $name
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/11/22 14:46
     */
    public function checkName($name, $rule, $data)
    {
        $where = [['name', '=', $name]];
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $template = FaceSheetTemplate::where($where)->findOrEmpty();
        if ($template->isEmpty()) {
            return true;
        }
        return '该模板名称已被使用，请重新输入';
    }

    /**
     * @notes 校验快递公司
     * @param $expressId
     * @author Tab
     * @date 2021/11/22 14:47
     */
    public function checkExpress($expressId)
    {
        $express = Express::findOrEmpty($expressId);
        if ($express->isEmpty()) {
            return '快递公司不存在';
        }
        return true;
    }

    /**
     * @notes 校验运费支付方式
     * @param $payType
     * @author Tab
     * @date 2021/11/22 14:49
     */
    public function checkPayType($payType)
    {
        $payTypeDesc = DeliveryEnum::getFaceSheetPaymentDesc($payType);
        if (empty($payTypeDesc) || is_array($payTypeDesc)) {
            // 未知支付方式 或 null值
            return '支付方式错误';
        }
        return true;
    }

    /**
     * @notes 校验电子面单模板
     * @param $templateId
     * @return bool|string
     * @author Tab
     * @date 2021/11/22 15:00
     */
    public function checkTemplate($templateId)
    {
        $template = FaceSheetTemplate::findOrEmpty($templateId);
        if ($template->isEmpty()) {
            return '模板不存在';
        }
        return true;
    }
}