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
namespace app\shopapi\http\middleware;
use app\common\model\User;
use app\common\service\JsonService;

class InspectAccountMiddleware{


    /**
     * @notes 账号是否合法
     * @param $request
     * @param \Closure $next
     * @return mixed|\think\response\Json
     * @author 令狐冲
     * @date
     */
    public function handle($request, \Closure $next)
    {
        $userId = $request->userId;
        if($userId > 0){
            $disable = User::where(['id'=>$userId])->value('disable');

            if($disable){
                return JsonService::fail('您的账号被冻结，请联系客服。', [], -1, 1);

            }
        }
        return $next($request);

    }

}