<?php
namespace app\adminapi\controller\lucky_draw;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\lucky_draw\LuckyDrawList;
use app\adminapi\lists\lucky_draw\LuckyDrawRecordList;
use app\adminapi\logic\lucky_draw\LuckyDrawLogic;
use app\adminapi\validate\lucky_draw\LuckyDrawValidate;
use app\common\service\JsonService;

/**
 * 幸运抽奖
 */
class LuckyDrawController extends BaseAdminController
{
    /**
     * @notes 获取奖品类型
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/24 9:49
     */
    public function getPrizeType()
    {
        $result = LuckyDrawLogic::getPrizeType();
        return JsonService::data($result);
    }

    /**
     * @notes 添加幸运抽奖活动
     * @author Tab
     * @date 2021/11/24 10:20
     */
    public function add()
    {
        $params = (new LuckyDrawValidate())->post()->goCheck('add');
        $result = LuckyDrawLogic::add($params);
        if ($result) {
            return JsonService::success('添加成功');
        }
        return JsonService::fail(LuckyDrawLogic::getError());
    }

    /**
     * @notes 查看幸运抽奖活动详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/24 14:04
     */
    public function detail()
    {
        $params = (new LuckyDrawValidate())->goCheck('detail');
        $result = LuckyDrawLogic::detail($params);
        return JsonService::data($result);
    }

    /**
     * @notes  编辑幸运抽奖活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/24 14:19
     */
    public function edit()
    {
        $params = (new LuckyDrawValidate())->post()->goCheck('edit');
        $result = LuckyDrawLogic::edit($params);
        if ($result) {
            return JsonService::success('编辑成功');
        }
        return JsonService::fail(LuckyDrawLogic::getError());
    }

    /**
     * @notes 开始活动
     * @author Tab
     * @date 2021/11/24 14:58
     */
    public function start()
    {
        $params = (new LuckyDrawValidate())->post()->goCheck('start');
        $result = LuckyDrawLogic::start($params);
        if ($result) {
            return JsonService::success('操作成功');
        }
        return JsonService::fail(LuckyDrawLogic::getError());
    }

    /**
     * @notes 结束活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/24 15:04
     */
    public function end()
    {
        $params = (new LuckyDrawValidate())->post()->goCheck('end');
        $result = LuckyDrawLogic::end($params);
        if ($result) {
            return JsonService::success('操作成功');
        }
        return JsonService::fail(LuckyDrawLogic::getError());
    }

    /**
     * @notes 删除幸运抽奖活动
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/24 15:13
     */
    public function delete()
    {
        $params = (new LuckyDrawValidate())->post()->goCheck('delete');
        $result = LuckyDrawLogic::delete($params);
        if ($result) {
            return JsonService::success('删除成功');
        }
        return JsonService::fail(LuckyDrawLogic::getError());
    }

    /**
     * @notes 幸运抽奖活动列表
     * @author Tab
     * @date 2021/11/24 15:19
     */
    public function lists()
    {
        return JsonService::dataLists(new LuckyDrawList());
    }

    /**
     * @notes 幸运抽奖记录
     * @author Tab
     * @date 2021/11/25 16:08
     */
    public function record()
    {
        $params = (new LuckyDrawValidate())->goCheck('record');
        return JsonService::dataLists(new LuckyDrawRecordList());
    }
}