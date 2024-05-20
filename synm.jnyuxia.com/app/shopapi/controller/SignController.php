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

use app\common\service\ConfigService;
use app\shopapi\logic\SignLogic;

/**
 * 签到控制器
 * Class SignController
 * @package app\shopapi\controller
 */
class SignController extends BaseShopController
{
    /**
     * @notes 查看签到列表
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 14:08
     */
    public function lists()
    {
        $result = SignLogic::lists($this->userId);
        return $this->data($result);
    }

    /**
     * @notes 签到
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 16:49
     */
    public function sign()
    {
        if(!$this->request->isPost()) {
            return $this->fail('请求方式错误');
        }
        $result = SignLogic::sign($this->userId);
        if($result) {
            return $this->success('恭喜您签到成功啦', $result);
        }
        return $this->fail(SignLogic::getError());
    }

    /**
     * @notes 获取签到说明
     * @return \think\response\Json
     * @author Tab
     * @date 2021/8/16 18:21
     */
    public function getRemark()
    {
        $result = ['remark' => ConfigService::get('sign', 'remark')];
        return $this->data($result);
    }
}