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

namespace app\common\model;

use app\adminapi\logic\goods\GoodsCategoryLogic;
use app\common\enum\YesNoEnum;

class GoodsCategory extends BaseModel
{
    /**
     * @notes 商品分类名称搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/7/21 12:02 下午
     */
    public function searchNameAttr($query, $value, $data)
    {
        $query->where('name','like', '%' .$value . '%');
    }

    /**
     * @notes 商品分类等级搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2021/7/21 12:04 下午
     */
    public function searchLevelAttr($query, $value, $data)
    {
        $query->where('level','<=',$value);
    }

    /**
     * @notes 获取完整分类名称
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/23 11:45
     */
    public function getCompleteNameAttr($value, $data)
    {
        return GoodsCategoryLogic::getCompleteName($data['id']);
    }

    /**
     * @notes 状态获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/7/31 3:26 下午
     */
    public function getIsShowDescAttr($value, $data)
    {
        return YesNoEnum::getIsShowDesc($data['is_show']);
    }

    /**
     * @notes 商品数量
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2023/5/5 6:24 下午
     */
    public function getGoodsNumAttr($value, $data)
    {
        $categoryIds = [];
        switch ($data['level']) {
            case 1:
                $categoryIds = GoodsCategory::withoutGlobalScope()
                    ->aliasSid('A')
                    ->alias('A')
                    ->join('goods_category B', 'A.id = B.pid')
                    ->where(['A.pid' => $data['id']])
                    ->field('A.id as aid,B.id as bid')
                    ->select()->toArray();
                $categoryIds = array_merge(array_column($categoryIds, 'aid'), array_column($categoryIds, 'bid'));
                break;
            case 2:
                $categoryIds = GoodsCategory::where(['pid' => $value])
                    ->column('id');
                break;
        }
        $categoryIds[] = $data['id'];
        return GoodsCategoryIndex::withoutGlobalScope()
            ->aliasSid('gci')
            ->alias('gci')
            ->join('goods g', 'g.id = gci.goods_id')
            ->where(['gci.category_id'=>$categoryIds])
            ->where('g.delete_time','null')
            ->group('gci.goods_id')
            ->count();
    }
}