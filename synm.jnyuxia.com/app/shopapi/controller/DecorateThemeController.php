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
namespace app\shopapi\controller;

use app\common\enum\ThemePageEnum;
use app\shopapi\logic\DecorateThemeLogic;

/**
 * 装修主题
 * Class DecorateThemeController
 * @package app\shopapi\controller
 */
class DecorateThemeController extends BaseShopController
{
    public array $notNeedLogin = ['index', 'getConfig','getPage','getIndexPage', 'visit'];

    /**
     * @notes 获取主题首页
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/12 17:35
     */
    public function index()
    {
        $index = (new DecorateThemeLogic())->index($this->userId);
        return $this->success('', $index);
    }

    /**
     * @notes 主题配置
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/13 10:18
     */
    public function getConfig()
    {
        $config = (new DecorateThemeLogic())->getConfig();
        return $this->success('', $config);

    }


    /**
     * @notes 根据类型获取主题页面（个人中心和商品分类）
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/12 15:02
     */
    public function getPage()
    {
        $type = $this->request->get('type', ThemePageEnum::TYPE_MEMBER_CENTRE);
        $goodsId = $this->request->get('goods_id','');
        $detail = (new DecorateThemeLogic())->getPage($type,$this->userId,$goodsId);
        return $this->success('', $detail);
    }

    /**
     * @notes 获取首页微页面
     * @author cjhao
     * @date 2021/9/1 14:51
     */
    public function getIndexPage()
    {
        $id = $this->request->get('id');
        $detail = (new DecorateThemeLogic())->getIndexPage($id);
        return $this->success('', $detail);
    }

}
