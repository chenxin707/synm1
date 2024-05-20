<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\shopapi\logic;

use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\NoticeSetting;

/**
 * 小程序订阅消息
 */
class SubscribeLogic extends BaseLogic
{
    /**
     * @notes 获取小程序模板ID (取已启用的3条)
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/10/12 11:56
     */
    public static function lists()
    {
        $where = [
            ['mnp_notice', '<>', ''],
            ['type', '=', 1]
        ];
        $lists = NoticeSetting::where($where)->field('mnp_notice')->select()->toArray();

        $template_id = [];
        foreach ($lists as $item) {
            if (isset($item['mnp_notice']['status']) && $item['mnp_notice']['status'] != YesNoEnum::YES) {
                continue;
            }
            $template_id[] = $item['mnp_notice']['template_id'] ?? '';
            // 限制3条
            if (count($template_id) == 3) {
                break;
            }
        }
        return $template_id;
    }
}