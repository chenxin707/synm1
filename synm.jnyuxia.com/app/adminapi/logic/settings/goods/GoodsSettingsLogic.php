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

namespace app\adminapi\logic\settings\goods;


use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;

class GoodsSettingsLogic extends BaseLogic
{
    /**
     * @notes 设置商品配置
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/26 6:03 下午
     */
    public function setConfig($params)
    {
        ConfigService::set('goods_set', 'image', $params['image'] ?? '');
        ConfigService::set('goods_set', 'is_show', $params['is_show'] ?? 1);
        ConfigService::set('goods_set', 'show_price', $params['show_price'] ?? 1);
        return true;
    }

    /**
     * @notes 查看商品配置
     * @return array
     * @author ljj
     * @date 2021/7/26 6:15 下午
     */
    public function getConfig()
    {
        $image = ConfigService::get('goods_set', 'image', '');
        $image = empty($image) ? '' : FileService::getFileUrl($image);
        $config = [
            'image' => $image,
            'is_show' => ConfigService::get('goods_set', 'is_show', 1),
            'show_price' => ConfigService::get('goods_set', 'show_price', 1)
        ];
        return $config;
    }


    /**
     * @notes 设置商品采集配置
     * @param $params
     * @return bool
     * @author ljj
     * @date 2023/3/13 3:42 下午
     */
    public function setGatherKey($params)
    {
        ConfigService::set('goods_gather', 'key_99api', $params['key_99api'] ?? '');
        return true;
    }

    /**
     * @notes 获取商品采集配置
     * @return array
     * @author ljj
     * @date 2023/3/13 3:41 下午
     */
    public function getGatherKey()
    {
        return [
            'key_99api' => ConfigService::get('goods_gather', 'key_99api', '')
        ];
    }
}