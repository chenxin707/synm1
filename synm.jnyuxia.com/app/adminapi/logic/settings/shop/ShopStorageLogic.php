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

namespace app\adminapi\logic\settings\shop;

use app\common\cache\DuokaiCache;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\RegionService;
use think\facade\Cache;

/**
 * 存储设置逻辑层
 * Class ShopStorageLogic
 * @package app\adminapi\logic\settings\shop
 */
class ShopStorageLogic extends BaseLogic
{

    /**
     * @notes 存储引擎列表
     * @return array[]
     * @author suny
     * @date 2021/9/9 4:52 下午
     */
    public static function lists()
    {

        $default = ConfigService::get('storage', 'default', 'local');

        $data = [
            [
                'name' => '本地存储',
                'path' => '存储在本地服务器',
                'engine' => 'local',
                'status' => $default == 'local' ? 1 : 0
            ],
            [
                'name' => '七牛云存储',
                'path' => '存储在七牛云，请前往七牛云开通存储服务',
                'engine' => 'qiniu',
                'status' => $default == 'qiniu' ? 1 : 0
            ],
            [
                'name' => '阿里云OSS',
                'path' => '存储在阿里云，请前往阿里云开通存储服务',
                'engine' => 'aliyun',
                'status' => $default == 'aliyun' ? 1 : 0
            ],
            [
                'name' => '腾讯云OSS',
                'path' => '存储在腾讯云，请前往腾讯云开通存储服务',
                'engine' => 'qcloud',
                'status' => $default == 'qcloud' ? 1 : 0
            ]
        ];
        return $data;
    }

    /**
     * @notes 存储设置信息
     * @return mixed
     * @author suny
     * @date 2021/9/9 3:19 下午
     */
    public static function index($param): mixed
    {

        $default = ConfigService::get('storage', 'default', '');

        // 本地存储
        $local = ['status' => $default == 'local' ? 1 : 0];
        // 七牛云存储
        $qiniu = ConfigService::get('storage', 'qiniu', [
            'bucket' => '',
            'access_key' => '',
            'secret_key' => '',
            'domain' => '',
            'status' => $default == 'qiniu' ? 1 : 0
        ]);

        // 阿里云存储
        $aliyun = ConfigService::get('storage', 'aliyun', [
            'bucket' => '',
            'access_key' => '',
            'secret_key' => '',
            'domain' => '',
            'status' => $default == 'aliyun' ? 1 : 0
        ]);

        // 腾讯云存储
        $qcloud = ConfigService::get('storage', 'qcloud', [
            'bucket' => '',
            'region' => '',
            'access_key' => '',
            'secret_key' => '',
            'domain' => '',
            'status' => $default == 'qcloud' ? 1 : 0
        ]);

        $data = [
            'local' => $local,
            'qiniu' => $qiniu,
            'aliyun' => $aliyun,
            'qcloud' => $qcloud
        ];
        $result = $data[$param['engine']];
        if ($param['engine'] == $default) {
            $result['status'] = 1;
        } else {
            $result['status'] = 0;
        }
        return $result;
    }

    /**
     * @notes 设置存储参数
     * @param $params
     * @return bool
     * @author suny
     * @date 2021/9/9 3:56 下午
     */
    public static function setup($params)
    {

        if ($params['status'] == 1) { //状态为开启
            ConfigService::set('storage', 'default', $params['engine']);
        } else {
            ConfigService::set('storage', 'default', 'local');
        }

        switch ($params['engine']) {
            case 'local':
                ConfigService::set('storage', 'local', []);
                break;
            case 'qiniu':
                ConfigService::set('storage', 'qiniu', [
                    'bucket' => $params['bucket'] ?? '',
                    'access_key' => $params['access_key'] ?? '',
                    'secret_key' => $params['secret_key'] ?? '',
                    'domain'     => $params['domain'] ?? ''
                ]);
                break;
            case 'aliyun':
                ConfigService::set('storage', 'aliyun', [
                    'bucket' => $params['bucket'] ?? '',
                    'access_key' => $params['access_key'] ?? '',
                    'secret_key' => $params['secret_key'] ?? '',
                    'domain' => $params['domain'] ?? ''
                ]);
                break;
            case 'qcloud':
                ConfigService::set('storage', 'qcloud', [
                    'bucket' => $params['bucket'] ?? '',
                    'region' => $params['region'] ?? '',
                    'access_key' => $params['access_key'] ?? '',
                    'secret_key' => $params['secret_key'] ?? '',
                    'domain' => $params['domain'] ?? '',
                ]);
                break;
        }

        (new DuokaiCache())->delete('STORAGE_DEFAULT');
        (new DuokaiCache())->delete('STORAGE_ENGINE');
        if($params['engine'] == 'local' && $params['status'] == 0){
            return '默认开启本地存储';
        }else{
            return true;
        }
    }

    /**
     * @notes 切换状态
     * @param $params
     * @author suny
     * @date 2021/9/10 2:58 下午
     */
    public static function change($params)
    {
        $default = ConfigService::get('storage', 'default', '');
        if($default == $params['engine']){
            ConfigService::set('storage', 'default', 'local');
        }else{
            ConfigService::set('storage', 'default', $params['engine']);
        }
    }
}