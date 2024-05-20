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

namespace app\adminapi\logic\shop_notice;


use app\common\enum\DefaultEnum;
use app\common\logic\BaseLogic;
use app\common\model\ShopNotice;

class ShopNoticeLogic extends BaseLogic
{
    /**
     * @notes 添加商城公告
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/23 2:00 下午
     */
    public function add($params)
    {
        $shop_notice = new ShopNotice;
        $shop_notice->name = $params['name'];
        $shop_notice->synopsis = $params['synopsis'] ?? '';
        $shop_notice->image = $params['image'] ?? '';
        $shop_notice->content = $params['content'];
        $shop_notice->sort = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : DefaultEnum::SORT;
        $shop_notice->status = $params['status'];
        $shop_notice->publish_time = time();
        return $shop_notice->duokaiSave();
    }

    /**
     * @notes 编辑商城公告
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 2:41 下午
     */
    public function edit($params)
    {
        $shop_notice = ShopNotice::find($params['id']);
        $shop_notice->name = $params['name'];
        $shop_notice->synopsis = $params['synopsis'];
        $shop_notice->image = $params['image'];
        $shop_notice->content = $params['content'];
        $shop_notice->sort = $params['sort'];
        $shop_notice->status = $params['status'];
        return $shop_notice->duokaiSave();
    }

    /**
     * @notes 查看商城公告详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 2:49 下午
     */
    public function detail($params)
    {
        $result = ShopNotice::field('id,name,synopsis,image,content,sort,status')->where('id',$params['id'])->find()->toArray();
        return $result;
    }

    /**
     * @notes 修改商城公告状态
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 2:58 下午
     */
    public function status($params)
    {
        $shop_notice = ShopNotice::find($params['id']);
        $shop_notice->status = $params['status'];
        return $shop_notice->duokaiSave();
    }

    /**
     * @notes 删除商城公告
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/23 3:04 下午
     */
    public function del($params)
    {
        return ShopNotice::duokaiDestroy($params['id']);
    }
}