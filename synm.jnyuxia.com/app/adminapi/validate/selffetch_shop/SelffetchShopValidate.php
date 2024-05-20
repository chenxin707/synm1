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

namespace app\adminapi\validate\selffetch_shop;


use app\common\model\SelffetchShop;
use app\common\model\SelffetchVerifier;
use app\common\validate\BaseValidate;

class SelffetchShopValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'image' => 'require',
        'contact' => 'require',
        'mobile' => 'require',
        'province' => 'require|number',
        'city' => 'number',
        'district' => 'number',
        'address' => 'require',
        'longitude' => 'require',
        'latitude' => 'require',
        'business_start_time' => 'require',
        'business_end_time' => 'require',
        'weekdays' => 'require',
        'status' => 'require|in:0,1',
        'keyword' => 'require',
        'boundary' => 'require',
        'key' => 'require',
    ];

    protected $message = [
        'name.require' => '门店名称不能为空',
        'image.require' => '门店LOGO不能为空',
        'contact.require' => '联系人不能为空',
        'mobile.require' => '联系电话不能为空',
        'mobile.mobile' => '不是有效的手机号码',
        'province.require' => '门店地区不能为空',
        'address.require' => '详细地址不能为空',
        'longitude.require' => '经度不能为空',
        'latitude.require' => '纬度不能为空',
        'business_start_time.require' => '营业开始时间不能为空',
        'business_end_time.require' => '营业结束时间不能为空',
        'weekdays.require' => '营业周天不能为空',
        'status.require' => '门店状态不能为空',
        'status.in' => '门店状态取值范围[0,1]',
        'keyword.require' => '搜索关键字不能为空',
        'boundary.require' => '搜索区域不能为空',
        'key.require' => '腾讯地图开发密钥不能为空',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','image','contact','mobile','province','city','district','address','longitude','latitude','business_start_time','business_end_time','weekdays','status']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','image','contact','mobile','province','city','district','address','longitude','latitude','business_start_time','business_end_time','weekdays','status']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneStatus()
    {
        return $this->only(['id','status']);
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneRegionSearch()
    {
        return $this->only(['keyword','boundary','key']);
    }

    /**
     * @notes 检查门店名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 2:46 下午
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        //编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $result = SelffetchShop::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '门店名称已存在';
        }
        return true;
    }

    /**
     * @notes 检查门店ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 3:25 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = SelffetchShop::where('id', $value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '自提门店不存在';
        }
        return true;
    }

    /**
     * @notes 检查门店是否可以删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 5:06 下午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = SelffetchVerifier::where('selffetch_shop_id', $value)->select()->toArray();
        if (!empty($result)) {
            return '门店正在使用中，无法删除';
        }
        return true;
    }
}