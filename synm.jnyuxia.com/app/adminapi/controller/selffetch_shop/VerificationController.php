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

namespace app\adminapi\controller\selffetch_shop;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\selffetch_shop\VerificationLists;
use app\adminapi\logic\selffetch_shop\VerificationLogic;
use app\adminapi\validate\selffetch_shop\VerificationValidate;

class VerificationController extends BaseAdminController
{
    /**
     * @notes 查看上门自提订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/12 11:37 上午
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
     * @date 2021/8/12 11:57 上午
     */
    public function verification()
    {
        $params = (new VerificationValidate())->post()->goCheck('verification', ['admin_info'=>$this->adminInfo]);
        $result = (new VerificationLogic())->verification($params);
        if (true !== $result) {
            if (is_array($result)) {
                return $this->success($result['msg'],[],10,1);
            }
            return $this->fail(VerificationLogic::getError());
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 核销查询
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/27 10:27 上午
     */
    public function verificationQuery()
    {
        $params = (new VerificationValidate())->goCheck('verificationQuery');
        $result = (new VerificationLogic())->verificationQuery($params);
        return $this->success('',$result);
    }

    /**
     * @notes 查看核销详情
     * @return \think\response\Json
     * @author ljj
     * @date 2021/8/27 10:48 上午
     */
    public function verificationDetail()
    {
        $params = (new VerificationValidate())->goCheck('verificationDetail');
        $result = (new VerificationLogic())->verificationDetail($params);
        return $this->success('',$result);
    }
}