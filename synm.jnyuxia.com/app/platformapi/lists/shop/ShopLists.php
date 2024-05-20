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

namespace app\platformapi\lists\shop;

use app\common\enum\YesNoEnum;
use app\common\lists\ListsExcelInterface;
use app\common\model\Config;
use app\common\model\Order;
use app\common\model\PlatformShop;
use app\common\model\SetMeal;
use app\common\model\User;
use app\common\service\FileService;
use app\platformapi\lists\BasePlatformDataLists;

/**
 * 商城列表
 */
class ShopLists extends BasePlatformDataLists implements ListsExcelInterface
{

    public function setFileName(): string
    {
        return '商城列表';
    }

    public function setExcelFields(): array
    {
        return [
            'id' => '商城id',
            'sn' => '商城编号',
            'name' => '商城名称',
            'account' => '管理员账号',
            'status_desc' => '商城状态',
            'create_time' => '创建时间',
        ];
    }

    public function attachSearch()
    {
        $this->searchWhere[] = ['a.root', '=', YesNoEnum::YES];

        if (isset($this->params['shop_info']) && trim($this->params['shop_info']) !== '') {
            $this->searchWhere[] = ['ps.sn|ps.name', 'like', '%' . trim($this->params['shop_info']) . '%'];
        }
        if (isset($this->params['status']) && trim($this->params['status']) !== '') {
            $this->searchWhere[] = ['ps.status', '=', $this->params['status']];
        }

        if (isset($this->params['set_meal_id']) && trim($this->params['set_meal_id']) !== '') {
            $this->searchWhere[] = ['ps.set_meal_id', '=', $this->params['set_meal_id']];
        }

        if (isset($this->params['set_meal_status']) && trim($this->params['set_meal_status']) !== '') {
            switch ($this->params['set_meal_status']) {
                case 1: // 正常
                    $this->searchWhere[] = ['ps.expires_time', '>', time() + 7 * 24 * 3600];
                    break;
                case 2: // 即将过期
                    $this->searchWhere[] = ['ps.expires_time', '>', time()];
                    $this->searchWhere[] = ['ps.expires_time', '<=', time() + 7 * 24 * 3600];
                    break;
                case 3: // 已过期
                    $this->searchWhere[] = ['ps.expires_time', '<=', time()];
                    break;
            }
        }

        if (isset($this->params['time_type']) && isset($this->params['start_time'])) {
            switch ($this->params['time_type']) {
                case 'create_time':
                    $this->searchWhere[] = ['ps.create_time','>=', strtotime($this->params['start_time'])];
                    $this->searchWhere[] = ['ps.create_time','<=', strtotime($this->params['end_time'])];
                    break;
                case 'expires_time':
                    $this->searchWhere[] = ['ps.expires_time','>=', strtotime($this->params['start_time'])];
                    $this->searchWhere[] = ['ps.expires_time','<=', strtotime($this->params['end_time'])];
                    break;
            }
        }
    }

    public function lists(): array
    {
        $this->attachSearch();

        $field = [
            'ps.id',
            'ps.sn',
            'ps.name',
            'ps.status',
            'a.account',
            'ps.domain_alias',
            'a.create_time',
            'ps.set_meal_id',
            'ps.expires_time',
        ];
        $lists = PlatformShop::withoutGlobalScope()
            ->alias('ps')
            ->append(['status_desc', 'shop_admin_url','set_meal_name','expires_status'])
            ->leftJoin('admin a', 'a.sid = ps.id')
            ->field($field)
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('ps.id', 'desc')
            ->select()
            ->toArray();

        //用户统计
        $userNum = User::withoutGlobalScope()->group('sid')->column('count(id)','sid');

        //营业额统计
        $orderAmount = Order::withoutGlobalScope()
            ->where(['pay_status'=>YesNoEnum::YES])
            ->group('sid')
            ->column('sum(order_amount)','sid');

        //商家logo
        $shopLogos = Config::withoutGlobalScope()->where(['type'=>'shop','name'=>'logo'])->column('value','sid');
        $defaultLogo = config('project.shop.logo');

        foreach ($lists as $key =>$shop){

            $logo = $shopLogos[$shop['id']] ?? $defaultLogo;
            $domain = request()->scheme() . '://shop' . $shop['sn'] . '.' . secondary_domain();
            $lists[$key]['logo'] = FileService::getFileUrl($logo);
            //域名别名
            if($shop['domain_alias']){
                $lists[$key]['shop_admin_url'] = $shop['domain_alias']. '/admin';
                $lists[$key]['shop_pc_url'] = $shop['domain_alias']. '/pc';
                $lists[$key]['shop_mobile_url'] = $shop['domain_alias']. '/mobile';

            }else{

                $lists[$key]['shop_admin_url'] = $domain. '/admin';
                $lists[$key]['shop_pc_url'] = $domain. '/pc';
                $lists[$key]['shop_mobile_url'] = $domain . '/mobile';

            }

            $lists[$key]['user_num'] = $userNum[$shop['id']] ?? 0;
            $lists[$key]['order_amount'] = $orderAmount[$shop['id']] ?? 0;

        }

        return $lists;
    }

    public function count(): int
    {
        $this->attachSearch();

        $field = [
            'ps.id',
            'ps.sn',
            'ps.name',
            'ps.status',
            'a.account',
        ];
        $count = PlatformShop::withoutGlobalScope()
            ->alias('ps')
            ->append(['status_desc'])
            ->leftJoin('admin a', 'a.sid = ps.id')
            ->field($field)
            ->where($this->searchWhere)
            ->count();

        return $count;
    }
}
