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

namespace app\common\model;

use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use think\model\concern\SoftDelete;

/**
 * 订单日志
 * Class OrderLog
 * @package app\common\model
 */
class OrderLog extends BaseModel
{
    public function record($params)
    {
        $this->insert([
            'sid'          => request()->sid,
            'type'          => $params['type'],
            'channel'       => $params['channel'],
            'order_id'      => $params['order_id'],
            'operator_id'   => $params['operator_id'] ?? 0,
            'content'       => $params['content'] ?? OrderLogEnum::getRecordDesc($params['channel']),
            'create_time'   => time(),
            'update_time'   => time(),
        ]);
    }

    /**
     * @notes 变动方式获取器
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2021/8/6 3:24 下午
     */
    public function getChannelDescAttr($value,$data)
    {
        return OrderLogEnum::getRecordDesc($data['channel']);
    }

    /**
     * @notes 操作人获取器
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ljj
     * @date 2021/8/9 5:23 下午
     */
    public function getOperatorAttr($value,$data)
    {
        switch ($data['type']) {
            case 1:
                return '系统';
            case 2:
                return Admin::where('id',$data['operator_id'])->value('name');
            case 3:
                return User::where('id',$data['operator_id'])->value('nickname');
            default:
                return '未知';
        }
    }

}