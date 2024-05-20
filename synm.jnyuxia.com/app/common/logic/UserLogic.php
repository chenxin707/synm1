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
namespace app\common\logic;
use app\common\{enum\PayEnum, model\Order, model\User, model\UserLevel};

/**
 * 用户逻辑类
 * Class UserLogic
 * @package app\common\logic
 */
class UserLogic  extends BaseLogic
{
    /**
     * @notes 注册奖励
     * @param int $userId
     * @author cjhao
     * @date 2021/9/15 15:25
     */
    public static function registerAward(int $userId)
    {
        // 创建分销基础表
        DistributionLogic::add($userId);
        //默认等级
        self::defaultUserLevel($userId);


    }

    /**
     * @notes 注册后调整默认等级
     * @param $userId
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/9/15 15:33
     */
    public static function defaultUserLevel($userId)
    {
        $level = UserLevel::where(['rank'=>1])->find();
        if($level){
            User::where(['id'=>$userId])->update(['level'=>$level->id]);
        }

    }

    /**
     * @notes 格式化会员等级（兼容旧数据）
     * @param $condition
     * @return mixed
     * @author cjhao
     * @date 2022/4/6 18:05
     */
    public static function formatLevelCondition($condition)
    {
        if(empty($condition)){
            return [];
        }
        $condition['condition_type'] = (int)$condition['condition_type'];
        if(!isset($condition['is_single_money'])){
            $condition['is_single_money'] = $condition['single_money'] > 0 ? 1 :0;
        }
        if(!isset($condition['is_total_money'])){
            $condition['is_total_money'] = $condition['total_money'] > 0 ? 1 :0;
        }
        if(!isset($condition['is_total_num'])){
            $condition['is_total_num'] = $condition['total_num'] > 0 ? 1 :0;
        }
        return $condition;

    }


    /**
     * @notes 更新会员等级 todo 该方法调在更新用户的累计金额和累计订单数后调用
     * @param int $userId
     * @return bool
     * @author cjhao
     * @date 2021/7/29 18:06
     */
    public static function updateLevel(int $userId)
    {

        $user = User::with('user_level')->find($userId);
        $levelList = UserLevel::where('rank', '>', $user->rank)
            ->order('rank desc')
            ->select();
        //没有比会员当前高的等级，直接断掉
        if (empty($levelList)) {
            return true;
        }
        $orderAmount = Order::where(['user_id' => $userId, 'pay_status' => PayEnum::ISPAID])
            ->order('order_amount desc')
            ->value('order_amount');

        //从最高等级开始遍历
        foreach ($levelList as $level) {
            $condition = self::formatLevelCondition($level['condition']);


            $conditionType = $condition['condition_type'];

            $singleMoney = $condition['single_money'];
            $isSingleMoney = $condition['is_single_money'];
            $totalMoney = $condition['total_money'];
            $isTotalMoney = $condition['is_total_money'];
            $totalNum = $condition['total_num'];
            $isTotalNum = $condition['is_total_num'];
            //数据异常不处理
            if( 0 == $isSingleMoney && 0 == $isTotalMoney  && 0 == $isTotalNum){
                continue;
            }

            //满足其中任意条件
            if(0 == $conditionType){

                $singleMoneyBoole = false;      //满足单笔消费条件
                $totalMoneyBoole = false;       //满足累计消费金额条件
                $totalNumBoole = false;         //累计消费次数条件

                //是否满足单笔消费条件
                if (1 == $isSingleMoney && $singleMoney > 0 && $orderAmount >= $singleMoney) {
                    $singleMoneyBoole = true;
                }
                //是否满足累计消费金额
                if (1 == $isTotalMoney && $totalMoney > 0 && $user->total_order_amount >= $totalMoney) {
                    $totalMoneyBoole = true;
                }
                //是否满足消费次数
                if (1 == $isTotalNum && $totalNum > 0 && $user->total_order_num >= $totalNum) {
                    $totalNumBoole = true;
                }

                //满足其中任意条件
                if ($singleMoneyBoole || $totalMoneyBoole || $totalNumBoole) {
                    $user->level = $level->id;
                    $user->save();
                    break;
                }

            }else{
                //
                $singleMoneyBoole = true;      //满足单笔消费条件
                $totalMoneyBoole = true;       //满足累计消费金额条件
                $totalNumBoole = true;         //累计消费次数条件

                //判断不满单笔消费条件
                if (1 == $isSingleMoney && $orderAmount < $singleMoney) {
                    $singleMoneyBoole = false;
                }
                //判断不满累计消费金额条件
                if (1 == $isTotalMoney && $user->total_order_amount < $totalMoney) {
                    $totalMoneyBoole = false;
                }
                //判断不满足消费次数条件
                if (1 == $isTotalNum &&  $user->total_order_num < $totalNum) {
                    $totalNumBoole = false;
                }

                //满足勾选的全部条件
                if ($singleMoneyBoole && $totalMoneyBoole && $totalNumBoole) {
                    $user->level = $level->id;
                    $user->save();
                    break;
                }


            }


        }
        return true;
    }


}