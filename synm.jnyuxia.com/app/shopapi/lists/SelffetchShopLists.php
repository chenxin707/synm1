<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\shopapi\lists;


use app\common\model\SelffetchShop;

class SelffetchShopLists extends BaseShopDataLists
{
    /**
     * @notes 查看自提门店列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/27 3:19 下午
     */
    public function lists(): array
    {
        $longitude = $this->params['longitude'] ?? '113.26699632855939';
        $latitude = $this->params['latitude'] ?? '23.129000787845236';
        $coordinate = convert_gcj02_to_bd09(floatval($latitude), floatval($longitude));
        $longitude = $coordinate['lng'];
        $latitude = $coordinate['lat'];
        $distance = "round((6378.138*2*asin(sqrt(pow(sin(( {$latitude} *pi()/180-latitude*pi()/180)/2),2)+cos( {$latitude} *pi()/180)*cos(latitude*pi()/180)* pow(sin(( {$longitude} *pi()/180-longitude*pi()/180)/2),2)))*1000))/1000";

        $lists = SelffetchShop::field(['id', 'name', $distance => 'distance','mobile', 'business_start_time','business_end_time','province','city','district','address','longitude','latitude'])
            ->append(['detailed_address'])
            ->hidden(['province','city','district','address'])
            ->where(['status'=>1])
            ->order(['distance' => 'asc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['distance'] = round($item['distance'], 2) . 'km';
//            $coordinate = Convert_BD09_To_GCJ02($item['latitude'], $item['longitude']);
//            $item['longitude'] = $coordinate['lng'];
//            $item['latitude'] = $coordinate['lat'];
        }

        return $lists;
    }

    /**
     * @notes 查看自提门店总数
     * @return int
     * @author ljj
     * @date 2021/8/27 3:19 下午
     */
    public function count(): int
    {
        return SelffetchShop::where(['status'=>1])->count();
    }
}