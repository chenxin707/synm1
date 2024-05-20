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

namespace app\adminapi\lists\settings\shop;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\model\SetMealOrder;

class RenewLists extends BaseAdminDataLists
{
    public function lists(): array
    {
        $lists = SetMealOrder::field('id,sn,pay_way,pay_status,order_status,create_time,set_meal_price_snapshot')
            ->append(['time_desc', 'price', 'pay_way_desc', 'pay_status_desc', 'order_status_desc', 'set_meal_name'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }


    public function count(): int
    {
        return SetMealOrder::count();
    }

}
