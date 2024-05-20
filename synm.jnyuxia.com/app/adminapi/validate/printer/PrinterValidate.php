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

class PrinterValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:30|checkName',
        'type' => 'require|in:1',
        'machine_code' => 'require|max:120',
        'private_key' => 'require|max:120',
        'client_id' => 'require|max:120',
        'client_secret' => 'require|max:120',
        'template_id' => 'require|checkTemplate',
        'print_number' => 'require|in:1,2,3,4',
        'auto_print' => 'require|in:0,1',
        'status' => 'require|in:0,1',
        'id' => 'require|checkId',

    ];

    protected $message = [
        'name.require'   => '请输入打印机名称',
        'name.max'   => '打印机名称不能超过30个字符',
        'type.require'   => '请选择设备类型',
        'type.in'   => '设备类型值错误',
        'machine_code.require'   => '请输入终端号',
        'machine_code.max'   => '终端号不能超过120个字符',
        'private_key.require'   => '请输入打印机密钥',
        'private_key.max'   => '打印机秘钥不能超过120个字符',
        'client_id.require'   => '请输入应用ID',
        'client_id.max'   => '应用ID不能超过120个字符',
        'client_secret.require'   => '请输入应用秘钥',
        'client_secret.max'   => '应用秘钥不能超过120个字符',
        'template_id.require'   => '请选择模板ID',
        'print_number.require'   => '请选择打印联数',
        'print_number.in'   => '联数值错误',
        'auto_print.require'   => '请选择自动打印状态',
        'auto_print.in'   => '自动打印状态值错误',
        'status.require'   => '请选择启动状态',
        'status.in'   => '启动状态值错误',
        'id.require'   => '参数缺失',
    ];

    public function sceneAddPrinter()
    {
        return $this->only(['name', 'type', 'machine_code', 'private_key', 'client_id', 'client_secret', 'template_id', 'print_number', 'auto_print', 'status']);
    }

    public function sceneEditPrinter()
    {
        return $this->only(['name', 'type', 'machine_code', 'private_key', 'client_id', 'client_secret', 'template_id', 'print_number', 'auto_print', 'status', 'id']);
    }

    public function sceneDeletePrinter()
    {
        return $this->only(['id']);
    }

    public function scenePrinterDetail()
    {
        return $this->only(['id']);
    }

    public function sceneTestPrinter()
    {
        return $this->only(['id']);
    }

    public function sceneAutoPrint()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验模板ID
     * @param $templateId
     * @return bool|string
     * @author Tab
     * @date 2021/11/15 17:01
     */
    public function checkTemplate($templateId)
    {
        $template = PrinterTemplate::findOrEmpty($templateId);
        if ($template->isEmpty()) {
            return '模板不存在';
        }
        return true;
    }

    /**
     * @notes 校验打印机ID
     * @param $id
     * @return bool|string
     * @author Tab
     * @date 2021/11/15 17:30
     */
    public function checkId($id)
    {
        $printer = Printer::findOrEmpty($id);
        if ($printer->isEmpty()) {
            return '打印机不存在';
        }
        return true;
    }

    /**
     * @notes 校验打印机名称
     * @param $name
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/11/15 17:54
     */
    public function checkName($name, $rule, $data)
    {
        $where = [
            ['name', '=', $name]
        ];
        if (isset($data['id'])) {
            // 编辑
            $where[] = ['id', '<>', $data['id']];
        }
        $printer = Printer::where($where)->findOrEmpty();
        if (!$printer->isEmpty()) {
            return '该名称已被使用，请重新输入';
        }
        return true;
    }
}