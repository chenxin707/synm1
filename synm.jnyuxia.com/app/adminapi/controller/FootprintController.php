<?php
namespace app\adminapi\controller;

use app\adminapi\logic\FootprintLogic;

class FootprintController extends BaseAdminController
{
    /**
     * @notes 设置足迹气泡
     */
    public function setConfig()
    {
        $params = $this->request->post();
        FootprintLogic::setConfig($params);
        return $this->success('设置成功', [], 1, 1);
    }

    /**
     * @notes 获取足迹气泡设置
     */
    public function getConfig()
    {
        $config = FootprintLogic::getConfig();
        $config['pages']  = array_map(function($item) {
            return (int)$item;
        }, $config['pages']);
        return $this->data($config);
    }

    /**
     * @notes 足迹汽泡列表
     */
    public function lists()
    {
        $lists = FootprintLogic::lists();

        return $this->data($lists);
    }


    /**
     * @notes 修改汽泡状态
     */
    public function status()
    {
        FootprintLogic::status(request()->post('id'));
        return $this->success('修改成功', [], 1, 1);
    }


}
