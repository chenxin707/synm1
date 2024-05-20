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

namespace app\common\model;

use think\facade\Config;

/**
 * 商户模型
 */
class PlatformShop extends BaseModel
{
    // 关闭全局查询范围
    protected $globalScope = [];

    public function getStatusDescAttr($value, $data)
    {
        return $data['status'] ? '正常' : '关闭';
    }


    public function getShopAdminUrlAttr($value, $data)
    {
        if($data['domain_alias']){
            return  $data['domain_alias'] . '/admin';
        }
        $subDir = str_replace('\\', '/', dirname(request()->server('PHP_SELF')));
        return request()->scheme() . '://shop' . $data['sn'] . '.' . secondary_domain() . '/admin';
    }

    //商城的域名
    public function getShopUrlAttr($value,$data){
        return 'shop' . $data['sn'] . '.' . secondary_domain();
    }

    /**
     * @notes 到期时间
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2022/3/4 6:07 下午
     */
    public function getExpiresTimeAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$value);
    }

    /**
     * @notes 套餐名称
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2022/3/4 6:15 下午
     */
    public function getSetMealNameAttr($value,$data)
    {
        return SetMeal::where('id',$data['set_meal_id'])->value('name');
    }

    /**
     * @notes 套餐到期状态描述
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2022/3/4 6:22 下午
     */
    public function getExpiresStatusAttr($value,$data)
    {
        $time = time();
        $expires_time = $data['expires_time'];
        if (($expires_time - $time) > 7*24*60*60) {
            return '使用正常';
        }elseif (($expires_time - $time) > 0 && ($expires_time - $time) < 7*24*60*60) {
            return '即将到期';
        }elseif (($expires_time - $time) <= 0) {
            return '已过期';
        }
        return '未知';
    }
}
