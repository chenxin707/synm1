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
declare (strict_types=1);

namespace app\adminapi\http\middleware;

use app\common\exception\ControllerExtendException;
use app\common\model\PlatformShop;
use app\common\service\JsonService;
use app\common\logic\ShopLogic;



class SidMiddleware
{
    /**
     * @notes 获取商户id
     * @param $request
     * @param \Closure $next
     * @return mixed|\think\response\Json
     * @throws ControllerExtendException
     * @author Tab
     * @date 2021/12/13 17:58
     */
    public function handle($request, \Closure $next)
    {
        if (empty($request->adminInfo)) {
            // 未登录
            $request->sid = ShopLogic::getSid();
        } else {
            // 已登录
            $request->sid = $request->adminInfo['sid'];
        }
        $shop = PlatformShop::withoutGlobalScope()->findOrEmpty($request->sid);
        if ($shop->isEmpty()) {
            return JsonService::fail('商户不存在或访问链接错误', [], 0, 1);
        }
        if (!$shop->status) {
            return JsonService::fail('商户已停止服务', [], 0, 1);
        }
        return $next($request);
    }

}