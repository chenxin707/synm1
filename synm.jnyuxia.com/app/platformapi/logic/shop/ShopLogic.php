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

namespace app\platformapi\logic\shop;


use app\common\enum\SetMealLogEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Admin;
use app\common\model\Goods;
use app\common\model\Order;
use app\common\model\PlatformShop;
use app\common\model\SetMeal;
use app\common\model\SetMealLog;
use app\common\model\User;
use app\common\model\Config as ConfigModel;
use think\facade\Db;
use think\facade\Config;

/**
 * 平台设置
 */
class ShopLogic extends BaseLogic
{
    /**
     * @notes 添加商城
     * @param $params
     * @author Tab
     * @date 2021/12/14 11:50
     */
    public static function add($params)
    {
        Db::startTrans();
        try {

            $data = [
                'sn' => create_shop_sn((new PlatformShop()), 8, 'lower'),
                'name' => $params['name'],
                'contact' => $params['contact'] ?? '',
                'contact_mobile' => $params['contact_mobile'] ?? '',
                'domain_alias' => $params['domain_alias'] ?? '',
                'status' => $params['status'],
                'set_meal_id' => $params['set_meal_id'],
                'remark' => $params['remark'] ?? '',
                'expires_time' => strtotime($params['expires_time']),
            ];
            // 创建新商户
            $shop = PlatformShop::duokaiCreate($data, [], false, '', false);

            // 为新商户创建超级管理员
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['super_password'], $passwordSalt);
            $superData = [
                'root' => 1,
                'name' => '超级管理员',
                'avatar' => Config::get('project.default_image.admin_avatar'),
                'account' => $params['super_admin'],
                'password' => $password,
                'sid' => $shop->id
            ];
            Admin::duokaiCreate($superData, [], false, '', false);

            // 为新商户创建一份默认数据
            (new ShopDefaultDataLogic($shop->id,false))->generateDefaultData();


            //创建商户套餐使用记录
            $set_meal_name = SetMeal::where('id', $params['set_meal_id'])->value('name');
            SetMealLog::create([
                'sid' => $shop->id,
                'type' => SetMealLogEnum::TYPE_PLATFORM,
                'operator_id' => $params['platform_id'],
                'set_meal_id' => $params['set_meal_id'],
                'origin_set_meal_id' => $params['set_meal_id'],
                'set_meal_name' => $set_meal_name,
                'origin_set_meal_name' => $set_meal_name,
                'content' => SetMealLogEnum::getRecordDesc(SetMealLogEnum::PLATFORM_OPEN_SHOP),
                'channel' => SetMealLogEnum::PLATFORM_OPEN_SHOP,
                'expires_time' => strtotime($params['expires_time']),
                'origin_expires_time' => strtotime($params['expires_time']),
            ]);
            $configModel = new ConfigModel();
            $configModel->type  = 'shop';
            $configModel->name  = 'name';
            $configModel->sid   = $shop->id;
            $configModel->value = $params['name'];
            $configModel->duokaiSave([], null, false, false);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
//            dd($e->getLine());
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 商城详情
     * @author Tab
     * @date 2021/12/15 10:29
     */
    public static function detail($params)
    {
        $field = ['id', 'sn', 'name', 'contact', 'contact_mobile', 'status', 'domain_alias', 'create_time', 'expires_time', 'set_meal_id', 'remark'];
        $shop = PlatformShop::withoutGlobalScope()->field($field)->findOrEmpty($params['id']);
        if ($shop->isEmpty()) {
            return [];
        }
        $shop->append(['shop_url']);
        $shopSuperAdmin = Admin::withoutGlobalScope()->where(['sid' => $shop->id, 'root' => YesNoEnum::YES])->value('account');
        $shopDetail = $shop->toArray();
        $shopDetail['account'] = $shopSuperAdmin;
        // 营业额
        $orderInfo = Order::withoutGlobalScope()
            ->where(['pay_status' => YesNoEnum::YES, 'sid' => $params['id']])
            ->field('sum(order_amount) as total_order_amout,count(id) as order_num')
            ->find()->toArray();
        $userCount = User::withoutGlobalScope()->where(['sid' => $params['id']])->count();
        $goodsCount = Goods::withoutGlobalScope()->where(['sid' => $params['id']])->count();

        $shopDetail['shop_data'] = [
            'total_order_amount' => $orderInfo['total_order_amout'] ?: 0,
            'order_num' => $orderInfo['order_num'] ?: 0,
            'user_count' => $userCount,
            'goods_count' => $goodsCount,
        ];

        return $shopDetail;
    }

    /**
     * @notes 商城编辑
     * @param $params
     * @author Tab
     * @date 2021/12/15 10:37
     */
    public static function edit($params)
    {
        Db::startTrans();
        try {
            $shop = PlatformShop::withoutGlobalScope()->findOrEmpty($params['id']);
            if ($shop->isEmpty()) {
                throw new \Exception('商城不存在');
            }
            $edit_set_meal = false;
            if (strtotime($shop->expires_time) != strtotime($params['expires_time'])) {
                $edit_set_meal = true;
            }

            $shop->name = $params['name'];
            $shop->contact = $params['contact'] ?? '';
            $shop->contact_mobile = $params['contact_mobile'] ?? '';
            $shop->domain_alias = $params['domain_alias'] ?? '';
            $shop->status = $params['status'];
            $shop->set_meal_id = $params['set_meal_id'];
            $shop->expires_time = strtotime($params['expires_time']);
            $shop->remark = $params['remark'];
            $shop->duokaiSave([], null, false, false);

            //创建商户套餐使用记录
            if ($edit_set_meal) {
                $set_meal_name = SetMeal::where('id', $params['set_meal_id'])->value('name');
                $recentlyLog = SetMealLog::where('sid', $shop->id)->order('id', 'desc')->findOrEmpty()->toArray();
                SetMealLog::create([
                    'sid' => $shop->id,
                    'type' => SetMealLogEnum::TYPE_PLATFORM,
                    'operator_id' => $params['platform_id'],
                    'set_meal_id' => $params['set_meal_id'],
                    'origin_set_meal_id' => $recentlyLog['set_meal_id'],
                    'set_meal_name' => $set_meal_name,
                    'origin_set_meal_name' => $recentlyLog['set_meal_name'],
                    'content' => SetMealLogEnum::getRecordDesc(SetMealLogEnum::PLATFORM_ADJUST),
                    'channel' => SetMealLogEnum::PLATFORM_ADJUST,
                    'expires_time' => strtotime($params['expires_time']),
                    'origin_expires_time' => $recentlyLog['expires_time'],
                ]);
            }
            $configModel = ConfigModel::withoutGlobalScope()->where(['type'=>'shop','name'=>'name','sid'=>$params['id']])->findOrEmpty();
            $configModel->type  = 'shop';
            $configModel->name  = 'name';
            $configModel->value = $params['name'];
            $configModel->sid   = $shop->id;
            $configModel->duokaiSave([], null, false, false);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 修改超级管理员
     * @author Tab
     * @date 2021/12/15 10:56
     */
    public static function changeSuperAdmin($params)
    {
        try {
            $superAdmin = Admin::withoutGlobalScope()->where([
                'sid' => $params['id'],
                'root' => YesNoEnum::YES,
            ])->findOrEmpty();
            if ($superAdmin->isEmpty()) {
                throw new \Exception('超级管理员不存在');
            }
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['super_password'], $passwordSalt);
            $superAdmin->account = $params['super_admin'];
            $superAdmin->password = $password;
            $superAdmin->duokaiSave([], null, false, false);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除商城
     * @return bool
     * @author Tab
     * @date 2021/12/15 11:24
     */
    public static function delete($params)
    {
        Db::startTrans();
        try {
            // 删除商城
            $shop = PlatformShop::withoutGlobalScope()->findOrEmpty($params['id']);
            if ($shop->isEmpty()) {
                throw new \Exception('商城不存在');
            }
            $shop->duokaiDelete(false);

            // 删除该商城下所有管理后台账号
            $ids = Admin::withoutGlobalScope()->where(['sid' => $params['id'], 'root' => YesNoEnum::YES])->column('id');
            if (!empty($ids)) {
                Admin::duokaiDestroy($ids, false, false);
            }

            // 删除整个商城数据 todo

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 切换商城状态
     * @param $params
     * @author Tab
     * @date 2021/12/20 11:38
     */
    public static function switchStatus($params)
    {
        try {
            $shop = PlatformShop::findOrEmpty($params['id']);
            if ($shop->isEmpty()) {
                throw new \Exception('商城不存在');
            }
            $shop->status = !$shop->status;
            $shop->save();

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 套餐记录
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/7 10:37 上午
     */
    public static function setMealLogLists($params)
    {
        $where = [
            ['sid', '=', $params['id']]
        ];
        if (isset($params['content']) && $params['content'] != '') {
            $where[] = ['content', '=', $params['content']];
        }
        if (isset($params['start_time']) && $params['start_time'] != '') {
            $where[] = ['create_time', '>=', strtotime($params['start_time'])];
        }
        if (isset($params['end_time']) && $params['end_time'] != '') {
            $where[] = ['create_time', '<=', strtotime($params['end_time'])];
        }
        $lists = SetMealLog::where($where)
            ->field('id,type,origin_set_meal_name,set_meal_name,origin_expires_time,expires_time,content,operator_id,create_time,set_meal_order_id')
            ->append(['time_desc', 'operator', 'origin_expires_time_desc', 'expires_time_desc'])
            ->order('id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }


    /**
     * @notes 修改备注
     * @param $params
     * @author cjhao
     * @date 2022/3/9 16:10
     */
    public static function changeRemark($params)
    {
        PlatformShop::where(['id'=>$params['id']])
            ->update(['remark' => $params['remark']]);
    }
}
