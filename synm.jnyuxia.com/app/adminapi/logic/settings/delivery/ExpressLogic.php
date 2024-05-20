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

namespace app\adminapi\logic\settings\delivery;


use app\common\enum\DefaultEnum;
use app\common\logic\BaseLogic;
use app\common\model\Express;
use app\common\service\FileService;

class ExpressLogic extends BaseLogic
{
    /**
     * @notes 添加快递公司
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/29 4:39 下午
     */
    public function add($params)
    {
        $express = new Express;
        $express->name = $params['name'];
        $express->icon = $params['icon'] ? FileService::setFileUrl($params['icon']) : '';
        $express->code = $params['code'] ?? '';
        $express->code100 = $params['code100'] ?? '';
        $express->codebird = $params['codebird'] ?? '';
        $express->sort = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : DefaultEnum::SORT;
        return $express->duokaiSave();
    }

    /**
     * @notes 编辑快递公司
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/29 5:20 下午
     */
    public function edit($params)
    {
        $express = Express::find($params['id']);
        $express->name = $params['name'];
        $express->icon = FileService::setFileUrl($params['icon']);
        $express->code = $params['code'];
        $express->code100 = $params['code100'];
        $express->codebird = $params['codebird'];
        $express->sort = $params['sort'];
        return $express->duokaiSave();
    }

    /**
     * @notes 删除快递公司
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/29 5:30 下午
     */
    public function del($params)
    {
        return Express::duokaiDestroy($params['id']);
    }

    /**
     * @notes 查看快递公司详情
     * @param $params
     * @return Express|array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/29 5:42 下午
     */
    public function detail($params)
    {
        return Express::field('id,name,icon,code,code100,codebird,sort')->find($params['id'])->toArray();
    }
}