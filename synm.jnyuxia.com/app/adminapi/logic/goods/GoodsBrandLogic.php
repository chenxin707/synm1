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
use app\common\enum\YesNoEnum;
use app\common\model\GoodsBrand;

class GoodsBrandLogic
{
    /**
     * @notes 添加商品品牌
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/14 5:00
     */
    public function add($params)
    {
        $goods_brand = new GoodsBrand;
        $goods_brand->name = $params['name'];
        $goods_brand->image = $params['image'] ?? '';
        $goods_brand->sort = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : DefaultEnum::SORT;
        $goods_brand->is_show = $params['is_show'] ?? YesNoEnum::YES;
        return $goods_brand->duokaiSave();
    }

    /**
     * @notes 删除商品品牌
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/15 10:37
     */
    public function del($params)
    {
        return GoodsBrand::duokaiDestroy($params['id']);
    }

    /**
     * @notes 编辑商品品牌
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/15 11:05
     */
    public function edit($params)
    {
        $goods_brand = GoodsBrand::find($params['id']);
        $goods_brand->name = $params['name'];
        $goods_brand->image = $params['image'];
        $goods_brand->is_show = $params['is_show'];
        $goods_brand->sort = $params['sort'];
        return $goods_brand->duokaiSave();
    }

    /**
     * @notes 修改商品品牌显示状态
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/15 11:41
     */
    public function status($params)
    {
        $goods_brand = GoodsBrand::find($params['id']);
        $goods_brand->is_show = $params['is_show'];
        return $goods_brand->duokaiSave();
    }

    /**
     * @notes 查看商品品牌详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 4:45 下午
     */
    public function detail($params)
    {
        return GoodsBrand::find($params['id'])->toArray();
    }
}