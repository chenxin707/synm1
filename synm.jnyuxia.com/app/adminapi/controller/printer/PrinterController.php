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

namespace app\adminapi\controller\printer;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\printer\PrinterList;
use app\adminapi\lists\printer\PrinterTemplateList;
use app\adminapi\logic\printer\PrinterLogic;
use app\adminapi\validate\printer\PrinterTemplateValidate;
use app\adminapi\validate\printer\PrinterValidate;
use app\common\service\JsonService;

/**
 * 小票打印
 */
class PrinterController extends BaseAdminController
{
    /**
     * @notes 打印机列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 17:45
     */
    public function printerLists()
    {
        return JsonService::dataLists(new PrinterList());
    }

    /**
     * @notes 获取打印机类型
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 16:15
     */
    public function getPrinterType()
    {
        return JsonService::success('', PrinterLogic::getPrinterType(), 1, 0);
    }

    /**
     * @notes 添加打印机
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 17:14
     */
    public function addPrinter()
    {
        $params = (new PrinterValidate())->post()->goCheck('addPrinter');
        $result = PrinterLogic::addPrinter($params);
        if ($result) {
            return JsonService::success('添加成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 获取打印机详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/16 11:48
     */
    public function printerDetail()
    {
        $params = (new PrinterValidate())->goCheck('printerDetail');
        $result = PrinterLogic::printerDetail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 编辑打印机
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 17:24
     */
    public function editPrinter()
    {
        $params = (new PrinterValidate())->post()->goCheck('editPrinter');
        $result = PrinterLogic::editPrinter($params);
        if ($result) {
            return JsonService::success('编辑成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 删除打印机
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 18:49
     */
    public function deletePrinter()
    {
        $params = (new PrinterValidate())->post()->goCheck('deletePrinter');
        $result = PrinterLogic::deletePrinter($params);
        if ($result) {
            return JsonService::success('删除成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 添加模板
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 18:17
     */
    public function addTemplate()
    {
        $params = (new PrinterTemplateValidate())->post()->goCheck('addTemplate');
        $result = PrinterLogic::addTemplate($params);
        if ($result) {
            return JsonService::success('添加成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 编辑模板
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 18:41
     */
    public function editTemplate()
    {
        $params = (new PrinterTemplateValidate())->post()->goCheck('editTemplate');
        $result = PrinterLogic::editTemplate($params);
        if ($result) {
            return JsonService::success('编辑成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 删除模板
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 18:45
     */
    public function deleteTemplate()
    {
        $params = (new PrinterTemplateValidate())->post()->goCheck('deleteTemplate');
        $result = PrinterLogic::deleteTemplate($params);
        if ($result) {
            return JsonService::success('删除成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 模板列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/15 18:49
     */
    public function printerTemplateLists()
    {
        return JsonService::dataLists(new PrinterTemplateList());
    }

    /**
     * @notes 获取模板详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/16 17:54
     */
    public function templateDetail()
    {
        $params = (new PrinterTemplateValidate())->goCheck('templateDetail');
        $result = PrinterLogic::templateDetail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 测试打印机
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/16 17:56
     */
    public function testPrinter() {
        $params = (new PrinterValidate())->post()->goCheck('testPrinter');
        $result = PrinterLogic::testPrinter($params);
        if ($result) {
            return JsonService::success('测试完成', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }

    /**
     * @notes 自动打印状态切换
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/17 10:31
     */
    public function autoPrint()
    {
        $params = (new PrinterValidate())->post()->goCheck('autoPrint');
        $result = PrinterLogic::autoPrint($params);
        if ($result) {
            return JsonService::success('修改成功', [], 1, 1);
        }
        return JsonService::fail(PrinterLogic::getError());
    }
}