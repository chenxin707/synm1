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

namespace app\platformapi\validate\shop;


use app\common\model\PlatformShop;
use app\common\model\SetMeal;
use app\common\validate\BaseValidate;
use think\facade\Validate;

class SetMealValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require',
        'explain' => 'require',
        'status' => 'require|in:0,1',
        'set_meal_price' => 'require|array|checkPrice',
        'func' => 'require|array|checkFunc',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'name.require' => '请输入套餐名称',
        'explain.require' => '请输入套餐说明',
        'status.require' => '请选择套餐状态',
        'status.in' => '套餐状态值错误',
        'set_meal_price.require' => '请输入套餐价格',
        'set_meal_price.array' => '套餐价格格式不正确',
        'func.require' => '请关联营销应用',
        'func.array' => '营销应用格式不正确',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','explain','status','set_meal_price', 'func']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','explain','status','set_meal_price', 'func']);
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneStatus()
    {
        return $this->only(['id','status']);
    }

    /**
     * @notes 检验套餐价格
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/3/3 6:45 下午
     */
    public function checkPrice($value,$rule,$data)
    {
        $num = count($value);
        if ($num > 3) {
            return '套餐价格最多支持三个规格';
        }
        foreach ($value as $key=>$val) {
            $key += 1;
            if (in_array($val['time_type'],[1,2]) && (!isset($val['time']) || $val['time'] == '')) {
                return '套餐价格第'.$key.'个规格的时长不能为空';
            }
            if (in_array($val['time_type'],[1,2]) && !Validate::isNumber($val['time'])) {
                return '套餐价格第'.$key.'个规格的时长错误';
            }
            if (!isset($val['time_type']) || $val['time_type'] == '') {
                return '套餐价格第'.$key.'个规格的时长类型不能为空';
            }
            if (!in_array($val['time_type'],[1,2,3])) {
                return '套餐价格第'.$key.'个规格的套餐价格时长类型错误';
            }
            if (!isset($val['price']) || $val['price'] == '') {
                return '套餐价格第'.$key.'个规格的价格不能为空';
            }
            if (!Validate::isFloat($val['price'])) {
                return '套餐价格第'.$key.'个规格的价格错误';
            }
        }

        return true;
    }

    /**
     * @notes 检验套餐能否删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/4 3:27 下午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = SetMeal::where('id',$value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '套餐不存在';
        }
        $shop = PlatformShop::where('set_meal_id',$value)->select()->toArray();
        if ($shop) {
            return '套餐正在使用中，无法删除';
        }
        return true;
    }

    public function checkFunc($value,$rule,$data) {
        $requireFunc = ['sms', 'service', 'express', 'print'];
        $intersect = array_intersect($value, $requireFunc);
        if (count($intersect) != count($requireFunc)) {
            return '请选择营销应用必选项';
        }
        return true;
    }
}
