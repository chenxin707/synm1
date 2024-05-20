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

namespace app\adminapi\controller;

use app\adminapi\logic\WorkbenchLogic;

/**
 * 工作台
 * Class WorkbenchCotroller
 * @package app\adminapi\controller
 */
class WorkbenchController extends BaseAdminController
{
    /**
     * @notes 工作台
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/10 14:02
     */
    public function index()
    {
        $result = WorkbenchLogic::index($this->adminInfo);
        return $this->data($result);
    }

    /**
     * @notes 商品排行榜 - 按销售额排序
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/11 11:34
     */
    public function topGoods50()
    {
        $params = request()->get();
        $params['page_no'] = isset($params['page_no']) && !empty($params['page_no']) ? (int)$params['page_no']: 1;
        $params['page_size'] = isset($params['page_size']) && !empty($params['page_size']) ? (int)$params['page_size']: 10;
        $params['page_size'] = $params['page_size'] > 50 ? 50 : $params['page_size']; // 限制每页最多显示50条
        $result = WorkbenchLogic::topGoods50($params);
        return $this->data($result);
    }

    /**
     * @notes 用户排行榜 - 按用户累计购买金额
     * @return \think\response\Json
     * @author Tab
     * @date 2021/12/11 11:34
     */
    public function topUser50()
    {
        $params = request()->get();
        $params['page_no'] = isset($params['page_no']) && !empty($params['page_no']) ? (int)$params['page_no']: 1;
        $params['page_size'] = isset($params['page_size']) && !empty($params['page_size']) ? (int)$params['page_size']: 10;
        $params['page_size'] = $params['page_size'] > 50 ? 50 : $params['page_size']; // 限制每页最多显示50条
        $result = WorkbenchLogic::topUser50($params);
        return $this->data($result);
    }

    /**
     * @notes 获取平台客服
     * @return \think\response\Json
     * @author cjhao
     * @date 2022/3/9 10:33
     */
    public function getPlatformService(){
        $result = WorkbenchLogic::getPlatformService();
        return $this->data($result);
    }
}