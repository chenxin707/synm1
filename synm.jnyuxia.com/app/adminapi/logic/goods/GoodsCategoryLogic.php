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
use app\common\model\GoodsCategory;

class GoodsCategoryLogic
{
    /**
     * @notes 添加商品分类
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/17 5:46 下午
     */
    public function add($params)
    {
        $goods_category = new GoodsCategory;
        $goods_category->name = $params['name'];
        $goods_category->pid = $params['pid'] ?? 0;
        $goods_category->level = isset($params['pid']) ? (GoodsCategory::where('id',$params['pid'])->value('level') + 1) : 1;
        $goods_category->image = $params['image'] ?? '';
        $goods_category->sort = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : DefaultEnum::SORT;
        $goods_category->is_show = $params['is_show'] ?? YesNoEnum::YES;
        $goods_category->is_recommend = $params['is_recommend'] ?? YesNoEnum::YES;
        return $goods_category->duokaiSave();
    }

    /**
     * @notes 修改商品分类状态
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 11:55 上午
     */
    public function status($params)
    {
        $goods_category = GoodsCategory::find($params['id']);
        $goods_category->is_show = $params['is_show'];
        return $goods_category->duokaiSave();
    }

    /**
     * @notes 删除商品分类
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/19 4:03 下午
     */
    public function del($params)
    {
        return GoodsCategory::duokaiDestroy($params['id']);
    }

    /**
     * @notes 编辑商品分类
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 4:21 下午
     */
    public function edit($params)
    {
        $pid = $params['pid'] ?? 0;
        $level = ($pid > 0) ? (GoodsCategory::where('id',$pid)->value('level') + 1) : 1;

        //更新分类信息
        $goods_category = GoodsCategory::find($params['id']);
        $goods_category->name = $params['name'];
        $goods_category->pid = $pid;
        $goods_category->level = $level;
        $goods_category->image = $params['image'];
        $goods_category->sort = $params['sort'];
        $goods_category->is_show = $params['is_show'];
        $goods_category->is_recommend = $params['is_recommend'];
        $goods_category->duokaiSave();

        //更新下级分类信息
        $goods_category_son = GoodsCategory::where('pid',$params['id'])->select()->toArray();
        if (empty($goods_category_son)) {
            return true;
        }
        foreach ($goods_category_son as $val) {
            $data[] = ['id'=>$val['id'],'level'=>$level+1];
        }
        $goods_category->duokaiSaveAll($data, true, false, false);

        return true;
    }

    /**
     * @notes 查看商品分类详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 4:36 下午
     */
    public function detail($params)
    {
        return GoodsCategory::find($params['id'])->toArray();
    }

    /**
     * @notes 获取完整的分类
     * @param array $ids 获取某个某个分类
     * @return array
     * @author cjhao
     * @date 2021/7/26 10:41
     */
    public static function getCategoryNameLists(array $ids = []):array
    {
        $where = [];
        if($ids){
            $where[] = ['C.id'=>$ids];
        }
        $list = GoodsCategory::withoutGlobalScope()
            ->aliasSid('C')
            ->alias('C')
            ->leftJoin('goods_category B','C.pid = B.id')
            ->leftJoin('goods_category A','B.pid = A.id')
            ->where($where)
            ->column('A.id as A_id,A.name as A_name,B.id as B_id,B.name as B_name,C.id as C_id,C.name as C_name,
CONCAT_WS(\'/\',A.name,B.name,C.name) as category_name','C.id');

        return $list;
    }

    /**
     * @notes 获取完整分类名称
     * @param $id
     * @param bool $flag
     * @return mixed|string
     * @author Tab
     * @date 2021/7/23 12:42
     */
    public static function getCompleteName($id, $flag = true) {
        static $completeName = '';
        if($flag) {
            $completeName = GoodsCategory::where('id', $id)->value('name');
        }
        $pid = GoodsCategory::where('id', $id)->value('pid');
        if($pid) {
            $pidName = GoodsCategory::where('id', $pid)->value('name');
            $completeName = $pidName . '/' . $completeName;
            self::getCompleteName($pid, false);
        }
        return $completeName;
    }
}