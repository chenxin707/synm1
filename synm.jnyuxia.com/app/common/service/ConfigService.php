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

declare(strict_types=1);

namespace app\common\service;

use app\common\model\Config;
use think\facade\Cache;

class ConfigService
{

    /**
     * @notes
     * @param string $type
     * @param string $name
     * @return string
     * @author 令狐冲
     * @date 2022/10/19 16:13
     */
    public static function getCacheKey($type, $name)
    {
        $sid = request()->sid;
        $sid = $sid ? '-' . $sid : '';
        return 'config' . '-' . $type . '-' . $name . $sid;
    }


    /**
     * @notes 设置配置
     * @param $type
     * @param $name
     * @param $value
     * @return mixed
     * @author 令狐冲
     * @date 2022/11/8 15:21
     */
    public static function set($type, $name, $value)
    {
        //刷新缓存
        $cacheKey = self::getCacheKey($type, $name);
        Cache::delete($cacheKey);
        return self::dBset($type, $name, $value);

    }

    /**
     * @notes 从缓存获取配置
     * @param $type
     * @param string $name
     * @param null $default_value
     * @return array|int|mixed|string
     * @author 令狐冲
     * @date 2022/11/8 15:23
     */
    public static function get($type, $name = '', $default_value = null)
    {
        //获取缓存
        $CacheKey = self::getCacheKey($type, $name);
        $result = Cache::get($CacheKey);
        $value = $result['config_service'] ?? null;
        if ($value !== null) {
            return $value;
        }
        $value = self::dbGet($type, $name, $default_value);
        Cache::set($CacheKey, ['config_service' => $value]);
        return $value;
    }

    /**
     * @notes 设置配置值
     * @param $type
     * @param $name
     * @param $value
     * @return mixed
     * @author Tab
     * @date 2021/7/15 14:54
     */
    public static function dbSet($type, $name, $value)
    {
        $original = $value;
        // 数组数据进行json编码
        if (is_array($value)) {
            // JSON_UNESCAPED_UNICODE 不对中文进行unicode编码
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $data = Config::where(['type' => $type, 'name' => $name])->findOrEmpty();
        if ($data->isEmpty()) {
            // 没有则添加
            Config::duokaiCreate([
                'type' => $type,
                'name' => $name,
                'value' => $value,
            ]);
        } else {
            // 有则修改
            $data->value = $value;
            $data->duokaiSave();
        }
        // 返回原始值
        return $original;
    }

    /**
     * @notes 从数据库获取配置值
     * @param $type
     * @param string $name
     * @param null $default_value
     * @return array|int|mixed|string
     * @author Tab
     * @date 2021/7/15 15:16
     */
    public static function dbGet($type, $name = '', $default_value = null)
    {
        if ($name) {
            $value = Config::where(['type' => $type, 'name' => $name])->value('value');

            if (!is_null($value)) {
                $json = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $json : $value;
            }

            if ($value) {
                return $value;
            }
            // 返回特殊值 0 '0'
            if ($value === 0 || $value === '0') {
                return $value;
            }
            // 返回默认值
            if ($default_value !== null) {
                return $default_value;
            }

            // 返回本地配置文件中的值
            return config('project.' . $type . '.' . $name);
        }
        // 取某个类型下的所有name的值
        $data = Config::where(['type' => $type])->column('value', 'name');
        foreach ($data as $k => $v) {
            $json = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[$k] = $json;
            }
        }
        if ($data) {
            return $data;
        }
    }
}