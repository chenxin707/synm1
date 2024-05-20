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

use app\shopapi\logic\GoodsLogic;

/**
 * 商品接口控制器
 * Class GoodsController
 * @package app\shopapi\controller
 */
class GoodsController extends BaseShopController
{

    public array $notNeedLogin = ['lists','detail','goodsMarketing','searchRecord'];
    /**
     * @notes 首页商品列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/26 16:48
     */
    public function lists()
    {
        return $this->dataLists();
    }

    /**
     * @notes 搜索记录
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/11 17:43
     */
    public function searchRecord()
    {
        $limit = $this->request->get('limit ',10);
        $lists = [];
        if($this->userId){
            $lists = (new GoodsLogic())->searchRecord($this->userId,$limit);
        }
        return $this->success('', $lists);
    }

    /**
     * @note 商品详情
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/26 17:15
     */
    public function detail()
    {
        $params =  $this->request->get();
        $params['user_id'] = $this->userId;
        $detail = (new GoodsLogic)->detail($params);
        if (false === $detail) {
            return $this->fail(GoodsLogic::getError());
        }
        return $this->success('获取成功', $detail);
    }

    /**
     * @notes 商品营销接口
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/8/27 17:08
     */
    public function goodsMarketing()
    {
        $goodsId = $this->request->get('id');
        $marketing = (new GoodsLogic)->goodsMarketing($goodsId,$this->userId);
        return $this->success('获取成功',$marketing);
    }

    /**
     * @notes 清空搜索记录
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/15 11:35
     */
    public function clearRecord()
    {
        if($this->userId){
            (new GoodsLogic)->clearRecord($this->userId);
        }
        return $this->success('');
    }

}