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
namespace app\common\logic;
use app\common\model\PlatformShop;
use app\common\service\JsonService;

class ShopLogic extends BaseLogic {

    /**
     * @notes 获取商家id
     * @return mixed|\think\response\Json
     * @author cjhao
     * @date 2022/2/17 11:55
     */
    public static function getSid(){
        $mainDomain = env('project.main_domain');
        $secondaryDomain = secondary_domain();
        $mainDomain = secondary_domain($mainDomain);

        //当前域名和项目主域系统相同时，用编号查找商户
        if($mainDomain == $secondaryDomain){
            $thirdDomain = third_domain();
            if ($thirdDomain === false) {
                return JsonService::fail('商户域名错误', [], 0, 1);
            }
            $subDomain = substr($thirdDomain, 4);
            
            $shop = PlatformShop::withoutGlobalScope()->where('sn', $subDomain)->findOrEmpty();
            if(!$shop->isEmpty()){
                return $shop->id;
            }

        }

        /*
         * 两种可能的情况执行了下面代码：
         * 1.如果通过编号查找的商家不存在，可能设置同个主域不同子域。
         * 2.设置了域名别名。
         */
        $domain = $_SERVER['HTTP_HOST'];
        $shop = PlatformShop::withoutGlobalScope()->where('domain_alias', $domain)->findOrEmpty();
        return $shop->id;
    }

}