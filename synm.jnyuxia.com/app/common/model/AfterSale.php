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

use app\common\enum\AfterSaleEnum;
use app\common\service\FileService;
/**
 * 售后类
 * Class AfterSale
 * @package app\common\model
 */
class AfterSale extends BaseModel
{

    /**
     * @notes 关联售后商品模型
     * @return \think\model\relation\HasOne
     * @author Tab
     * @date 2021/8/2 14:34
     */
    public function afterSaleGoods()
    {
        return $this->hasMany(AfterSaleGoods::class, 'after_sale_id', 'id')
            ->field('after_sale_id, order_goods_id as goods_snap');
    }

    /**
     * @notes 售后类型获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/2 14:37
     */
    public function getRefundTypeDescAttr($value)
    {
        $desc = [1 => '整单退款', 2 => '商品售后'];

        return $desc[$value] ?? '';
    }

    /**
     * @notes 售后方式获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/2 14:38
     */
    public function getRefundMethodDescAttr($value)
    {
        $desc = [1 => '仅退款', 2 => '退货退款'];

        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取售后状态描述
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/2 14:43
     */
    public function getStatusDescAttr($value)
    {
        return AfterSaleEnum::getStatusDesc($value);
    }

    /**
     * @notes 获取售后子状态描述
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/8/2 14:44
     */
    public function getSubStatusDescAttr($value)
    {
        return AfterSaleEnum::getSubStatusDesc($value);
    }

    /**
     * @notes 商品编码搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/8/2 17:56
     */
    public function searchGoodsInfoAttr($query, $value, $data)
    {
        if(!isset($data['goods_info']) || empty($data['goods_info'])) {
            return false;
        }
        $goodsId = Goods::where('name|code', 'like', '%'.$data['goods_info'].'%')->value('id');
        $afterSaleIds = AfterSaleGoods::where('goods_id', $goodsId)->column('after_sale_id');
        $query->where('as.id', 'in', $afterSaleIds);
    }

    /**
     * @notes 凭证修改器
     * @param $voucher
     * @return false|string
     * @author Tab
     * @date 2021/8/26 10:22
     */
    public function setVoucherAttr($voucher)
    {
        foreach($voucher as &$value) {
            FileService::setFileUrl($value);
        }
        if (empty($voucher)) {
            return '';
        }
        return json_encode($voucher, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @notes 凭证获取器
     * @param $voucher
     * @return mixed|string
     * @author Tab
     * @date 2021/8/26 10:23
     */
    public function getVoucherAttr($voucher)
    {
        return empty($voucher) ? '' : json_decode($voucher, true);
    }

    public function getExpressNameAttr($value)
    {
        return empty($value) ? '-' : $value;
    }

    public function getInvoiceNoAttr($value)
    {
        return empty($value) ? '-' : $value;
    }

    public function getExpressTimeAttr($value)
    {
        return empty($value) ? '-' : date('Y-m-d H:i:s');
    }
}