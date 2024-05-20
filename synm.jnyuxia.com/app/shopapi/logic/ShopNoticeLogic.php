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

namespace app\shopapi\logic;


use app\common\enum\UserTerminalEnum;
use app\common\logic\BaseLogic;
use app\common\model\ShopNotice;

class ShopNoticeLogic extends BaseLogic
{
    /**
     * @notes 查看商城公告详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 7:25 下午
     */
    public function detail($params)
    {
        $shop_notice = ShopNotice::find($params['id']);
        $shop_notice->views = $shop_notice->views + 1;
        $shop_notice->duokaiSave();
        $shop_notice = $shop_notice->toArray();

        // 按浏览量，取5条
        $shop_notice['newest'] = ShopNotice::field('id,image,name,views')
            ->order([
                'views' => 'desc',
                'id' => 'desc',
            ])
            ->limit(5)->select()->toArray();

        return $shop_notice;
    }
}