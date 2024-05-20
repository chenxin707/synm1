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


use app\common\model\GoodsSupplierCategory;
use app\common\validate\BaseValidate;

class GoodsSupplierCategoryValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId|checkDel',
        'name' => 'require|checkName',
        'sort' => 'number|max:5'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'name.require' => '供应商分类名称不能为空',
        'sort.number' => '排序必须为数字',
        'sort.max' => '排序最大不能超过五位数',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','sort']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','sort'])
            ->append('sort','require')
            ->remove('id','checkDel');
    }

    public function sceneDetail()
    {
        return $this->only(['id'])
            ->remove('id','checkDel');
    }

    /**
     * @notes 检查供应商分类名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/16 5:25
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        // 编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $result = GoodsSupplierCategory::where($where)->select()->toArray();
        if ($result) {
            return '该供应商分类已存在';
        }
        return true;
    }

    /**
     * @notes 检查供应商分类ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/16 6:22
     */
    public function checkId($value,$rule,$data)
    {
        $result = GoodsSupplierCategory::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '供应商分类不存在';
        }
        return true;
    }

    /**
     * @notes 检查供应商分类能否删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author ljj
     * @date 2021/7/16 6:57
     */
    public function checkDel($value,$rule,$data)
    {
        $result = GoodsSupplierCategory::withoutGlobalScope()->alias('GoodsSupplierCategory')->aliasSid('GoodsSupplier')->hasWhere('goodsSupplier',['supplier_category_id'=>$value])->select()->toArray();
        if (!empty($result)) {
            return '该供应商分类正在使用中，无法删除';
        }
        return true;
    }
}