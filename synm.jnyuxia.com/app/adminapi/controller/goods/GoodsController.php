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
namespace app\adminapi\controller\goods;
use app\adminapi\{
    logic\goods\GoodsLogic,
    validate\goods\GoodsValidate,
    lists\goods\GoodsCommonLists,
    controller\BaseAdminController,
    validate\goods\GoodsItemValidate,
};
use app\common\enum\GoodsEnum;

/**
 * 商品控制器
 * Class GoodsController
 * @package app\adminapi\controller\goods
 */
class GoodsController extends BaseAdminController
{
    public function commonLists()
    {
        return $this->dataLists(new GoodsCommonLists());
    }

    /**
     * @notes 商品列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/16 16:51
     */
    public function lists()
    {
        return $this->dataLists();
    }


    /**
     * @notes 添加商品
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/13 15:05
     */
    public function add()
    {

        $params = $this->request->post();
        if (GoodsEnum::SEPC_TYPE_MORE == $params['spec_type']) {
            $params['server_spec_value_list'] = cartesian_product(array_converting(array_column($params['spec_value'],'spec_list')));
        }
        (new GoodsValidate())->post()->goCheck('add');              //商品基础信息验证
        (new GoodsItemValidate())->post()->goCheck('', $params);    //商品规格验证

        $res = (new GoodsLogic)->add($params);
        if (true === $res) {
            return $this->success('添加商品成功',[],1,1);
        }
        return $this->fail($res);
    }

    /**
     * @notes 编辑商品
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/14 17:54
     */
    public function edit()
    {
        $params = $this->request->post();
        if (GoodsEnum::SEPC_TYPE_MORE == $params['spec_type']) {
            $params['server_spec_value_list'] = cartesian_product(array_converting(array_column($params['spec_value'],'spec_list')));
        }
        (new GoodsValidate())->post()->goCheck('edit', $params);    //商品基础信息验证
        (new GoodsItemValidate())->post()->goCheck('', $params);    //商品规格验证

        $res = (new GoodsLogic)->edit($params);
        if (true === $res['status'] && $res['is_distribution_goods']) {
            // 分销商品
            return $this->success('商品信息修改成功,该商品属于分销商品，请重新设置分销信息',[],1,1);
        }

        if (true === $res['status'] && !$res['is_distribution_goods']) {
            // 非分销商品
            return $this->success('修改成功',[],1,1);
        }

        return $this->fail($res['err']);

    }


    /**
     * @notes 获取商品
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/15 14:16
     */
    public function detail(){
        $params = (new GoodsValidate())->goCheck('detail');
        $detail = (new GoodsLogic)->detail($params['id']);
        return $this->success('获取成功',$detail);
    }


    /**
     * @notes 设置商品状态
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/17 17:10
     */
    public function status()
    {
        $params = (new GoodsValidate())->post()->goCheck('status');
        (new GoodsLogic)->status($params);
        return $this->success('操作成功',[],1,1);

    }

    /**
     * @notes 设置商品排序
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/17 17:16
     */
    public function sort(){
        $params = (new GoodsValidate())->post()->goCheck('sort');
        (new GoodsLogic)->sort($params);
        return $this->success('操作成功');
    }

    /**
     * @notes 删除商品
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/17 18:55
     */
    public function del(){
        $params = (new GoodsValidate())->post()->goCheck('del');
        (new GoodsLogic)->del($params['ids']);
        return $this->success('删除成功',[],1,1);
    }


    /**
     * @notes 其他列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/7/22 15:50
     */
    public function otherList(){
        $type = $this->request->get('type','list');
        $list = (new GoodsLogic())->otherList($type);
        return $this->success('',$list);
    }

    /**
     * @notes 修改商品名称
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/7/29 16:12
     */
    public function rename(){
        $params = (new GoodsValidate())->post()->goCheck('rename');
        (new GoodsLogic)->rename($params);
        return $this->success('修改成功',[],1,1);
    }


    /**
     * @notes 移动分类
     * @return \think\response\Json
     * @throws \Exception
     * @author ljj
     * @date 2023/5/6 2:24 下午
     */
    pubLic function changeCategory()
    {
        $params = (new GoodsValidate())->post()->goCheck('changeCategory');
        (new GoodsLogic)->changeCategory($params);
        return $this->success('修改成功',[],1,1);
    }
}
