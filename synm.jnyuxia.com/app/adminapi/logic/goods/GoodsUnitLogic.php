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

namespace app\adminapi\logic\goods;


use app\common\enum\DefaultEnum;
use app\common\model\GoodsUnit;

class GoodsUnitLogic
{
    /**
     * @notes 添加商品单位
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/19 6:03 下午
     */
    public function add($params)
    {
        $goods_unit = new GoodsUnit;
        $goods_unit->name = $params['name'];
        $goods_unit->sort = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : DefaultEnum::SORT;
        return $goods_unit->duokaiSave();
    }

    /**
     * @notes 删除商品单位
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/19 6:48 下午
     */
    public function del($params)
    {
        return GoodsUnit::duokaiDestroy($params['id']);
    }

    /**
     * @notes 编辑商品单位
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 6:54 下午
     */
    public function edit($params)
    {
        $goods_unit = GoodsUnit::find($params['id']);
        $goods_unit->name = $params['name'];
        $goods_unit->sort = $params['sort'];
        return $goods_unit->duokaiSave();
    }

    /**
     * @notes 查看商品单位详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 7:02 下午
     */
    public function detail($params)
    {
        return GoodsUnit::find($params)->toArray();
    }
}