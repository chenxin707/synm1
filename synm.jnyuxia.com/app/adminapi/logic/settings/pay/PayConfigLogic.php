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

namespace app\adminapi\logic\settings\pay;


use app\common\enum\PayEnum;
use app\common\logic\BaseLogic;
use app\common\model\PayConfig;

class PayConfigLogic extends BaseLogic
{
    /**
     * @notes 设置支付配置
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/27 6:14 下午
     */
    public function setConfig($params)
    {
        $pay_config = PayConfig::find($params['id']);

        $config = '';
        if ($pay_config['pay_way'] == PayEnum::WECHAT_PAY) {
            $config = [
                'interface_version' => $params['interface_version'],
                'merchant_type' => $params['merchant_type'],
                'mch_id' => $params['mch_id'],
                'pay_sign_key' => $params['pay_sign_key'],
                'apiclient_cert' => $params['apiclient_cert'],
                'apiclient_key' => $params['apiclient_key'],
            ];
        }
        if ($pay_config['pay_way'] == PayEnum::ALI_PAY) {
            $config = [
                'mode' => $params['mode'],
                'merchant_type' => $params['merchant_type'],
                'app_id' => $params['app_id'],
                'private_key' => $params['private_key'],
                'ali_public_key' => $params['ali_public_key'],
            ];
        }

        $pay_config->name = $params['name'];
        $pay_config->icon = $params['icon'];
        $pay_config->sort = $params['sort'];
        $pay_config->config = $config;
        $pay_config->remark = $params['remark'] ?? '';
        return $pay_config->duokaiSave();
    }

    /**
     * @notes 查看支付配置
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/28 2:24 下午
     */
    public function getConfig($params)
    {
        $payConfig = PayConfig::find($params['id'])->toArray();
        $payConfig['domain'] = request()->domain();
        return $payConfig;
    }
}
