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

namespace app\adminapi\logic\selffetch_shop;


use app\common\logic\BaseLogic;
use app\common\model\SelffetchShop;

class SelffetchShopLogic extends BaseLogic
{
    /**
     * @notes 添加自提门店
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/11 3:14 下午
     */
    public function add($params)
    {
        $selffetch_shop = new SelffetchShop;
        $selffetch_shop->name = $params['name'];
        $selffetch_shop->image = $params['image'];
        $selffetch_shop->contact = $params['contact'];
        $selffetch_shop->mobile = $params['mobile'];
        $selffetch_shop->province = $params['province'];
        $selffetch_shop->city = $params['city'] ?? '';
        $selffetch_shop->district = $params['district'] ?? '';
        $selffetch_shop->address = $params['address'];
        $selffetch_shop->longitude = $params['longitude'];
        $selffetch_shop->latitude = $params['latitude'];
        $selffetch_shop->business_start_time = $params['business_start_time'];
        $selffetch_shop->business_end_time = $params['business_end_time'];
        $selffetch_shop->weekdays = $params['weekdays'];
        $selffetch_shop->status = $params['status'];
        $selffetch_shop->remark = $params['remark'] ?? '';
        return $selffetch_shop->duokaiSave();
    }

    /**
     * @notes 编辑自提门店
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 3:29 下午
     */
    public function edit($params)
    {
        $selffetch_shop = SelffetchShop::find($params['id']);
        $selffetch_shop->name = $params['name'];
        $selffetch_shop->image = $params['image'];
        $selffetch_shop->contact = $params['contact'];
        $selffetch_shop->mobile = $params['mobile'];
        $selffetch_shop->province = $params['province'];
        $selffetch_shop->city = $params['city'] ?? '';
        $selffetch_shop->district = $params['district'] ?? '';
        $selffetch_shop->address = $params['address'];
        $selffetch_shop->longitude = $params['longitude'];
        $selffetch_shop->latitude = $params['latitude'];
        $selffetch_shop->business_start_time = $params['business_start_time'];
        $selffetch_shop->business_end_time = $params['business_end_time'];
        $selffetch_shop->weekdays = $params['weekdays'];
        $selffetch_shop->status = $params['status'];
        $selffetch_shop->remark = $params['remark'] ?? '';
        return $selffetch_shop->duokaiSave();
    }

    /**
     * @notes 查看自提门店详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 3:46 下午
     */
    public function detail($params)
    {
        $info = SelffetchShop::find($params['id'])->toArray();
        return $info;
    }

    /**
     * @notes 修改自提门店状态
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 4:18 下午
     */
    public function status($params)
    {
        $selffetch_shop = SelffetchShop::find($params['id']);
        $selffetch_shop->status = $params['status'];
        return $selffetch_shop->duokaiSave();
    }

    /**
     * @notes 删除自提门店
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/11 5:12 下午]
     */
    public function del($params)
    {
        return SelffetchShop::duokaiDestroy($params['id']);
    }

    /**
     * @notes 腾讯地图区域搜索
     * @param $params
     * @return mixed
     * @author ljj
     * @date 2021/9/28 6:42 下午
     */
    public function regionSearch($params)
    {
        $query = http_build_query($params);
        $url = 'https://apis.map.qq.com/ws/place/v1/search';//城市、区域搜索api
        return json_decode(file_get_contents($url.'?'.$query),true);
    }
}