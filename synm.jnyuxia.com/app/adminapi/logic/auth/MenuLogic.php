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

namespace app\adminapi\logic\auth;


use app\common\{
    model\DevAuth,
    logic\BaseLogic,
    enum\DefaultEnum
};

/**
 * 菜单逻辑层
 * Class MenuLogic
 * @package app\adminapi\logic\auth
 */
class MenuLogic extends BaseLogic
{
    /**
     * @notes 添加菜单权限
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/20 9:06 下午
     */
    public function add(array $params)
    {

        $menuId = DevAuth::order('id desc')->value('id');
        $menu = new DevAuth;
        $menu->id       = $menuId+1;
        $menu->name     = $params['name'];
        $menu->uri      = $params['uri'];
        $menu->type     = $params['type'];
        $menu->pid      = $params['pid'];
        $menu->alias    = $params['alias'];
        $menu->icon     = $params['icon'] ?? '';
        $menu->sort     = $params['sort'] ?? DefaultEnum::SORT;
        $menu->disable  = $params['disable'];
        return $menu->duokaiSave();
    }

    /**
     * @notes 编辑菜单权限
     * @param array $params
     * @return DevAuth
     * @author cjhao
     * @date 2021/8/23 17:21
     */
    public function edit(array $params)
    {
        return DevAuth::duokaiUpdate($params,[],['name','uri','type','pid','alias','icon','sort','disable'], '', false);

    }

    /**
     * @notes 删除菜单
     * @param int $id
     * @return bool
     * @author cjhao
     * @date 2021/8/23 17:28
     */
    public function del(int $id)
    {
        DevAuth::where(['id'=>$id])->useSoftDelete('delete_time', time())->delete();
        return true;
    }
}