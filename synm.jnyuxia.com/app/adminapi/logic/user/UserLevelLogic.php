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
use app\common\enum\PayEnum;
use app\common\model\
{
    User,
    Order,
    UserLevel
};


/**
 * 会员等级逻辑层
 * Class UserLevelLogic
 * @package app\adminapi\logic\user
 */
class UserLevelLogic
{

    /**
     * @notes 添加会员等级
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/7/28 15:08
     */
    public function add(array $params)
    {

        $userLevel = new UserLevel();
        //等级条件
        $condition = $this->disposeCondition($params['condition']);

        $userLevel->name             = $params['name'];
        $userLevel->rank             = $params['rank'];
        $userLevel->image            = $params['image'];
        $userLevel->background_image = $params['background_image'];
        $userLevel->remark           = $params['remark'];
        $userLevel->discount         = $params['level_discount'] ? $params['discount'] : '';
        $userLevel->condition        = json_encode($condition,JSON_UNESCAPED_UNICODE);
        $userLevel->duokaiSave();
        return true;
    }


    /**
     * @notes 获取用户等级
     * @param $id
     * @return array
     * @author cjhao
     * @date 2021/7/29 17:14
     */
    public function detail($id){
        $userLevel = UserLevel::find($id);

        $detail = [
            'id'                => $userLevel->id,
            'name'              => $userLevel->name,
            'rank'              => $userLevel->rank,
            'image'             => $userLevel->image,
            'background_image'  => $userLevel->background_image,
            'remark'            => $userLevel->remark,
            'level_discount'    => $userLevel->discount > 0 ? 1 :0,
            'discount'          => $userLevel->discount,
            'condition'         => \app\common\logic\UserLogic::formatLevelCondition($userLevel->condition),
        ];

        return $detail;
    }

    /**
     * @notes 编辑会员等级
     * @param array $params
     * @author cjhao
     * @date 2021/7/28 15:15
     */
    public function edit(array $params){
        $userlevel = UserLevel::find($params['id']);

        $userlevel->name             = $params['name'];
        $userlevel->image            = $params['image'];
        $userlevel->background_image = $params['background_image'];
        $userlevel->remark           = $params['remark'];
        $userlevel->discount         = $params['level_discount'] ? $params['discount'] : '';

        //非系统默认，可设置等级条件
        if(1 != $userlevel->rank){
            $userlevel->rank            = $params['rank'];
            //等级条件
            $condition =$this->disposeCondition($params['condition']);
            $userlevel->condition    = json_encode($condition,JSON_UNESCAPED_UNICODE);
        }

        $userlevel->save();
        return true;
    }

    /**
     * @notes 删除会员等级
     * @param int $id
     * @return bool
     * @author cjhao
     * @date 2021/7/28 16:59
     */
    public function del(int $id){
        $res = UserLevel::duokaiDestroy($id);
        //todo 将该等级的用户全部降到系统默认等级
        if($res){

            $level = UserLevel::where(['rank'=>1])->find();
            if($level){
                User::where(['level'=>$id])->update(['level'=>$level->id]);
            }

        }
        return true;
    }

    /**
     * @notes 处理前端传过来的等级数据
     * @param $condition
     * @author cjhao
     * @date 2022/4/28 17:05
     */
    public function disposeCondition($condition){

        //默认满足任意条件
        $condition_type = $condition['condition_type'] ?? 0;
        //默认不勾选
        $isSingleMoney = $condition['is_single_money'];
        //单笔消费金额
        $singleMoney =$condition['single_money'] ?? '';
        //默认不勾选
        $isTotalMoney = $condition['is_total_money'];
        //累计消费金额
        $totalMoney = $condition['total_money'] ?? '';
        //默认不勾选
        $isTotalNum = $condition['is_total_num'];
        //累计消费次数
        $totalNum = $condition['total_num'] ?? '';

        return [
            'condition_type'        => (int)$condition_type,   //默认满足任意条件
            'is_single_money'       => (int)$isSingleMoney,    //默认不勾选
            'single_money'          => $singleMoney ? round($singleMoney,2) : '',
            'is_total_money'        => (int)$isTotalMoney,
            'total_money'           => $totalMoney ? round($totalMoney,2) : '',
            'is_total_num'          => (int)$isTotalNum,
            'total_num'             => $totalNum ?  (int)$totalNum : '',
        ];


    }
    

}