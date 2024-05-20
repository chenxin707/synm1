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
use app\adminapi\logic\live\LiveRoomLogic;
use app\adminapi\validate\live\LiveRoomValidate;


/**
 * 直播间控制器
 * Class LiveRoomController
 * @package app\adminapi\controller\live
 */
class LiveRoomController extends BaseAdminController
{

    /**
     * @notes 直播间列表
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/22 16:13
     */
    public function lists()
    {
        $limitOffset = $this->request->get('page_no');
        $limitLength = $this->request->get('page_size');
        $result = (new LiveRoomLogic())->lists($limitOffset,$limitLength);
        if(is_array($result)){
            return $this->success('',$result);
        }
        return $this->fail($result);
    }



    /**
     * @notes 创建直播间
     * @author cjhao
     * @date 2021/11/22 17:51
     */
    public function add()
    {
        $post = (new LiveRoomValidate())->post()->goCheck('add');
        $result = (new LiveRoomLogic())->add($post);
        if (true === $result) {
            return $this->success('创建成功',[],1,1);
        }
        return $this->fail($result);

    }


    /**
     * @notes 删除直播间
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/23 10:24
     */
    public function del()
    {
        $post = (new LiveRoomValidate())->post()->goCheck('del');
        $result = (new LiveRoomLogic())->del($post['room_id']);
        if (true === $result) {
            return $this->success('删除成功',[],1,1);
        }
        return $this->fail($result);

    }

}