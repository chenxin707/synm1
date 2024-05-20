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

namespace app\common\enum;

/**
 * 售后枚举类
 * Class AfterSaleEnum
 * @package app\common\enum
 */
class AfterSaleEnum
{
    /**
     * 售后类型
     * REFUND_TYPE_ORDER 整单退款
     * REFUND_TYPE_GOODS 商品售后
     */
    const REFUND_TYPE_ORDER = 1;
    const REFUND_TYPE_GOODS = 2;

    /**
     * 售后方式
     *  METHOD_ONLY_REFUND 仅退款
     *  METHOD_REFUND_GOODS 退货退款
     */
    const METHOD_ONLY_REFUND = 1;
    const METHOD_REFUND_GOODS = 2;

    /**
     * 退款路径
     * REFUND_WAYS_ORIGINAL 原路退回
     * REFUND_WAYS_BALANCE 退回余额
     */
    const REFUND_WAYS_ORIGINAL = 1;
    const REFUND_WAYS_BALANCE = 2;

    /**
     * 售后主状态
     * STATUS_ING 售后中
     * STATUS_SUCCESS 售后成功
     * STATUS_FAIL 售后失败
     */
    const STATUS_ING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;

    /**
     * 售后子状态
     */
    const SUB_STATUS_WAIT_SELLER_AGREE = 11;
    const SUB_STATUS_WAIT_BUYER_RETURN = 12;
    const SUB_STATUS_WAIT_SELLER_RECEIPT = 13;
    const SUB_STATUS_WAIT_SELLER_HANDLE = 14;
    const SUB_STATUS_WAIT_SELLER_REFUND = 15;
    const SUB_STATUS_SELLER_REFUND_ING = 16;
    const SUB_STATUS_SELLER_REFUND_FAIL = 17;
    const SUB_STATUS_SELLER_REFUND_SUCCESS = 21;
    const SUB_STATUS_BUYER_CANCEL_AFTER_SALE = 31;
    const SUB_STATUS_SELLER_REFUSE_AFTER_SALE = 32;
    const SUB_STATUS_SELLER_REFUSE_RECEIPT = 33;
    const SUB_STATUS_SELLER_REFUSE_REFUND = 34;

    /**
     * 退款状态
     */
    const NO_REFUND = 1;
    const PARTIAL_REFUND = 2;
    const FULL_REFUND = 3;

    /**
     * 允许买家取消售后申请的子状态
     */
    const ALLOW_CANCEL = [
        self::SUB_STATUS_WAIT_SELLER_AGREE,
        self::SUB_STATUS_WAIT_BUYER_RETURN,
    ];

    /**
     * 允许买家重新发起售后申请的子状态
     */
    const ALLOW_REAPPLY = [
        self::SUB_STATUS_BUYER_CANCEL_AFTER_SALE,
        self::SUB_STATUS_SELLER_REFUSE_AFTER_SALE,
        self::SUB_STATUS_SELLER_REFUSE_RECEIPT,
        self::SUB_STATUS_SELLER_REFUSE_REFUND,
    ];

    /**
     * @notes 获取退款方式描述
     * @param $value
     * @param false $flag
     * @return string|string[]
     * @author Tab
     * @date 2021/8/10 12:00
     */
    public static function getMethodDesc($value, $flag = false)
    {
        $desc = [
            self::METHOD_ONLY_REFUND => '仅退款',
            self::METHOD_REFUND_GOODS => '退货退款',
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }

    /**
     * @notes 获取售后状态描述
     * @param $status
     * @return string
     * @author Tab
     * @date 2021/8/2 14:42
     */
    public static function getStatusDesc($status)
    {
        $desc = [
            self::STATUS_ING => '售后中',
            self::STATUS_SUCCESS => '售后成功',
            self::STATUS_FAIL => '售后失败',
        ];

        return $desc[$status] ?? '';
    }

    /**
     * @notes 获取子状态描述
     * @param $subStatus
     * @return string
     * @author Tab
     * @date 2021/8/2 14:40
     */
    public static function getSubStatusDesc($subStatus)
    {
        $desc = [
            self::SUB_STATUS_WAIT_SELLER_AGREE => '售后中：买家发起售后，等待卖家同意',
            self::SUB_STATUS_WAIT_BUYER_RETURN => '售后中：卖家已同意，等待买家退货',
            self::SUB_STATUS_WAIT_SELLER_RECEIPT => '售后中：买家已退货，等待卖家收货',
            self::SUB_STATUS_WAIT_SELLER_HANDLE => '售后中：卖家已收货，等待卖家处理',
            self::SUB_STATUS_WAIT_SELLER_REFUND => '售后中：卖家已处理，等待卖家退款',
            self::SUB_STATUS_SELLER_REFUND_ING => '售后中：售后退款中',
            self::SUB_STATUS_SELLER_REFUND_FAIL => '售后中：售后退款失败，等待卖家处理',
            self::SUB_STATUS_SELLER_REFUND_SUCCESS => '售后成功：售后退款成功',
            self::SUB_STATUS_BUYER_CANCEL_AFTER_SALE => '售后失败：买家取消售后',
            self::SUB_STATUS_SELLER_REFUSE_AFTER_SALE => '售后失败：卖家拒绝售后',
            self::SUB_STATUS_SELLER_REFUSE_RECEIPT => '售后失败：卖家拒绝收货',
            self::SUB_STATUS_SELLER_REFUSE_REFUND => '售后失败：卖家拒绝退款',
        ];
        return $desc[$subStatus] ?? '';
    }

    /**
     * @notes 获取退货原因
     * @param $value
     * @param false $flag
     * @return string|string[]
     * @author Tab
     * @date 2021/7/31 18:56
     */
    public static function getReason($value, $flag = false)
    {
        $desc = [
            self::METHOD_ONLY_REFUND => [
                '不喜欢/不要了',
                '快递/物流一直未送到',
                '空包裹',
                '快递/物流无跟踪记录',
                '货物破损已拒签',
                '其它',
            ],
            self::METHOD_REFUND_GOODS => [
                '7天无理由退货',
                '大小尺寸与商品描述不符',
                '颜色/图案/款式不符',
                '做工粗糙/有瑕疵',
                '质量问题',
                '卖家发错货',
                '少件(含缺少配件)',
            ]
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }

    /**
     * @notes 管理后台售后按钮
     * @param $afterSale
     * @return bool[]|false[]
     * @author Tab
     * @date 2021/8/10 10:00
     */
    public static function getBtns($afterSale)
    {
        $agreeBtn = $refuseBtn = $refuseGoodsBtn = $confirmGoodsBtn = $agreeRefundBtn = $refuseRefundBtn = $changeBtn = false;
        if($afterSale['sub_status'] == self::SUB_STATUS_WAIT_SELLER_AGREE) {
            $agreeBtn = true;
            $refuseBtn = true;
        }

        if($afterSale['sub_status'] == self::SUB_STATUS_WAIT_SELLER_REFUND) {
            $changeBtn = true;
        }

        if($afterSale['sub_status'] == self::SUB_STATUS_WAIT_SELLER_RECEIPT) {
            $refuseGoodsBtn = true;
            $confirmGoodsBtn = true;
        }

        if($afterSale['sub_status'] == self::SUB_STATUS_WAIT_SELLER_HANDLE) {
            $agreeRefundBtn = true;
            $refuseRefundBtn = true;
        }

        return [
            // 同意售后
            'agree_btn' => $agreeBtn,
            // 拒绝售后
            'refuse_btn' => $refuseBtn,
            // 拒绝收货
            'refuse_goods_btn' => $refuseGoodsBtn,
            // 确认收货
            'confirm_goods_btn' => $confirmGoodsBtn,
            // 同意退款
            'agree_refund_btn' => $agreeRefundBtn,
            // 拒绝退款
            'refuse_refund_btn' => $refuseRefundBtn,
            // 修改售后金额
            'change_btn' => $changeBtn,
        ];
    }

    /**
     * @notes 商城端售后按钮
     * @param $subStatus
     * @return bool[]|false[]
     * @author Tab
     * @date 2021/8/10 14:07
     */
    public static function getBtns2($subStatus)
    {
        $cancelBtn = $expressBtn = $reapplyBtn = false;
        if($subStatus == self::SUB_STATUS_WAIT_SELLER_AGREE) {
            $cancelBtn = true;
        }
        if($subStatus == self::SUB_STATUS_WAIT_BUYER_RETURN) {
            $cancelBtn = true;
            $expressBtn = true;
        }
        if(in_array($subStatus, self::ALLOW_REAPPLY)) {
            $reapplyBtn = true;
        }
        return [
            'cancel_btn' => $cancelBtn,
            'express_btn' => $expressBtn,
            'reapply_btn' => $reapplyBtn,
        ];
    }

    /**
     * @notes 获取退款状态描述
     * @param $value
     * @param false $flag
     * @return string|string[]
     * @author Tab
     * @date 2021/8/18 10:44
     */
    public static function getRefundStatusDesc($value, $flag = false)
    {
        $desc = [
            self::NO_REFUND => '未退款',
            self::PARTIAL_REFUND => '部分退款',
            self::FULL_REFUND => '全部退款'
        ];
        if($flag) {
            return $desc;
        }

        return $desc[$value] ?? '';
    }

    /**
     * @notes 退款路径描述
     * @param $value
     * @param false $flag
     * @return string|string[]
     * @author Tab
     * @date 2021/10/13 10:59
     */
    public static function getRefundTypeDesc($value, $flag = false)
    {
        $desc = [
            self::REFUND_WAYS_ORIGINAL => '原路退回',
            self::REFUND_WAYS_BALANCE => '退回余额',
        ];
        if($flag) {
            return $desc;
        }

        return $desc[$value] ?? '';
    }
}