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
use app\common\model\GoodsSupplier;

class GoodsSupplierLogic
{
    /**
     * @notes 添加供应商
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/17 11:46
     */
    public function add($params)
    {
        $goods_supplier = new GoodsSupplier;
        $goods_supplier->code = $params['code'];
        $goods_supplier->name = $params['name'];
        $goods_supplier->supplier_category_id = $params['supplier_category_id'];
        $goods_supplier->contact = $params['contact'] ?? '';
        $goods_supplier->mobile = $params['mobile'] ?? '';
        $goods_supplier->landline = $params['landline'] ?? '';
        $goods_supplier->email = $params['email'] ?? '';
        $goods_supplier->province_id = $params['province_id'] ?? '';
        $goods_supplier->city_id = $params['city_id'] ?? '';
        $goods_supplier->district_id = $params['district_id'] ?? '';
        $goods_supplier->address = $params['address'] ?? '';
        $goods_supplier->bank_account = $params['bank_account'] ?? '';
        $goods_supplier->bank = $params['bank'] ?? '';
        $goods_supplier->cardholder_name = $params['cardholder_name'] ?? '';
        $goods_supplier->tax_id = $params['tax_id'] ?? '';
        $goods_supplier->sort = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : DefaultEnum::SORT;
        return $goods_supplier->duokaiSave();
    }

    /**
     * @notes 删除供应商
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/17 3:14
     */
    public function del($params)
    {
        return GoodsSupplier::duokaiDestroy($params['id']);
    }

    /**
     * @notes 编辑供应商
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/17 3:42 下午
     */
    public function edit($params)
    {
        $goods_supplier = GoodsSupplier::find($params['id']);
        $goods_supplier->code = $params['code'];
        $goods_supplier->name = $params['name'];
        $goods_supplier->supplier_category_id = $params['supplier_category_id'];
        $goods_supplier->contact = $params['contact'];
        $goods_supplier->mobile = $params['mobile'];
        $goods_supplier->landline = $params['landline'];
        $goods_supplier->email = $params['email'];
        $goods_supplier->province_id = $params['province_id'];
        $goods_supplier->city_id = $params['city_id'];
        $goods_supplier->district_id = $params['district_id'];
        $goods_supplier->address = $params['address'];
        $goods_supplier->bank_account = $params['bank_account'];
        $goods_supplier->bank = $params['bank'];
        $goods_supplier->cardholder_name = $params['cardholder_name'];
        $goods_supplier->tax_id = $params['tax_id'];
        $goods_supplier->sort = $params['sort'];
        return $goods_supplier->duokaiSave();
    }

    /**
     * @notes 查看供应商详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/19 4:59 下午
     */
    public function detail($params)
    {
        return GoodsSupplier::find($params['id'])->toArray();
    }
}