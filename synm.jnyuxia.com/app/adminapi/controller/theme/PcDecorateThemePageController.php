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
namespace app\adminapi\controller\theme;
use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\theme\DecorateThemePageLogic;
use app\adminapi\logic\theme\PcDecorateThemePageLogic;
use app\adminapi\validate\theme\PcDecoratePageValiadte;
use app\common\enum\PcDecorateThemePageEnum;

/**
 * pc端装修页面
 * Class PcDecorateThemePageController
 * @package app\adminapi\controller\theme
 */
class PcDecorateThemePageController extends BaseAdminController
{

    /**
     * @notes 首页主题
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/30 11:55
     */
    public function index()
    {

        $result = (new PcDecorateThemePageLogic())->index();
        return $this->success('',$result);

    }

    /**
     * @notes 获取页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/30 12:00
     */
    public function getPage()
    {
        $type = $this->request->get('type',PcDecorateThemePageEnum::TYPE_HOME);
        $detail = (new PcDecorateThemePageLogic())->getPage($type);
        return $this->success('', $detail);
    }


    /**
     * @notes 设置页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/30 15:34
     */
    public function setPage()
    {
        $params = (new PcDecoratePageValiadte())->post()->goCheck();
        (new PcDecorateThemePageLogic())->setPage($params);
        return $this->success('设置成功',[],1,1);
    }


    /**
     * @notes pc页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/12/6 17:06
     */
    public function getPcPage()
    {
        $params = $this->request->get('type','shop');
        $detail = (new PcDecorateThemePageLogic())->getPcPage($params);
        return $this->success('', $detail);
    }


}