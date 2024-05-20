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

use app\common\cache\RegionCache;
use app\common\model\Region;

class RegionService
{
    /**
     * @notes 获取地址
     * @param $val
     * @param string $address
     * @return mixed|string
     * @author Tab
     * @date 2021/7/17 11:11
     */
    public static function getAddress($val, $address = '')
    {
        $regionCache = new RegionCache();
        $region = $regionCache->getRegion();
        if(!$region) { // 无缓存
            $region = Region::withoutGlobalScope()->column('id,name', 'id');
            $regionCache->setRegion($region, 3600);
        }
        // 有缓存
        if(is_array($val)) { // 数组
            $temp = '';
            foreach($val as $v) {
                $temp .= isset($region[$v]) ? $region[$v]['name'] : '';
            }
            return $temp.$address;
        }

        // 非数组
        return $region[$val] ? $region[$val]['name'] : '';
    }
}