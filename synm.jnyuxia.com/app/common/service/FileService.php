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


namespace app\common\service;
use app\common\cache\DuokaiCache;
use app\common\model\Config;

class FileService
{
    /**
     * @notes 获取储存引擎 TODO 该方法做了缓存处理
     * @return string
     * @author cjhao
     * @date 2021/9/1 11:59
     */
    public static function getStorage():array
    {
        $doukaiCahe = new DuokaiCache(false);
        $default = $doukaiCahe->get('storage_default');
        if(!$default){
            $default = Config::withoutGlobalScope()->where(['type' => 'storage', 'name' => 'default','sid'=>0])->value('value') ?: 'local';
            $doukaiCahe->set('storage_default', $default);
        }

        if('local' === $default){

            $domain = request()->domain(true);

        }else{

            $domain = $doukaiCahe->get('storage_engine');
            if (!$domain) {
                $storage = Config::withoutGlobalScope()->where(['type' => 'storage', 'name' => $default,'sid'=>0])->value('value');
                $domain = $storage ? json_decode($storage,true)['domain'] : '';
                $doukaiCahe->set('storage_engine', $domain);
            }
        }

        return ['storage' => $default, 'domain' => $domain];

    }
    /**
     * @notes 补全路径
     * @param $uri
     * @param bool $type
     * @return string
     * @author 张无忌
     * @date 2021/7/28 15:08
     */
    public static function getFileUrl($uri = '', $type = false)
    {
//        if(empty(trim($uri))) return $uri;
        if (strstr($uri, 'http://'))  return $uri;
        if (strstr($uri, 'https://')) return $uri;

        $fileDomain = self::getStorage();

        if ('local' === $fileDomain['storage'] && 'share' === $type) {
            return public_path(). $uri;
        }

        return $fileDomain['domain'] . '/' . $uri;
    }

    /**
     * @notes 转相对路径
     * @param $uri
     * @return mixed
     * @author 张无忌
     * @date 2021/7/28 15:09
     */
    public static function setFileUrl($uri)
    {
        $fileDomain = self::getStorage();

        if ('local' === $fileDomain['storage']) {
            return str_replace($fileDomain['domain'].'/', '', $uri);
        }
        return str_replace($fileDomain['domain'].'/', '', $uri);

    }
}