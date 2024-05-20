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

namespace app\common\model;


use app\common\enum\YesNoEnum;
use think\model\concern\SoftDelete;

class SetMeal extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    // 关闭全局查询范围
    protected $globalScope = [];

    /**
     * @notes 关联套餐价格模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2022/3/3 5:34 下午
     */
    public function setMealPrice()
    {
        return $this->hasMany(SetMealPrice::class,'set_meal_id','id')->field('id,set_meal_id,time,time_type,price');
    }


    /**
     * @notes 套餐价格
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2022/3/3 5:59 下午
     */
    public function getPriceDescAttr($value,$data)
    {
        $price_arr = SetMealPrice::where('set_meal_id',$data['id'])->column('price');
        $min_price = min($price_arr);
        $max_price = max($price_arr);
        if($min_price == $max_price){
            return $min_price;
        }
        return min($price_arr).'-'.max($price_arr);
    }

    /**
     * @notes 商城数量
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2022/3/4 10:06 上午
     */
    public function getShopNumAttr($value,$data)
    {
        return PlatformShop::where(['set_meal_id'=>$data['id']])->count();
    }

    /**
     * @notes 套餐状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/4 10:40 上午
     */
    public function getStatusDescAttr($value,$data)
    {
        return YesNoEnum::getIsOpenDesc($data['status']);
    }

    /**
     * @notes 套餐关联应用
     */
    public function getFuncAttr($value) {
        return json_decode($value);
    }
}
