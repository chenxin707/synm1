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
namespace app\adminapi\logic\theme;

use app\common\model\DecorateThemeConfig;

/**
 * 装修配置逻辑层
 * Class DecorateLogic
 * @package app\adminapi\logic\decorate
 */
class DecorateThemeConfigLogic
{

    /**
     * @notes 获取主题配置内容
     * @param string $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/4 20:33
     */
    public function getContent(string $type):array
    {
        $decorate = DecorateThemeConfig::where(['type' => $type])->field('id,content')->find()->toArray();
        return $decorate['content'] ?? [];
    }


    /**
     * @notes 设置主题配置内容
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/4 20:36
     */
    public function setContent(array $params):bool
    {
        $decorate = DecorateThemeConfig::where(['type' => $params['type']])->findOrEmpty();
        $decorate->type    = $params['type'];
        $decorate->content = $params['content'];
        $decorate->duokaiSave();
        return true;
    }

}