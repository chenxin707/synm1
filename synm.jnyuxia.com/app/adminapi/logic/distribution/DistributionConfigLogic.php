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

namespace app\adminapi\logic\distribution;

use app\common\logic\BaseLogic;
use app\common\model\DistributionConfig;
use app\common\enum\DistributionConfigEnum;
use app\common\service\FileService;
use think\facade\Db;

/**
 * 分销配置逻辑层
 * Class DistributionConfigLogic
 * @package app\adminapi\logic\distribution
 */
class DistributionConfigLogic extends BaseLogic
{
    /**
     * @notes 获取分销配置
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/22 9:38
     */
    public static function getConfig()
    {
        $dbConfig = DistributionConfig::column('value', 'key');
        $config['switch'] = $dbConfig['switch'] ?? DistributionConfigEnum::DEFAULT_SWITCH;
        $config['level'] = $dbConfig['level'] ?? DistributionConfigEnum::DEFAULT_LEVEL;
        $config['self'] = $dbConfig['self'] ?? DistributionConfigEnum::DEFAULT_SELF;
        $config['open'] = $dbConfig['open'] ?? DistributionConfigEnum::DEFAULT_OPEN;
        $config['apply_image'] = $dbConfig['apply_image'] ?? DistributionConfigEnum::DEFAULT_APPLY_IMAGE;
        $config['poster'] = $dbConfig['poster'] ?? DistributionConfigEnum::DEFAULT_POSTER;
        $config['protocol_show'] = $dbConfig['protocol_show'] ?? DistributionConfigEnum::DEFAULT_PROTOCOL_SHOW;
        $config['protocol_content'] = $dbConfig['protocol_content'] ?? DistributionConfigEnum::DEFAULT_PROTOCOL_CONTENT;
        $config['cal_method'] = $dbConfig['cal_method'] ?? DistributionConfigEnum::DEFAULT_CAL_METHOD;
        $config['settlement_timing'] = $dbConfig['settlement_timing'] ?? DistributionConfigEnum::DEFAULT_SETTLEMENT_TIMING;
        $config['settlement_time'] = $dbConfig['settlement_time'] ?? DistributionConfigEnum::DEFAULT_SETTLEMENT_TIME;

        $config['apply_image'] = FileService::getFileUrl($config['apply_image']);
        $config['poster'] = FileService::getFileUrl($config['poster']);

        // 商品详情页是否显示佣金 0-不显示 1-显示
        if (!isset($dbConfig['is_show_earnings'])) {
            $config['is_show_earnings'] = 1;
        } else if(empty((int)$dbConfig['is_show_earnings'])) {
            $config['is_show_earnings'] = 0;
        } else {
            $config['is_show_earnings'] = 1;
        }
        // 详情页佣金可见用户 0-全部用户 1-分销商
        if (!isset($dbConfig['show_earnings_scope'])) {
            $config['show_earnings_scope'] = 0;
        } else if(empty((int)$dbConfig['show_earnings_scope'])) {
            $config['show_earnings_scope'] = 0;
        } else {
            $config['show_earnings_scope'] = 1;
        }

        $config = self::stringToInteger($config);

        return $config;
    }

    /**
     * @notes 分销配置
     * @param $params
     * @author Tab
     * @date 2021/7/22 9:38
     */
    public static function setConfig($params)
    {
        Db::startTrans();
        try {
            $allowFields = ['switch', 'level', 'self','open', 'apply_image', 'protocol_show', 'protocol_content', 'binding_condition', 'cal_method','settlement_timing', 'settlement_time', 'is_show_earnings', 'show_earnings_scope'];

            foreach($params as $k => $v) {
                // 判断是否在允许修改的字段中
                if(!in_array($k, $allowFields, true)) {
                    continue;
                }
                if ($k == 'settlement_time' && $v < 0) {
                    throw new \Exception('结算天数不允许负数');
                }
                if ($k == 'apply_image' || $k == 'poster') {
                    $v = empty($v) ? '' : FileService::setFileUrl($v);
                }
                $item = DistributionConfig::where('key', $k)->findOrEmpty();
                if($item->isEmpty()) {
                    // 没有则创建
                    DistributionConfig::duokaiCreate(['key' => $k, 'value' => $v]);
                    continue;
                }
                // 有则更新
                $item->value = $v;
                $item->duokaiSave();
            }

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 字符串数字 转 纯数字
     */
    public static function stringToInteger($config) {
        foreach($config as $key => $value) {
            if ($key == 'apply_image' || $key == 'poster') {
                continue;
            }
            $config[$key] = (int)$value;
        }
        return $config;
    }
}
