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

namespace app\platformapi\logic\settings;

use app\common\logic\BaseLogic;

class EnvLogic extends BaseLogic
{
    /**
     * @notes 系统环境
     * @author Tab
     * @date 2021/12/27 14:14
     */
    public static function systemEnv()
    {
        $data = [
            'server_info' => self::serverInfo(),
            'php_env' => self::phpEnv()
        ];
        return $data;
    }

    /**
     * @notes 服务器信息
     * @author Tab
     * @date 2021/12/27 14:36
     */
    public static function serverInfo()
    {
        return [
            [
                'name' => '服务器操作系统',
                'value' => PHP_OS,
                'status' => '',
                'remark' => '',
            ],
            [
                'name' => 'Web服务器环境',
                'value' => $_SERVER['SERVER_SOFTWARE'],
                'status' => '',
                'remark' => '',
            ],
            [
                'name' => 'PHP版本',
                'value' => PHP_VERSION,
                'status' => '',
                'remark' => '',
            ],
            [
                'name' => '文件上传限制',
                'value' => @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '未知',
                'status' => '',
                'remark' => '',
            ],
            [
                'name' => '程序运行目录',
                'value' => public_path(),
                'status' => '',
                'remark' => '',
            ],
        ];
    }

    /**
     * @notes PHP环境要求
     * @author Tab
     * @date 2021/12/27 14:47
     */
    public static function phpEnv()
    {
        return [
            [
                'name' => 'PHP版本',
                'value' => '8.0.0及以上',
                'status' => version_compare(PHP_VERSION, '8.0.0') === -1 ? false : true,
                'remark' => version_compare(PHP_VERSION, '8.0.0') === -1 ? 'PHP版本必须为 8.0.0及以上' : ''
            ],
            [
                'name' => 'CURL',
                'value' => '支持',
                'status' => extension_loaded('curl') && function_exists('curl_init') ? true : false,
                'remark' => extension_loaded('curl') && function_exists('curl_init') ? '' : '您的PHP环境不支持CURL, 系统无法正常运行'
            ],
            [
                'name' => 'PDO',
                'value' => '支持',
                'status' => extension_loaded('PDO') && extension_loaded('pdo_mysql') ? true : false,
                'remark' => extension_loaded('PDO') && extension_loaded('pdo_mysql') ? '' : '您的PHP环境不支持PDO, 系统无法正常运行'
            ]
        ];
    }
}