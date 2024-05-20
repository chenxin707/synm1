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

namespace app\adminapi\validate\goods;


use app\common\model\Goods;
use app\common\model\GoodsCategory;
use app\common\model\GoodsCategoryIndex;
use app\common\validate\BaseValidate;

class GoodsCategoryValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|max:8|checkName',
        'pid' => 'checkLevel',
        'sort' => 'number|max:5',
        'is_show' => 'in:0,1',
        'is_recommend' => 'in:0,1',
    ];

    protected $message = [
        'name.require' => '分类名称不能为空',
        'name.max' => '分类名称字数不能超过8个',
        'sort.number' => '排序必须为纯数字',
        'sort.max' => '排序最大不能超过五位数',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','pid','sort','is_show','is_recommend']);
    }

    public function sceneStatus()
    {
        return $this->only(['id','is_show'])
            ->append('is_show','require');
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','pid','sort','is_show','is_recommend'])
            ->append('pid','checkPid');
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检查商品分类名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/17 4:56 下午
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        // 编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $result = GoodsCategory::where($where)->select()->toArray();
        if ($result) {
            return '该商品分类名称已存在';
        }
        return true;
    }

    /**
     * @notes 检查父级分类等级
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/17 5:22 下午
     */
    public function checkLevel($value,$rule,$data)
    {
        if (!isset($value)) {
            return true;
        }
        $level = GoodsCategory::where('id',$value)->value('level');
        if (empty($level)) {
            return '所选父级分类不存在';
        }
        if ($level > 2) {
            return '所选父级分类已经是最大分级';
        }

        //编辑
        if (isset($data['id'])) {
            $category_two = GoodsCategory::where('pid',$data['id'])->find();
            if (!empty($category_two)&& $level > 1) {
                return '所选父级分类超过最大分级';
            }

            $category_three = !empty($category_two) ? GoodsCategory::where('pid',$category_two['id'])->find() : [];
            if (!empty($category_three)) {
                    return '目前分类已达最大分级，不能选择父级分类';
            }
        }

        return true;
    }

    /**
     * @notes 检查分类是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/19 11:49 上午
     */
    public function checkId($value,$rule,$data)
    {
        $result = GoodsCategory::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '分类不存在';
        }
        return true;
    }

    /**
     * @notes 检查商品分类能否删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 12:06 下午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = GoodsCategoryIndex::withoutGlobalScope()->aliasSid('GCI')->alias('GCI')
            ->join('goods G','GCI.goods_id = G.id')
            ->where('category_id',$value)
            ->whereNull('G.delete_time')
            ->field("GCI.*")
            ->select()
            ->toArray();
        if (!empty($result)) {
            return '商品分类已使用，需移除分类关联的商品后再作删除';
        }
        $result = GoodsCategory::where('pid',$value)->select()->toArray();
        if (!empty($result)) {
            return '该分类存在下级，无法删除';
        }
        return true;
    }

    /**
     * @notes 检验父级分类
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/9/14 5:17 下午
     */
    public function checkPid($value,$rule,$data)
    {
        if ($value == $data['id']) {
            return '不能选择自己作为父级分类';
        }
        return true;
    }
}