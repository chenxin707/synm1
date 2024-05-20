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


use app\api\model\Goods;
use app\common\model\GoodsSupplier;
use app\common\model\GoodsSupplierCategory;
use app\common\validate\BaseValidate;

class GoodsSupplierValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId|checkDel',
        'code' => 'require|checkCode',
        'name' => 'require|checkName',
        'supplier_category_id' => 'require|checkCategory',
        'mobile' => 'mobile',
        'email' => 'email',
        'sort' => 'number|max:5',
    ];

    protected $message = [
        'code.require' => '供应商编码不能为空',
        'name.require' => '供应商名称不能为空',
        'supplier_category_id.require' => '供应商分类不能为空',
        'mobile.mobile' => '无效的手机号码',
        'email.email' => '邮箱格式不正确',
        'sort.number' => '排序必须是纯数字',
        'sort.max' => '排序最大不能超过五位数',
    ];

    public function sceneAdd()
    {
        return $this->only(['code','name','supplier_category_id','mobile','email','sort']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','code','name','supplier_category_id','mobile','email','sort'])
            ->remove('id','checkDel');
    }

    public function sceneDetail()
    {
        return $this->only(['id'])
            ->remove('id','checkDel');
    }

    /**
     * @notes 检查供应商编码是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/17 11:12
     */
    public function checkCode($value,$rule,$data)
    {
        $where[] = ['code', '=', $value];
        // 编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $result = GoodsSupplier::where($where)->select()->toArray();
        if ($result) {
            return '供应商编码已存在';
        }
        return true;
    }

    /**
     * @notes 检查供应商名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/17 11:13
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        // 编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $result = GoodsSupplier::where($where)->select()->toArray();
        if ($result) {
            return '该供应商名称已存在';
        }
        return true;
    }

    /**
     * @notes 检查供应商分类是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/17 11:18
     */
    public function checkCategory($value,$rule,$data)
    {
        $result = GoodsSupplierCategory::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '供应商分类不存在';
        }
        return true;
    }

    /**
     * @notes 检查供应商id是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/17 2:50
     */
    public function checkId($value,$rule,$data)
    {
        $result = GoodsSupplier::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '供应商不存在';
        }
        return true;
    }

    /**
     * @notes 检查供应商是否能删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author ljj
     * @date 2021/7/17 3:14
     */
    public function checkDel($value,$rule,$data)
    {
        $result = GoodsSupplier::withoutGlobalScope()->alias("GoodsSupplier")->aliasSid("GoodsSupplier")->hasWhere('goods',['supplier_id'=>$value])->select()->toArray();
        if (!empty($result)) {
            return '供应商正在使用中，无法删除';
        }
        return true;
    }
}