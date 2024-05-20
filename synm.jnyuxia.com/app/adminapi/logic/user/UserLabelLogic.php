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
namespace app\adminapi\logic\user;
use app\common\{
    model\UserLabel,
    model\UserLabelIndex
};

/**
 * 标签逻辑层
 * Class UserLabelLogic
 * @package app\adminapi\logic\user
 */
class UserLabelLogic
{

    /**
     * @notes 新增用户标签
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/7/28 17:43
     */
    public function add(array $params)
    {
        $userLable = new UserLabel();
        $userLable->name            = $params['name'];
        $userLable->remark          = $params['remark'];
        $userLable->label_type      = $params['label_type'];
        $userLable->duokaiSave();
        return true;

    }

    /**
     * @notes 标签详情
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/7/28 17:58
     */
    public function detail(int $id)
    {

        return UserLabel::field('id,name,remark,label_type')->find($id)->toArray();

    }

    /**
     * @notes 编辑用户标签
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/7/28 17:47
     */
    public function edit(array $params)
    {

        $updateData = [
            'id'            => $params['id'],
            'name'          => $params['name'],
            'remark'        => $params['remark'],
            'label_type'    => $params['label_type'],
        ];
        UserLabel::duokaiUpdate($updateData, [], [], '', false);
        return true;
    }

    /**
     * @notes 删除标签
     * @param int $ids
     * @return bool
     * @author cjhao
     * @date 2021/7/28 18:36
     */
    public function del(array $ids)
    {
        UserLabel::duokaiDestroy($ids);
        UserLabelIndex::where(['label_id'=>$ids])->useSoftDelete('delete_time', time())->delete();
        return UserLabel::duokaiDestroy($ids);
    }
}