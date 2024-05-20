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

namespace app\platformapi\controller;

use app\platformapi\logic\WorkbenchLogic;

/**
 * 平台工作台控制器
 */
class WorkbenchController extends BasePlatformController
{
    /**
     * @notes 平台工作台
     * @return mixed
     * @author Tab
     * @date 2021/12/21 11:45
     */
    public function index()
    {
        $result = WorkbenchLogic::index($this->platformAdminInfo);
        return $this->data($result);
    }

    /**
     * @notes 商城用户数量排行榜
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/21 12:05
     */
    public function topUser()
    {
        $params = request()->get();
        $params['page_no'] = isset($params['page_no']) && !empty($params['page_no']) ? (int)$params['page_no']: 1;
        $params['page_size'] = isset($params['page_size']) && !empty($params['page_size']) ? (int)$params['page_size']: 10;
        $params['page_size'] = $params['page_size'] > 50 ? 50 : $params['page_size']; // 限制每页最多显示50条
        $result = WorkbenchLogic::topUser($params);
        return $this->data($result);
    }

    /**
     * @notes 商城营业额排行榜
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/21 12:05
     */
    public function topAmount()
    {
        $params = request()->get();
        $params['page_no'] = isset($params['page_no']) && !empty($params['page_no']) ? (int)$params['page_no']: 1;
        $params['page_size'] = isset($params['page_size']) && !empty($params['page_size']) ? (int)$params['page_size']: 10;
        $params['page_size'] = $params['page_size'] > 50 ? 50 : $params['page_size']; // 限制每页最多显示50条
        $result = WorkbenchLogic::topAmount($params);
        return $this->data($result);
    }


    /**
     * @notes 检测新版本
     * @return \think\response\Json
     * @author ljj
     * @date 2023/5/25 7:02 下午
     */
    public function checkVersion()
    {
        $data = WorkbenchLogic::checkVersion();
        return $this->data($data);
    }

    /**
     * @notes 正版检测
     * @return \think\response\Json
     * @author ljj
     * @date 2023/5/16 11:49 上午
     */
    public function checkLegal()
    {
        $data = WorkbenchLogic::checkLegal();
        return $this->data($data);
    }
}