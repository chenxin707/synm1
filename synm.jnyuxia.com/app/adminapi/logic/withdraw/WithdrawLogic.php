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

namespace app\adminapi\logic\withdraw;

use app\common\enum\AccountLogEnum;
use app\common\enum\WithdrawEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\BaseLogic;
use app\common\model\User;
use app\common\model\UserAuth;
use app\common\model\WithdrawApply;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use think\facade\Db;

/**
 * 提现逻辑层
 * Class WithdrawLogic
 * @package app\adminapi\logic\withdraw
 */
class WithdrawLogic extends BaseLogic
{
    /**
     * @notes 查看提现详情
     * @param $params
     * @return mixed
     * @author Tab
     * @date 2021/8/6 20:48
     */
    public static function detail($params)
    {
        $field = 'wa.id,wa.money,wa.handling_fee,wa.left_money,wa.type,wa.type as type_desc,wa.create_time,wa.status,wa.status as status_desc,wa.transfer_voucher,wa.transfer_time,wa.transfer_remark,wa.account,wa.real_name,wa.bank,wa.subbank,wa.money_qr_code';
        $field .= ',u.sn,u.nickname,u.mobile';
        $withdrawApply = WithdrawApply::withoutGlobalScope()
            ->aliasSid('wa')
            ->alias('wa')
            ->field($field)
            ->leftJoin('user u', 'u.id = wa.user_id')
            ->findOrEmpty($params['id'])
            ->toArray();

        return $withdrawApply;
    }

    /**
     * @notes 审核拒绝
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/9 9:58
     */
    public static function refuse($params)
    {
        Db::startTrans();
        try {
            // 修改提现申请单状态
            $withdrawApply = WithdrawApply::findOrEmpty($params['id']);
            if($withdrawApply->status != WithdrawEnum::STATUS_WAIT) {
                throw new \think\Exception('不是待提现状态,不允许审核');
            }
            $withdrawApply->status = WithdrawEnum::STATUS_FAIL;
            $withdrawApply->audit_remark = $params['audit_remark'] ?? '';
            $withdrawApply->duokaiSave();

            // 回退提现金额
            self::fallbackMoney($withdrawApply);

            // 增加账户流水变动记录
            AccountLogLogic::add($withdrawApply['user_id'], AccountLogEnum::BW_INC_REFUSE_WITHDRAWAL, AccountLogEnum::INC, $withdrawApply['money'], $withdrawApply['sn'], '拒绝提现回退金额');

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 回退提现金额
     * @param $withdrawApply
     * @author Tab
     * @date 2021/8/9 9:50
     */
    public static function fallbackMoney($withdrawApply)
    {
        $user = User::findOrEmpty($withdrawApply->user_id);
        $user->user_earnings = $user->user_earnings + $withdrawApply->money;
        $user->duokaiSave();
    }

    /**
     * @notes 审核通过
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/9 10:49
     */
    public static function pass($params)
    {
        Db::startTrans();
        try {
            $withdrawApply = WithdrawApply::findOrEmpty($params['id']);
            if($withdrawApply->status != WithdrawEnum::STATUS_WAIT) {
                throw new \think\Exception('不是待提现状态,不允许审核');
            }
            switch($withdrawApply->type) {
                // 提现至余额
                case WithdrawEnum::TYPE_BALANCE:
                    self::balance($withdrawApply, $params);
                    break;
                // 提现至微信零钱
                case WithdrawEnum::TYPE_WECHAT_CHANGE:
                    self::wechatChange($withdrawApply, $params);
                    break;
                // 提现至银行卡
                case WithdrawEnum::TYPE_BANK:
                // 提现至微信收款码
                case WithdrawEnum::TYPE_WECHAT_CODE:
                // 提现至支付宝收款码
                case WithdrawEnum::TYPE_ALI_CODE:
                    self::common($withdrawApply, $params);
                    break;
            }

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 提现至余额
     * @param $withdrawApply
     * @author Tab
     * @date 2021/8/9 10:48
     */
    public static function balance($withdrawApply, $params)
    {
        // 增加用户余额
        $user = User::findOrEmpty($withdrawApply->user_id);
        $user->user_money = $user->user_money + $withdrawApply->left_money;
        $user->duokaiSave();
        // 记录账户流水
        AccountLogLogic::add($withdrawApply->user_id, AccountLogEnum::BNW_INC_WITHDRAW, AccountLogEnum::INC, $withdrawApply->left_money, $withdrawApply->sn, '提现至余额');
        // 更新提现状态
        $withdrawApply->status = WithdrawEnum::STATUS_SUCCESS;
        $withdrawApply->audit_remark = $params['audit_remark'] ?? '';
        $withdrawApply->duokaiSave();
    }

    /**
     * @notes 提现至银行卡/微信收款码/支付宝收款
     * @param $withdrawApply
     * @param $params
     * @author Tab
     * @date 2021/8/9 11:01
     */
    public static function common($withdrawApply, $params)
    {
        $withdrawApply->status = WithdrawEnum::STATUS_ING;
        $withdrawApply->audit_remark = $params['audit_remark'] ?? '';
        $withdrawApply->duokaiSave();
    }

    /**
     * @notes 提现至微信零钱
     * @param $withdrawApply
     * @param $params
     * @return bool
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/9 12:03
     */
    public static function wechatChange($withdrawApply, $params)
    {
        // 校验条件
        self::checkCondition($withdrawApply);
        // 用户授权信息
        $userAuth = UserAuth::where('user_id', $withdrawApply->user_id)->order('terminal', 'asc')->findOrEmpty();
        if($userAuth->isEmpty()) {
            throw new \think\Exception('获取不到用户的openid');
        }
        // 获取app
        $config = WeChatConfigService::getWechatConfigByTerminal($userAuth->terminal);

        //微信零钱接口：1-企业付款到零钱;2-商家转账到零钱;
        $transfer_way = ConfigService::get('config', 'transfer_way',1);
        //企业付款到零钱
        if ($transfer_way == WithdrawEnum::ENTERPRISE) {
            $app = Factory::payment($config);
            // 发起企业付款
            $result = $app->transfer->toBalance([
                // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                'partner_trade_no' => $withdrawApply->sn,
                'openid' => $userAuth->openid,
                // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                'check_name' => 'NO_CHECK',
                // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                're_user_name' => '',
                // 企业付款金额，单位为分 100分=1元
                'amount' => 100 * $withdrawApply->left_money,
                // 企业付款操作说明信息。必填
                'desc' => '提现至微信零钱'
            ]);
            // 过滤敏感字段
            $fiterField = ['appid','mch_appid', 'mchid', 'mch_id', 'openid'];
            $filterResult = array_filter($result, function($key) use ($fiterField) {
                return !in_array($key, $fiterField);
            }, ARRAY_FILTER_USE_KEY);
            // 更新提现申请单为提现中状态
            $withdrawApply->status = WithdrawEnum::STATUS_ING;
            $withdrawApply->audit_remark = $params['audit_remark'] ?? '';
            $withdrawApply->pay_desc = json_encode($filterResult);
            $withdrawApply->save();
            // 通信标识 return_code && 业务结果 result_code
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                // 企业付款成功, 更新提现申请单状态为提现成功并记录支付单号及支付时间
                $withdrawApply->status = WithdrawEnum::STATUS_SUCCESS;
                $withdrawApply->payment_no = $result['payment_no'];
                $withdrawApply->payment_time = strtotime($result['payment_time']);
                $withdrawApply->save();
                return true;
            }
            if($result['return_code'] == 'FAIL') {
                // 企业付款失败,更新提现申请单为提现失败
                $withdrawApply->status = WithdrawEnum::STATUS_FAIL;
                $withdrawApply->save();
                // 回退提现金额
                self::fallbackMoney($withdrawApply);
                // 记录账户流水
                AccountLogLogic::add($withdrawApply->user_id, AccountLogEnum::BW_INC_PAYMENT_FAIL, AccountLogEnum::INC, $withdrawApply->money, $withdrawApply->sn, '付款失败回退金额');
                return false;
            }
        }
        //商家转账到零钱
        if ($transfer_way == WithdrawEnum::MERCHANT) {
            WechatMerchantTransferLogic::transfer($withdrawApply,$userAuth,$config);
        }
    }

    /**
     * @notes 校验条件(提现至微信零钱)
     * @param $withdrawApply
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/9 11:30
     */
    public static function checkCondition($withdrawApply)
    {
        if($withdrawApply->left_money < 1) {
            throw new \think\Exception('扣除手续费后提现金额不能小于1元');
        }
        $count = WithdrawApply::whereDay('update_time')->where([
            ['user_id', '=', $withdrawApply->user_id],
            ['type', '=', WithdrawEnum::TYPE_WECHAT_CHANGE],
            ['status', 'in', [WithdrawEnum::STATUS_ING,WithdrawEnum::STATUS_SUCCESS,WithdrawEnum::STATUS_FAIL]],
        ])->count();
        if($count >= 10) {
            throw new \think\Exception('同一天向同一个用户最多付款10次');
        }
    }

    /**
     * @notes 查询结果(提现至微信零钱)
     * @param $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author Tab
     * @date 2021/8/9 15:06
     */
    public static function search($params)
    {
        Db::startTrans();
        try  {
            $withdrawApply = WithdrawApply::findOrEmpty($params['id']);
            if($withdrawApply->status != WithdrawEnum::STATUS_ING) {
                throw new \think\Exception('非提现中状态无法查询结果');
            }
            if($withdrawApply->type != WithdrawEnum::TYPE_WECHAT_CHANGE) {
                throw new \think\Exception('非微信零钱提现方式无法查询结果');
            }
            // 用户授权信息
            $userAuth = UserAuth::where('user_id', $withdrawApply->user_id)->order('terminal', 'asc')->findOrEmpty();
            if($userAuth->isEmpty()) {
                throw new \think\Exception('获取不到用户的openid');
            }
            // 获取app
            $config = WeChatConfigService::getWechatConfigByTerminal($userAuth->terminal);

            //微信零钱接口：1-企业付款到零钱;2-商家转账到零钱;
            $transfer_way = ConfigService::get('config', 'transfer_way',1);
            //企业付款到零钱
            if ($transfer_way == WithdrawEnum::ENTERPRISE) {
                $app = Factory::payment($config);
                $result = $app->transfer->queryBalanceOrder($withdrawApply->sn);
                // 过滤敏感字段
                $fiterField = ['appid','mch_appid', 'mchid', 'mch_id', 'openid'];
                $filterResult = array_filter($result, function($key) use ($fiterField) {
                    return !in_array($key, $fiterField);
                }, ARRAY_FILTER_USE_KEY );
                // 记录查询结果
                $withdrawApply->pay_search_result = json_encode($filterResult);
                $withdrawApply->save();
                // 查询失败
                if($result['return_code'] == 'FAIL') {
                    throw new \think\Exception($result['return_msg']);
                }
                // 提示信息
                $tips = $result['err_code_des'] ?? '';
                // 查询结果：转账成功
                if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['status'] == 'SUCCESS') {
                    // 更新提现申请单状态
                    $withdrawApply->status = WithdrawEnum::STATUS_SUCCESS;
                    $withdrawApply->payment_no = $result['detail_id'];
                    $withdrawApply->payment_time =  strtotime($result['payment_time']);
                    $withdrawApply->save();
                    $tips = '提现成功';
                }
                // 查询结果：转账失败
                if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['status'] == 'FAILED') {
                    // 更新提现申请单状态
                    $withdrawApply->status = WithdrawEnum::STATUS_FAIL;
                    $withdrawApply->save();
                    // 回退提现金额
                    self::fallbackMoney($withdrawApply);
                    // 记录账户流水变动
                    AccountLogLogic::add($withdrawApply->user_id, AccountLogEnum::BW_INC_PAYMENT_FAIL, AccountLogEnum::INC, $withdrawApply->money, $withdrawApply->sn, '付款失败回退金额');
                    $tips =  '提现失败';
                }

                // 查询结果：处理中
                if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['status'] == 'PROCESSING') {
                    $tips =  '提现处理中';
                }
            }
            //商家转账到零钱
            if ($transfer_way == WithdrawEnum::MERCHANT) {
                $result = WechatMerchantTransferLogic::details($withdrawApply,$config);
                $tips =  '未知';
                if(isset($result['detail_status'])) {
                    if ($result['detail_status'] == 'SUCCESS') {
                        //提现成功，更新提现申请单
                        WithdrawApply::update([
                            'status' => WithdrawEnum::STATUS_SUCCESS,
                            'pay_search_result' => json_encode($result, JSON_UNESCAPED_UNICODE),
                            'payment_no' => $result['detail_id'],
                            'payment_time' => strtotime($result['update_time']),
                        ],['id'=>$withdrawApply['id']]);
                        $tips = '提现成功';
                    }
                    if ($result['detail_status'] == 'FAIL') {
                        //提现失败，更新提现申请单
                        WithdrawApply::update([
                            'status' => WithdrawEnum::STATUS_FAIL,
                            'pay_search_result' => json_encode($result, JSON_UNESCAPED_UNICODE),
                        ],['id'=>$withdrawApply['id']]);

                        // 回退提现金额
                        WithdrawLogic::fallbackMoney($withdrawApply);
                        // 记录账户流水
                        AccountLogLogic::add($withdrawApply->user_id, AccountLogEnum::BW_INC_PAYMENT_FAIL, AccountLogEnum::INC, $withdrawApply->money, $withdrawApply->sn, '付款失败回退金额');
                        $tips =  '提现失败';
                    }
                    if ($result['detail_status'] == 'PROCESSING') {
                        $tips =  '正在处理中';
                    }
                }else {
                    // 查询失败
                    throw new \think\Exception($result['message'] ?? '商家转账到零钱查询失败');
                }
            }

            // 提交事务
            Db::commit();
            // 返回提示消息
            return $tips;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 转账成功
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/9 15:56
     */
    public static function transferSuccess($params)
    {
        try {
            if(!isset($params['transfer_voucher']) || empty($params['transfer_voucher'])) {
                throw new \think\Exception('请上传转账凭证');
            }
            if(!isset($params['transfer_remark']) || empty($params['transfer_remark'])) {
                throw new \think\Exception('请填写转账说明');
            }
            $params['transfer_voucher'] = FileService::setFileUrl($params['transfer_voucher']);
            $withdrawApply = WithdrawApply::findOrEmpty($params['id']);
            $withdrawApply->status = WithdrawEnum::STATUS_SUCCESS;
            $withdrawApply->transfer_voucher = $params['transfer_voucher'];
            $withdrawApply->transfer_remark = $params['transfer_remark'];
            $withdrawApply->transfer_time = time();
            $withdrawApply->duokaiSave();

            return true;
        } catch(\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 转账失败
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/9 16:13
     */
    public static function transferFail($params)
    {
        Db::startTrans();
        try {
//            if(!isset($params['transfer_remark']) || empty($params['transfer_remark'])) {
//                throw new \think\Exception('请填写转账说明');
//            }
            // 更新状态
            $withdrawApply = WithdrawApply::findOrEmpty($params['id']);
            $withdrawApply->status = WithdrawEnum::STATUS_FAIL;
            $withdrawApply->transfer_remark = $params['transfer_remark'];
            $withdrawApply->transfer_voucher = $params['transfer_voucher'];
            $withdrawApply->duokaiSave();

            // 回退提现金额
            self::fallbackMoney($withdrawApply);

            // 记录账户流水
            AccountLogLogic::add($withdrawApply->user_id, AccountLogEnum::BW_INC_TRANSFER_FAIL, AccountLogEnum::INC, $withdrawApply->money, $withdrawApply->sn, '转账失败回退金额');

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }
}
