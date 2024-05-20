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
namespace app\adminapi\logic\user;
use app\adminapi\logic\distribution\DistributionLogic;
use app\common\{logic\BaseLogic,
    model\User,
    model\Order,
    enum\PayEnum,
    model\UserLevel,
    model\UserLabel,
    enum\CouponEnum,
    model\CouponList,
    enum\AccountLogEnum,
    model\UserLabelIndex,
    logic\AccountLogLogic,
    enum\UserTerminalEnum};
use think\facade\Db;
use think\Model;


/**
 * 用户逻辑层
 * Class UserLogic
 * @package app\adminapi\logic\user
 */
class UserLogic extends BaseLogic
{

    /**
     * @notes 用户概况页面
     * @return array
     * @author cjhao
     * @date 2021/8/17 14:58
     */
    public function index():array
    {
        $today = strtotime(date('Y-m-d'));
        //用户数
        $userCount = User::count();
        //今日新增用户数
        $userNewCount = User::where('create_time','>=',$today)->count();
        //成交用户数
        $repetitionCount = Order::where(['pay_status'=>PayEnum::ISPAID])->count();
        //复购用户数
        $purchaseCount = Order::where(['pay_status'=>PayEnum::ISPAID])
                        ->group('user_id')
                        ->having('count(user_id) >= 2')
                        ->count();

        $dayList = day_time(14,true);
        $dayList = array_reverse($dayList);
        $echarts_data = [];
        //图表数据
        foreach ($dayList as $dayKey => $dayVal){
            $newUserCount = User::whereTime('create_time','between',[$dayVal,$dayVal+86399])->count();
            $echarts_data[] = [
                'day'               => date('m-d',$dayVal),
                'user_new_count'    => $newUserCount,
            ];
        }

        $data = [
            'user_count'        => $userCount,
            'user_new_count'    => $userNewCount,
            'repetition_count'  => $repetitionCount,
            'purchase_count'    => $purchaseCount,
            'echarts_data'      => $echarts_data,
        ];
        return $data;
    }


    /**
     * @notes  用户搜索条件列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/10 16:58
     */
    public function otherLists(): array
    {

        $userLevel = UserLevel::order('rank', 'asc')->field('id,name')->select();
        $userLabel = UserLabel::field('id,name')->select();
        $sourceList = UserTerminalEnum::getTermInalDesc();
        return [
            'user_level_list'   => $userLevel,
            'user_label_list'   => $userLabel,
            'source_list'       => $sourceList,
        ];
    }


    /**
     * @notes 设置用户标签
     * @param array $param
     * @return bool
     * @throws \Exception
     * @author cjhao
     * @date 2021/8/17 11:43
     */
    public function setLabel(array $param): bool
    {
        $userIds = $param['user_ids'] ?? [];
        $labelIds = $param['label_ids'] ?? [];
        $labelIds = UserLabel::where(['id' => $labelIds])->column('id');
        //当前用户已绑定的标签
        $userLabelIndexList = UserLabelIndex::where(['user_id' => $userIds])
            ->group('user_id')
            ->column('group_concat(label_id  Separator \',\') as user_label_id', 'user_id');

        $addData = [];
        foreach ($userIds as $userId) {
            $userLabelIndex = $userLabelIndexList[$userId]['user_label_id'] ?? '';
            $userLabelIds = explode(',', $userLabelIndex);

            foreach ($labelIds as $labelId) {
                //该用户已有该标签，跳过
                if (in_array($labelId, $userLabelIds)) {
                    continue;
                }

                $addData[] = [
                    'user_id'   => $userId,
                    'label_id'  => $labelId,
                ];
            }
        }
        //写入数据
        if ($addData) {

            (new UserLabelIndex)->duokaiSaveAll($addData);
        }
        return true;

    }


    /**
     * @notes 用户详情
     * @param int $userId
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/18 15:52
     */
    public function detail(int $userId)
    {
        $user = User::with('userLevel')
                ->field('id,sn,nickname,avatar,real_name,sex,mobile,birthday,code,level,create_time,login_time,total_order_amount,total_order_num,user_money,user_earnings,user_money+user_earnings as total_user_money,user_integral,disable,register_source,inviter_id,first_leader,disable')
                ->find($userId);

        $labels = UserLabelIndex::withoutGlobalScope()
            ->aliasSid('ULI')
            ->alias('ULI')
            ->leftJoin('user_label UL', 'UL.id = ULI.label_id')
            ->where('ULI.user_id', $userId)
            ->column('UL.name');

        $labels = empty($labels) ? '' : implode('、',$labels);

        $user = $user->toArray();

        //交易信息
        $latelyTime = Order::where(['user_id'=>$userId])->order('id desc')->value('create_time');
        //分销信息
//        $distribution = DistributionLogic::info($user);

        //基本信息
        $data = [
            'user_info'          => [//用户信息
                'id'                => $user['id'],
                'sn'                => $user['sn'],
                'nickname'          => $user['nickname'],
                'real_name'         => $user['real_name'],
                'avatar'            => $user['avatar'],
                'sex'               => $user['sex'],
                'birthday'          => $user['birthday'],
                'mobile'            => $user['mobile'],
                'code'              => $user['code'],
                'level'             => $user['level'],
                'level_name'        => $user['name'],
                'labels'            => $labels,
                'create_time'       => $user['create_time'],
                'login_time'        => $user['login_time'],
                'register_source'   => UserTerminalEnum::getTermInalDesc($user['register_source']),
                'total_user_money'  => $user['total_user_money'],   //钱包金额
                'user_money'        => $user['user_money'],         //可用金额
                'user_earnings'     => $user['user_earnings'],      //可提现金额
                'user_integral'     => $user['user_integral'],      //积分
                'disable'           => $user['disable'],            //禁用状态
                'coupon_num'        => CouponList::where([
                    ['user_id','=',$userId],
                    ['status','=',CouponEnum::USE_STATUS_NOT],
                    ['invalid_time', '>=', time()]
                ])->count(),
                'inviter' => User::getInviterInfo($user['inviter_id'], $user['id']), // 邀请人信息
                'first_leader_info' => User::getFirstLeader($user['first_leader']), // 上级分销商信息
            ],
            'transaction'       => [//交易信息
                'total_order_amount'=> $user['total_order_amount'],
                'total_order_num'   => $user['total_order_num'],
                'customer_price'    => $user['total_order_num'] ? round($user['total_order_amount'] / $user['total_order_num'],2) : 0,
                'lately_order_time' => $latelyTime ? date('Y-m-d H:i:s',$latelyTime) : '',
            ],

        ];
        return $data;
    }

    /**
     * @notes 更新用户信息
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/8/18 17:21
     */
    public function setUserInfo(array $params):bool
    {
        User::where(['id'=>$params['user_id']])->update([$params['field']=>$params['value']]);
        return true;

    }

    /**
     * @notes 设置用户标签
     * @param array $params
     * @return bool
     * @throws \Exception
     * @author cjhao
     * @date 2021/8/19 11:31
     */
    public function setUserLabel(array $params):bool {
        //先删除用户标签，在重新设置用户标签
        UserLabelIndex::where(['user_id'=>$params['user_id']])->useSoftDelete('delete_time', time())->delete();
        $addData = [];
        foreach ($params['label_ids'] as $labelId){
            $addData[] = [
                'user_id'   => $params['user_id'],
                'label_id'  => $labelId,
            ];
        }
        if($addData){
            (new UserLabelIndex)->duokaiSaveAll($addData);
        }
        return true;

    }

    /**
     * @notes 调整用户余额
     * @param array $params
     * @return string
     * @author cjhao
     * @date 2021/9/10 18:15
     */
    public function adjustUserWallet(array $params)
    {
        Db::startTrans();
        try {
            $user = User::find($params['user_id']);
            switch ($params['type']){
                case 1:
                    //增加
                    if(1 == $params['action']){
                        //调整可用余额
                        $user->user_money = $user->user_money + $params['num'];
                        $user->duokaiSave();
                        //记录日志
                        AccountLogLogic::add($user->id, AccountLogEnum::BNW_INC_ADMIN, AccountLogEnum::INC, $params['num'], '', $params['remark'] ?? '');


                    }else{

                        $user->user_money = $user->user_money - $params['num'];
                        $user->duokaiSave();
                        //记录日志
                        AccountLogLogic::add($user->id, AccountLogEnum::BNW_DEC_ADMIN,AccountLogEnum::DEC, $params['num'], '', $params['remark'] ?? '');
                    }
                    break;
                case 2:
                    //增加
                    if(1 == $params['action']){
                        //调整可用余额
                        $user->user_earnings = $user->user_earnings + $params['num'];
                        $user->duokaiSave();
                        //记录日志
                        AccountLogLogic::add($user->id, AccountLogEnum::BW_INC_ADMIN, AccountLogEnum::INC, $params['num'], '', $params['remark'] ?? '');


                    }else{

                        $user->user_earnings = $user->user_earnings - $params['num'];
                        $user->duokaiSave();
                        //记录日志
                        AccountLogLogic::add($user->id, AccountLogEnum::BW_DEC_ADMIN,AccountLogEnum::DEC, $params['num'], '', $params['remark'] ?? '');
                    }

                    break;
                case 3:
                    //增加
                    if(1 == $params['action']){
                        //调整可用余额
                        $user->user_integral = $user->user_integral + $params['num'];
                        $user->duokaiSave();
                        //记录日志
                        AccountLogLogic::add($user->id, AccountLogEnum::INTEGRAL_INC_ADMIN, AccountLogEnum::INC, $params['num'], '', $params['remark'] ?? '');


                    }else{

                        $user->user_integral = $user->user_integral - $params['num'];
                        $user->duokaiSave();
                        //记录日志
                        AccountLogLogic::add($user->id, AccountLogEnum::INTEGRAL_DEC_ADMIN,AccountLogEnum::DEC, $params['num'], '', $params['remark'] ?? '');
                    }

                    break;
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }

    }

    /**
     * @notes 用户信息
     * @author Tab
     * @date 2021/9/13 19:33
     */
    public static function info($params)
    {
        $info = User::findOrEmpty($params['user_id'])->toArray();
        if (empty($info)) {
            $info = '';
        } else {
            $info = $info['sn'] . '(' . $info['nickname'] . ')';
        }
        $count = User::where('inviter_id', $params['user_id'])->count();
        return [
            'name' => $info,
            'count' => $count
        ];
    }

    /**
     * @notes 上级分销商调整信息
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/14 10:50
     */
    public static function adjustFirstLeaderInfo($params)
    {
        $userField = [
            'id',
            'sn',
            'nickname',
            'first_leader'
        ];
        $user = User::field($userField)->findOrEmpty($params['user_id'])->toArray();
        if(empty($user['first_leader'])) {
            $firstLeader = '系统';
        } else {
            $firstLeaderField = [
                'id',
                'sn',
                'nickname',
            ];
            $firstLeader = User::field($firstLeaderField)->findOrEmpty($user['first_leader'])->toArray();
        }
        return [
            'user' => $user,
            'first_leader' => $firstLeader
        ];
    }

    /**
     * @notes
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/9/14 11:42
     */
    public static function adjustFirstLeader($params)
    {
        Db::startTrans();
        try {
            switch($params['type']) {
                // 指定推荐人
                case 'assign':
                    $formatData = self::assignFirstLeader($params);
                    break;
                // 设置推荐人为系统,即清空上级
                case 'system':
                    $formatData = self::clearFirstLeader($params);
                    break;
            }

            $user = User::findOrEmpty($params['user_id']);
            // 旧关系链
            if (!empty($user->ancestor_relation)) {
                $old_ancestor_relation = $user->id . ',' .$user->ancestor_relation;
            } else {
                $old_ancestor_relation = $user->id;
            }

            // 更新当前用户的分销关系
            User::where(['id' => $params['user_id']])->update($formatData);

            //更新当前用户下级的分销关系
            $data = [
                'second_leader' => $formatData['first_leader'],
                'third_leader' => $formatData['second_leader'],
                'update_time'  => time()
            ];
            User::where(['first_leader' => $params['user_id']])->update($data);

            //更新当前用户下下级的分销关系
            $data = [
                'third_leader' => $formatData['first_leader'],
                'update_time'  => time()
            ];
            User::where(['second_leader' => $params['user_id']])->update($data);

            //更新当前用户所有后代的关系链
            $posterityArr = User::field('id,ancestor_relation')
                ->whereFindInSet('ancestor_relation', $params['user_id'])
                ->select()
                ->toArray();
            $updateData = [];
            $replace_ancestor_relation = $params['user_id'] . ','. $formatData['ancestor_relation'];
            foreach($posterityArr as $item) {
                $replace = substr($item['ancestor_relation'], strpos($item['ancestor_relation'], $params['user_id']));
                $updateData[] = [
                    'id' => $item['id'],
                    'ancestor_relation' => trim(str_replace($old_ancestor_relation, $replace_ancestor_relation, $item['ancestor_relation']), ',')
                ];
            }
            // 批量更新
            (new User())->duokaiSaveAll($updateData, true, false, false);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 指定上级分销商
     * @param $params
     * @return array
     * @throws \think\Exception
     * @author Tab
     * @date 2021/9/14 11:44
     */
    public static function assignFirstLeader($params)
    {
        if (empty($params['first_id'])) {
            throw new \think\Exception('请选择上级分销商');
        }
        if ($params['first_id'] ==  $params['user_id']) {
            throw new \think\Exception('上级分销商不可以选择自己');
        }
        $firstLeader = User::field(['id', 'first_leader', 'second_leader', 'third_leader', 'ancestor_relation'])
            ->where('id', $params['first_id'])
            ->findOrEmpty()
            ->toArray();
        if(empty($firstLeader)) {
            throw new \think\Exception('分销商不存在');
        }
        $ancestorRelation =explode(',', $firstLeader['ancestor_relation']);
        if(!empty($ancestorRelation) && in_array($params['user_id'], $ancestorRelation)) {
            throw new \think\Exception('不允许填写自己任一下级的邀请码');
        }

        // 上级
        $first_leader_id = $firstLeader['id'];
        // 上上级
        $second_leader_id = $firstLeader['first_leader'];
        // 上上上级
        $third_leader_id = $firstLeader['second_leader'];
        // 拼接关系链
        $firstLeader['ancestor_relation'] = $firstLeader['ancestor_relation'] ?: ''; // 清空null值及0
        $my_ancestor_relation = $first_leader_id. ',' . $firstLeader['ancestor_relation'];
        // 去除两端逗号
        $my_ancestor_relation = trim($my_ancestor_relation, ',');
        $data = [
            'first_leader' => $first_leader_id,
            'second_leader' => $second_leader_id,
            'third_leader' => $third_leader_id,
            'ancestor_relation' => $my_ancestor_relation,
        ];
        return $data;
    }

    /**
     * @notes 清空上级
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/9/14 11:46
     */
    public static function clearFirstLeader($params)
    {
        $data = [
            'first_leader' => 0,
            'second_leader' => 0,
            'third_leader' => 0,
            'ancestor_relation' => '',
        ];
        return $data;
    }
}