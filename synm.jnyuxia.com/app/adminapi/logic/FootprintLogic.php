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

namespace app\adminapi\logic;

use app\common\logic\BaseLogic;
use app\common\model\Footprint;
use app\common\service\ConfigService;

class FootprintLogic extends BaseLogic
{
    /**
     * @notes 设置足迹气泡
     */
    public static function setConfig($params)
    {
        ConfigService::set('footprint', 'status', $params['status']);
        ConfigService::set('footprint', 'duration', $params['duration']);
        ConfigService::set('footprint', 'pages', json_encode($params['pages']));
    }

    /**
     * @notes 获取足迹气泡配置
     */
    public static function getConfig()
    {
        return [
            'status' => ConfigService::get('footprint', 'status', 1),
            'duration' => ConfigService::get('footprint', 'duration', 60),
            'pages' => ConfigService::get('footprint', 'pages', [1, 2]),
        ];
    }


    /**
     * @notes 足迹气泡列表
     */
    public static function lists()
    {
        return Footprint::select()->toArray();
    }


    /**
     * @notes 修改汽泡状态
     */
    public static function status($id)
    {
        $record = Footprint::find($id);
        $record->status = $record->status ? 0 : 1;
        $record->duokaiSave();
    }
}
