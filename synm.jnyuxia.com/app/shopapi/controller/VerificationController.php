<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\shopapi\controller;


use app\shopapi\lists\VerificationLists;
use app\shopapi\logic\VerificationLogic;
use app\shopapi\validate\VerificationValidate;

class VerificationController extends BaseShopController
{
    /**
     * @notes 查看自提订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/27 4:16 下午
     */
    public function lists()
    {
        return $this->dataLists(new VerificationLists());
    }

    /**
     * @notes 提货核销
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/27 5:38 下午
     */
    public function verification()
    {
        $params = (new VerificationValidate())->goCheck('verification',['user_id'=>$this->userId]);
        $result = (new VerificationLogic())->verification($params);
        if (isset($result['code']) && $result['code'] == 10) {
            return $this->success($result['msg'],[],$result['code']);
        }
        return $this->success('',$result);
    }

    /**
     * @notes 确认提货
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/27 6:05 下午
     */
    public function verificationConfirm()
    {
        $params = (new VerificationValidate())->post()->goCheck('verificationConfirm',['user_id'=>$this->userId]);
        $result = (new VerificationLogic())->verificationConfirm($params);
        if (false === $result) {
            return $this->fail(VerificationLogic::getError());
        }
        return $this->success('提货成功',[],1,1);
    }
}