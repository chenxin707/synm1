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

namespace app\adminapi\controller\goods;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\goods\GoodsGatherLists;
use app\adminapi\lists\goods\GoodsGatherLogLists;
use app\adminapi\logic\goods\GoodsGatherLogic;
use app\adminapi\validate\goods\GoodsGatherValidate;
use app\adminapi\validate\goods\GoodsItemValidate;

class GoodsGatherController extends BaseAdminController
{
    /**
     * @notes 商品采集记录列表
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/13 3:23 下午
     */
    public function logLists()
    {
        return $this->dataLists(new GoodsGatherLogLists());
    }

    /**
     * @notes 采集
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/13 6:36 下午
     */
    public function gather()
    {
        $params = (new GoodsGatherValidate())->post()->goCheck('gather');
        $result = (new GoodsGatherLogic())->gather($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功');
    }

    /**
     * @notes 删除采集记录
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/14 9:41 上午
     */
    public function del()
    {
        $params = (new GoodsGatherValidate())->post()->goCheck('del');
        (new GoodsGatherLogic())->del($params);
        return $this->success('操作成功');
    }

    /**
     * @notes 商品采集列表
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/14 3:06 下午
     */
    public function lists()
    {
        return $this->dataLists(new GoodsGatherLists());
    }

    /**
     * @notes 采集商品详情
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/16 10:56 上午
     */
    public function gatherGoodsDetail()
    {
        $params = (new GoodsGatherValidate())->get()->goCheck('gatherGoodsDetail');
        $result = (new GoodsGatherLogic())->gatherGoodsDetail($params);
        return $this->success('',$result);
    }

    /**
     * @notes 编辑采集商品
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/16 11:38 上午
     */
    public function gatherGoodsEdit()
    {
        $params = (new GoodsGatherValidate())->post()->goCheck('gatherGoodsEdit');
        (new GoodsGatherLogic())->gatherGoodsEdit($params);
        return $this->success('操作成功');
    }

    /**
     * @notes 创建商品
     * @return \think\response\Json
     * @author ljj
     * @date 2023/3/16 12:09 下午
     */
    public function createGoods()
    {
        $params = (new GoodsGatherValidate())->post()->goCheck('createGoods');
        $params['adminInfo'] = $this->adminInfo;
        (new GoodsItemValidate())->post()->goCheck('',$params);    //商品规格验证
        $result = (new GoodsGatherLogic())->createGoods($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功');
    }
}