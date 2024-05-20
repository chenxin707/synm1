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
namespace app\adminapi\controller\live;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\logic\live\LiveGoodsLogic;
use app\adminapi\validate\live\LiveGoodsValidate;

/**
 * 直播间商品
 * Class LiveRoomGoodsController
 * @package app\adminapi\controller\live
 */
class LiveGoodsController extends BaseAdminController
{

    /**
     * @notes 直播商品列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/23 15:26
     */
    public function lists()
    {
//        return $this->dataLists();
        $limitOffset = $this->request->get('page_no');
        $limitLength = $this->request->get('page_size');
        $type = $this->request->get('type');
        $result = (new LiveGoodsLogic())->lists($limitOffset,$limitLength,$type);
        if(is_array($result)){
            return $this->success('',$result);
        }
        return $this->fail($result);
    }

    /**
     * @notes 添加直播商品
     * @return \think\response\Json
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author cjhao
     * @date 2021/11/23 16:26
     */
    public function add()
    {
        $post = (new LiveGoodsValidate())->post()->goCheck('add');
        $result = (new LiveGoodsLogic())->add($post);
        if (true === $result) {
            return $this->success('添加成功',[],1,1);
        }
        return $this->fail($result);

    }

    /**
     * @notes 删除直播商品
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/23 18:10
     */
    public function del()
    {
        $post = (new LiveGoodsValidate())->post()->goCheck('del');
        $result = (new LiveGoodsLogic())->del($post['goods_id']);
        if (true === $result) {
            return $this->success('删除成功',[],1,1);
        }
        return $this->fail($result);
    }

}