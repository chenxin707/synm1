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

use app\common\service\RegionService;

/**
 * 用户地址模型
 * Class UserLevel
 * @package app\common\model
 */
class UserAddress extends BaseModel
{
    protected $append = ['province', 'city', 'district'];


    public function getProvinceAttr($value, $data)
    {
        return RegionService::getAddress($data['province_id']);
    }


    public function getCityAttr($value, $data)
    {
        return RegionService::getAddress($data['city_id']);
    }

    public function getDistrictAttr($value, $data)
    {
        return RegionService::getAddress($data['district_id']);
    }

    /**
     * @notes 获取默认收货地址
     * @param $user_id
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/8/3 16:39
     */
    public static function getDefaultAddress($user_id)
    {
        $model = new self;
        $result = $model->where(['user_id' => $user_id])
            ->where('is_default', 1)
            ->order('is_default desc')
            ->find();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @notes 根据ID获取地址
     * @param $user_id
     * @param $id
     * @return array
     * @throws @\think\db\exception\DataNotFoundException
     * @throws @\think\db\exception\DbException
     * @throws @\think\db\exception\ModelNotFoundException
     * @author 张无忌
     * @date 2021/8/3 16:47
     */
    public static function getAddressById($user_id, $id)
    {
        $model = new self;
        $result = $model->where(['user_id'=>$user_id])
            ->where('id', '=', intval($id))
            ->find();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @notes 获取一条收货地址
     * @param $user_id
     * @param $id
     * @return array
     * @author 张无忌
     * @date 2021/8/3 16:42
     */
    public static function getOneAddress($user_id, $id=0)
    {
        if ($id > 0) {
            return self::getAddressById($user_id, $id);
        }

        return self::getDefaultAddress($user_id);
    }
}