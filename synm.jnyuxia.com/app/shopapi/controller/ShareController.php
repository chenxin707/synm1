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
use app\shopapi\{
    logic\ShareLogic,
    validate\ShareValidate
};

/**
 * 分享控制器
 * Class ShareController
 * @package app\shopapi\controller
 */
class ShareController extends BaseShopController
{

    /**
     * @notes 小程序码
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/9/11 18:14
     */
    public function getMnpQrCode()
    {

        $params = (new ShareValidate())->goCheck('goods');
        $res = (new ShareLogic())->getMnpQrCode($this->userId,$params);
        if(true === $res){
            return $this->success('获取成功',['qr_code'=>ShareLogic::getReturnData()]);
        }
        return $this->fail(ShareLogic::getReturnData());

    }

    /**
     * @notes 分享砍价小程序码
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/28 17:27
     */
    public function getMnpQrCodeInitiate()
    {

        $params = (new ShareValidate())->goCheck('initiate');
        $res = (new ShareLogic())->getMnpQrCodeInitiate($this->userId,$params);
        if(true === $res){
            return $this->success('获取成功',['qr_code'=>ShareLogic::getReturnData()]);
        }
        return $this->fail(ShareLogic::getReturnData());

    }


    /**
     * @notes 获取今日头条小程序码
     * @return \think\response\Json
     * @throws \Exception
     * @author cjhao
     * @date 2021/11/25 9:59
     */
    public function getTouTiaoQrcode(){
        $params = (new ShareValidate())->goCheck('goods');
        $res = (new ShareLogic())->getTouTiaoQrcode($this->userId,$params);
        if(true === $res){
            return $this->success('获取成功',['qr_code'=>ShareLogic::getReturnData()]);
        }
        return $this->fail(ShareLogic::getReturnData());
    }
}