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
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------

namespace app\shopapi\controller;

use app\common\enum\PcDecorateThemePageEnum;
use app\common\service\JsonService;
use app\shopapi\lists\GoodsCommentLists;
use app\shopapi\logic\PcLogic;
use app\shopapi\validate\PcValidate;

/**
 * PC商城
 */
class PcController extends BaseShopController
{
    public array $notNeedLogin = ['commonData', 'goodsDetail', 'goodsCommentCategory', 'goodsCommentLists','getPage'];
    /**
     * @notes 获取公共数据
     * @author Tab
     * @date 2021/11/29 16:11
     */
    public function commonData()
    {
        $params['user_id'] = $this->userId;
        $params['user_info'] = $this->userInfo;
        $result = PcLogic::commonData($params);
        return JsonService::data($result);
    }

    /**
     * @notes 获取商品详情
     * @author Tab
     * @date 2021/11/30 11:11
     */
    public function goodsDetail()
    {
        $params = (new PcValidate())->goCheck('goodsDetail');
        $params['user_id'] = $this->userId;
        $result = PcLogic::goodsDetail($params);
        return JsonService::data($result);
    }

    /**
     * @notes 获取商品评论
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/30 11:57
     */
    public function goodsCommentCategory()
    {
        $params = (new PcValidate())->goCheck('goodsCommentCategory');
        $result = PcLogic::goodsCommentCategory($params);
        return JsonService::data($result);
    }

    /**
     * @notes 获取商品评论列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/11/30 14:17
     */
    public function goodsCommentLists()
    {
        return JsonService::dataLists(new GoodsCommentLists());
    }

    /**
     * @notes 设置用户信息
     * @author Tab
     * @date 2021/12/2 16:43
     */
    public function setUserInfo()
    {
        $params = (new PcValidate())->post()->goCheck('setUserInfo');
        $params['user_id'] = $this->userId;
        $result = PcLogic::setUserInfo($params);
        if ($result) {
            return JsonService::success('修改成功');
        }
        return JsonService::fail(PcLogic::getError());
    }

    /**
     * @notes 售后申请页信息
     * @author Tab
     * @date 2021/12/3 15:09
     */
    public function afterSaleApplyPage()
    {
        $params = (new PcValidate())->goCheck('afterSaleApplyPage');
        $result = PcLogic::afterSaleApplyPage($params);
        return JsonService::data($result);
    }



    /**
     * @notes 获取页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/12/6 18:48
     */
    public function getPage()
    {
        $type = $this->request->get('type',PcDecorateThemePageEnum::TYPE_HOME);
        $result = (new PcLogic)->getPage($type);
        return JsonService::data($result);

    }
}