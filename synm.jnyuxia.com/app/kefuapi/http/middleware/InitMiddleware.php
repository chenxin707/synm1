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

namespace app\kefuapi\http\middleware;


use app\common\exception\ControllerExtendException;
use app\common\service\JsonService;
use app\kefuapi\controller\BaseKefuController;
use think\exception\ClassNotFoundException;
use think\exception\HttpException;

class InitMiddleware
{
    /**
     * @notes 初始化
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @author 令狐冲
     * @date 2021/7/2 19:29
     */
    public function handle($request, \Closure $next)
    {
        //接口版本判断
        $version = $request->header('version');

        if (empty($version) && !$this->nocheck($request)) {
            // 指定show为0，前端不弹出此报错
            return JsonService::fail('请求参数缺少接口版本号', [], 0, 0);
        }

        //获取控制器
        try {
            $controller = str_replace('.', '\\', $request->controller());
            $controller = '\\app\\kefuapi\\controller\\' . $controller . 'Controller';
            $controllerClass = invoke($controller);
            if (($controllerClass instanceof BaseKefuController) === false) {
                throw new ControllerExtendException($controller, '404');
            }
        } catch (ClassNotFoundException $e) {
            throw new HttpException(404, 'controller not exists:' . $e->getClass());
        }
        //创建控制器对象
        $request->controllerObject = invoke($controller);

        return $next($request);
    }


    /**
     * @notes 是否验证版本号
     * @param $request
     * @return bool
     * @author 段誉
     * @date 2021/9/7 11:37
     */
    public function nocheck($request)
    {
        //特殊方法不验证版本号参数
        $noCheck = [];
        $requestAction = $request->controller() . '/'. $request->action();
        return in_array($requestAction, $noCheck);
    }

}