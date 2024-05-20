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


use app\common\enum\FreightEnum;

class Freight extends BaseModel
{

    /**
     * @notes 计费方式名称获取器
     * @param $value
     * @return string
     * @author ljj
     * @date 2021/7/30 5:00 下午
     */
    public function getChargeWayNameAttr($value,$data)
    {
        return FreightEnum::getChargeWay($data['charge_way']);
    }

    /**
     * @notes 一对多关联FreightConfig模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2021/7/30 6:51 下午
     */
    public function region()
    {
        return $this->hasMany(FreightConfig::class,'freight_id','id')
            ->field('id,region_id,first_unit,first_money,continue_unit,continue_money');
    }
}