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

namespace app\adminapi\controller\integral;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\integral\IntegralGoodsLists;
use app\adminapi\logic\integral\IntegralGoodsLogic;
use app\adminapi\validate\integral\IntegralGoodsValidate;


/**
 * 积分商品
 * Class IntegralGoodsController
 * @package app\adminapi\controller\integral
 */
class IntegralGoodsController extends BaseAdminController
{

    /**
     * @notes 获取积分商品列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/30 11:28
     */
    public function lists()
    {
        return $this->dataLists(new IntegralGoodsLists());
    }


    /**
     * @notes 添加积分商品
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/30 14:11
     */
    public function add()
    {
        $params = (new IntegralGoodsValidate())->post()->goCheck('add');
        (new IntegralGoodsLogic())->add($params);
        return $this->success('添加成功', [], 1, 1);
    }


    /**
     * @notes 编辑积分商品
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/30 14:11
     */
    public function edit()
    {
        $params = (new IntegralGoodsValidate())->post()->goCheck('edit');
        IntegralGoodsLogic::edit($params);
        return $this->success('编辑成功', [], 1, 1);
    }


    /**
     * @notes 删除积分商品
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/30 14:12
     */
    public function del()
    {
        $params = (new IntegralGoodsValidate())->post()->goCheck('del');
        IntegralGoodsLogic::del($params['id']);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 积分商品详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/30 14:15
     */
    public function detail()
    {
        $params = (new IntegralGoodsValidate())->goCheck('detail');
        $result = IntegralGoodsLogic::detail($params['id']);
        return $this->success('获取成功', $result);
    }


    /**
     * @notes 切换积分商品状态
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/30 14:15
     */
    public function status()
    {
        $params = (new IntegralGoodsValidate())->post()->goCheck('status');
        IntegralGoodsLogic::setStatus($params);
        return $this->success('设置成功', [], 1, 1);
    }

}