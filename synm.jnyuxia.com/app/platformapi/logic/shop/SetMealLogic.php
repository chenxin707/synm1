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

namespace app\platformapi\logic\shop;


use app\common\logic\BaseLogic;
use app\common\model\SetMeal;
use app\common\model\SetMealPrice;
use think\facade\Config;
use think\facade\Db;

class SetMealLogic extends BaseLogic
{
    /**
     * @notes 添加套餐
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/3/4 9:35 上午
     */
    public function add($params)
    {
        Db::startTrans();
        try {
            //添加套餐信息
            $setMeal = SetMeal::create([
                'name' => $params['name'],
                'explain' => $params['explain'],
                'status' => $params['status'],
                'sort'   => $params['sort'],
                'func' => json_encode($params['func'])
            ]);

            //添加套餐价格信息
            foreach ($params['set_meal_price'] as $val) {
                SetMealPrice::create([
                    'set_meal_id' => $setMeal->id,
                    'time' => $val['time'],
                    'time_type' => $val['time_type'],
                    'price' => $val['price'],
                ]);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 套餐详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/3/4 10:53 上午
     */
    public function detail($id)
    {
        $result = SetMeal::where('id',$id)
            ->field('id,name,explain,status,sort,func')
            ->with(['set_meal_price'])
            ->findOrEmpty()
            ->toArray();

        return $result;
    }

    /**
     * @notes 编辑套餐
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/3/4 10:55 上午
     */
    public function edit($params)
    {
        Db::startTrans();
        try {
            //修改套餐信息
            SetMeal::update([
                'name' => $params['name'],
                'explain' => $params['explain'],
                'status' => $params['status'],
                'sort'   => $params['sort'],
                'func'   => json_encode($params['func']),
            ],['id'=>$params['id']]);


            //删除旧的套餐价格
            SetMealPrice::where('set_meal_id',$params['id'])->delete();
            //添加新的套餐价格
            foreach ($params['set_meal_price'] as $val) {
                SetMealPrice::create([
                    'set_meal_id' => $params['id'],
                    'time' => $val['time'],
                    'time_type' => $val['time_type'],
                    'price' => $val['price'],
                ]);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除套餐
     * @param $id
     * @return bool
     * @author ljj
     * @date 2022/3/4 3:24 下午
     */
    public function del($id)
    {
        return SetMeal::destroy($id);
    }

    /**
     * @notes 更新套餐状态
     * @param $params
     * @return SetMeal
     * @author ljj
     * @date 2022/3/4 3:35 下午
     */
    public function status($params)
    {
        return SetMeal::update(['status'=>$params['status']],['id'=>$params['id']]);
    }

    /**
     * @notes 获取套餐可关联的营销应用
     */
    public static function getMealModule() {
        $configModule = Config::get('modules');
        $data = [];
        foreach($configModule as $key => $item) {
            foreach($item as $subItem) {
                if (!isset($data[$key])) {
                    $data[$key] = [];
                }
                $data[$key] = array_merge($data[$key], $subItem['list']);
            }
        }
        return $data;
    }
}
