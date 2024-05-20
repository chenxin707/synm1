<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam-段誉
// +----------------------------------------------------------------------


namespace app\shopapi\logic;


use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Region;
use app\common\model\UserAddress;


/**
 * 用户地址逻辑
 * Class UserAddressLogic
 * @package app\shopapi\logic
 */
class UserAddressLogic extends BaseLogic
{

    /**
     * @notes 列表
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/7/22 14:13
     */
    public static function getLists($userId)
    {
        return UserAddress::where(['user_id' => $userId])->select()->toArray();
    }



    /**
     * @notes 详情
     * @param $addressId
     * @param $userId
     * @return array
     * @author 段誉
     * @date 2021/7/22 17:49
     */
    public static function getDetail($addressId, $userId)
    {
        return UserAddress::getAddressById($userId, $addressId)->toArray();
    }


    /**
     * @notes 获取默认地址
     * @param $userId
     * @return array
     * @author 段誉
     * @date 2021/7/22 14:30
     */
    public static function getDefault($userId)
    {
        return UserAddress::getDefaultAddress($userId)->toArray();
    }


    /**
     * @notes 设置默认地址
     * @param $params
     * @param $userId
     * @author 段誉
     * @date 2021/7/22 14:30
     */
    public static function setDefault($params, $userId)
    {
        UserAddress::where(['user_id' => $userId])->update(['is_default' => YesNoEnum::NO]);
        UserAddress::where(['id' => $params['id'], 'user_id' => $userId])->update(['is_default' => YesNoEnum::YES]);
    }

    /**
     * @notes 添加地址
     * @param $params
     * @param $userId
     * @author 段誉
     * @date 2021/7/22 14:14
     */
    public static function addAddress($params, $userId)
    {
        if ($params['is_default'] == YesNoEnum::YES) {
            UserAddress::where(['user_id' => $userId])->update(['is_default' => YesNoEnum::NO]);
        } else {
            $isFirst = UserAddress::where(['user_id' => $userId])->findOrEmpty();
            if ($isFirst->isEmpty()) {
                $params['is_default'] = YesNoEnum::YES;
            }
        }

        UserAddress::duokaiCreate([
            'user_id'       => $userId,
            'contact'       => $params['contact'],
            'mobile'        => $params['mobile'],
            'province_id'   => $params['province_id'],
            'city_id'       => $params['city_id'],
            'district_id'   => $params['district_id'],
            'address'       => $params['address'],
            'is_default'    => $params['is_default'] ?? YesNoEnum::NO,
            'create_time'   => time()
        ]);
    }


    /**
     * @notes 编辑地址
     * @param $params
     * @author 段誉
     * @date 2021/7/22 14:14
     */
    public static function editAddress($params)
    {
        if ($params['is_default'] == YesNoEnum::YES) {
            UserAddress::where(['user_id' => $params['user_id']])->update(['is_default' => YesNoEnum::NO]);
        }

        UserAddress::duokaiUpdate([
            'contact'       => $params['contact'],
            'mobile'        => $params['mobile'],
            'province_id'   => $params['province_id'],
            'city_id'       => $params['city_id'],
            'district_id'   => $params['district_id'],
            'address'       => $params['address'],
            'is_default'    => $params['is_default'],
        ], ['id' => $params['id'], 'user_id' => $params['user_id']]);
    }


    /**
     * @notes  删除
     * @param $params
     * @author 段誉
     * @date 2021/7/22 14:33
     */
    public static function del($params)
    {
        UserAddress::duokaiDestroy(['user_id' => $params['user_id'], 'id' => $params['id']]);
    }

    /**
     * @notes 将省市区名称转为id
     * @param $params
     * @return string[]
     * @author Tab
     * @date 2021/8/12 16:20
     */
    public static function handleRegion($params)
    {
        $province = self::handleRegionField($params['province'], 1);
        $city = self::handleRegionField($params['city'], 2,$province);
        $district = self::handleRegionField($params['district'], 3,$city);
        $result = [
            'province' => $province,
            'city' => $city,
            'district' => $district,
        ];
        return $result;
    }

    /**
     * @notes 根据地址名称获取id
     * @param $field
     * @param $level
     * @return mixed|string
     * @author Tab
     * @date 2021/8/12 16:20
     */
    public static function handleRegionField($field, $level,$partnerId = 100000)
    {
        $region = Region::where([
            ['level', '=', $level],
            ['name', 'like', '%' . $field . '%'],
            ['parent_id', '=', $partnerId]
        ])->findOrEmpty();
        if($region->isEmpty()) {
            return '';
        }
        return $region->id;
    }
}