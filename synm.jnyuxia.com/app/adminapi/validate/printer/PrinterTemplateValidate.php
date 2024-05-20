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

namespace app\adminapi\validate\printer;

use app\common\model\Printer;
use app\common\model\PrinterTemplate;
use app\common\validate\BaseValidate;

class PrinterTemplateValidate extends BaseValidate
{
    protected $rule = [
        'template_name' => 'require|max:30|checkTempateName',
        'ticket_name' => 'require|max:30',
        'show_shop_name' => 'require|in:0,1',
        'selffetch_shop' => 'require|checkSelffetchShop',
        'show_buyer_message' => 'require|in:0,1',
        'consignee_info' => 'require|checkConsigneeInfo',
        'verification_info' => 'require|checkVerificationInfo',
        'show_qrcode' => 'require|in:0,1',
        'bottom' => 'require|max:120',
        'id' => 'require|checkId',

    ];

    protected $message = [
        'template_name.require'   => '请输入模板名称',
        'template_name.max'   => '模板名称不能超过30个字符',
        'ticket_name.require'   => '请输入小票名称',
        'ticket_name.max'   => '小票名称不能超过30个字符',
        'show_shop_name.require'   => '请选择是否显示商城名称',
        'selffetch_shop.require'   => '请选择自提门店信息',
        'show_shop_name.in'   => 'show_shop_name状态值错误',
        'show_buyer_message.require'   => '请选择是否显示买家留言',
        'show_buyer_message.in'   => 'show_buyer_message状态值错误',
        'consignee_info.require'   => '请选择收货人信息',
        'verification_info.require' => '请选择提货人信息',
        'show_qrcode.require'   => '请选择是否显示二维码',
        'show_qrcode.in'   => 'show_qrcode状态值错误',
        'bottom.require'   => '请输入底部信息',
        'bottom.max'   => '底部信息不能超过120个字符',
        'id.require'   => '参数缺失',
    ];

    public function sceneAddTemplate()
    {
        return $this->only(['template_name', 'ticket_name', 'show_shop_name', 'selffetch_shop', 'show_buyer_message', 'consignee_info', 'show_qrcode', 'bottom','verification_info']);
    }

    public function sceneEditTemplate()
    {
        return $this->only(['template_name', 'ticket_name', 'show_shop_name', 'selffetch_shop', 'show_buyer_message', 'consignee_info', 'show_qrcode', 'bottom', 'id','verification_info']);
    }

    public function sceneDeleteTemplate()
    {
        return $this->only(['id'])
                ->append('id', "checkTempateUsed");
    }

    public function sceneTemplateDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验模板ID
     * @param $id
     * @return bool|string
     * @author Tab
     * @date 2021/11/15 17:30
     */
    public function checkId($id)
    {
        $template = PrinterTemplate::findOrEmpty($id);
        if ($template->isEmpty()) {
            return '模板不存在';
        }
        return true;
    }

    /**
     * @notes 校验模板名称
     * @param $name
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/11/15 17:54
     */
    public function checkTempateName($templateName, $rule, $data)
    {
        $where = [
            ['template_name', '=', $templateName]
        ];
        if (isset($data['id'])) {
            // 编辑
            $where[] = ['id', '<>', $data['id']];
        }
        $template = PrinterTemplate::where($where)->findOrEmpty();
        if (!$template->isEmpty()) {
            return '该模板名称已被使用，请重新输入';
        }
        return true;
    }

    /**
     * @notes 校验自提门店参数
     * @param $value
     * @return string|void
     * @author Tab
     * @date 2021/11/15 18:14
     */
    public function checkSelffetchShop($value)
    {
        if (!is_array($value)) {
            return '自提门店须为数组格式';
        }
        if (!isset($value['shop_name']) || !isset($value['contacts']) || !isset($value['shop_address'])) {
            return '自提门店参数不完整';
        }
        return true;
    }


    /**
     * @notes 校验提货人信息参数
     * @param $value
     * @return string|void
     * @author Tab
     * @date 2021/11/15 18:14
     */
    public function checkVerificationInfo($value)
    {
        if (!is_array($value)) {
            return '提货人信息须为数组格式';
        }
        if (!isset($value['contacts']) || !isset($value['mobile']) || !isset($value['pickup_code'])) {
            return '提货人信息参数不完整';
        }
        return true;
    }

    /**
     * @notes 校验收货人信息参数
     * @param $value
     * @return string|void
     * @author Tab
     * @date 2021/11/15 18:14
     */
    public function checkConsigneeInfo($value)
    {
        if (!is_array($value)) {
            return '收货人信息须为数组格式';
        }
        if (!isset($value['name']) || !isset($value['mobile']) || !isset($value['address'])) {
            return '收货人信息参数不完整';
        }
        return true;
    }

    /**
     * @notes 校验模板是否被打印机使用
     * @param $value
     * @return bool|string
     * @author Tab
     * @date 2021/11/16 9:50
     */
    public function checkTempateUsed($value)
    {
        $printer = Printer::where('template_id', $value)->findOrEmpty();
        if (!$printer->isEmpty()) {
            return '有打印机正在使用该模板，不允许删除';
        }
        return true;
    }
}