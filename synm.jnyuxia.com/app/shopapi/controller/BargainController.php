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

namespace app\shopapi\controller;

use app\common\enum\DeliveryEnum;
use app\shopapi\lists\BargainRecordLists;
use app\shopapi\logic\BargainLogic;
use app\shopapi\validate\BargainValidate;

/**
 * 砍价控制器
 * Class BargainController
 * @package app\shopapi\controller
 */
class BargainController extends BaseShopController
{
    public array $notNeedLogin = ['lists', 'detail'];


    /**
     * @notes 查看砍价活动列表
     * @author Tab
     * @date 2021/8/28 11:53
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 砍价商品详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/28 15:42
     */
    public function detail()
    {
        $params = (new BargainValidate())->goCheck('detail');
        $result = BargainLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 发起砍价
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/28 16:34
     */
    public function initiate()
    {
        $params = (new BargainValidate())->post()->goCheck('initiate');
        $params['user_id'] = $this->userId;
        $result = BargainLogic::initiate($params);
        if ($result) {
            return $this->success('发起砍价成功', $result);
        }
        return $this->fail(BargainLogic::getError());
    }

    /**
     * @notes 帮助砍价
     * @return \think\response\Json
     * @throws \Exception
     * @author Tab
     * @date 2021/8/30 11:11
     */
    public function help()
    {
        $params = (new BargainValidate())->post()->goCheck('help');
        $params['user_id'] = $this->userId;
        $result = BargainLogic::help($params);
        if ($result) {
            return $this->success('帮助砍价成功', $result);
        }
        return $this->fail(BargainLogic::getError());
    }

    /**
     * @notes 砍价记录列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/30 14:36
     */
    public function record()
    {
        return $this->dataLists(new BargainRecordLists());
    }

    /**
     * @notes 查看砍价进度
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/18 14:36
     */
    public function bargainProgress()
    {
        $params = (new BargainValidate())->goCheck('bargainProgress');
        $result = BargainLogic::bargainProgress($params);
        return $this->data($result);
    }

    /**
     * @notes 分享帮砍详情
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/18 17:16
     */
    public function shareDetail()
    {
        $params = (new BargainValidate())->goCheck('shareDetail');
        $params['user_id'] = $this->userId;
        $result = BargainLogic::shareDetail($params);
        return $this->data($result);
    }
}