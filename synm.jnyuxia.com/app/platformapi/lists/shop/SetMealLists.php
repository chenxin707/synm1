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

namespace app\platformapi\lists\shop;


use app\common\lists\ListsExcelInterface;
use app\common\model\SetMeal;
use app\platformapi\lists\BasePlatformDataLists;

class SetMealLists extends BasePlatformDataLists implements ListsExcelInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2022/3/3 5:30 下午
     */
    public function where(): array
    {
        $where = [];
        if (isset($this->params['name']) && $this->params['name'] != '') {
            $where[] = ['name','like','%'.$this->params['name'].'%'];
        }
        if (isset($this->params['status']) && $this->params['status'] != '') {
            $where[] = ['status','=',$this->params['status']];
        }
        if (isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $where[] = ['create_time', '>=', strtotime($this->params['start_time'])];
            $where[] = ['create_time', '<=', strtotime($this->params['end_time'])];
        }
        return $where;
    }

    /**
     * @notes 套餐列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/3 6:14 下午
     */
    public function lists(): array
    {
        $lists = SetMeal::withoutGlobalScope()
            ->field('id,name,explain,status,sort,create_time')
            ->where($this->where())
            ->append(['price_desc','shop_num','status_desc'])
            ->with(['set_meal_price'])
            ->order(['id'=>'desc'])
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 套餐数量
     * @return int
     * @author ljj
     * @date 2022/3/3 6:15 下午
     */
    public function count(): int
    {
        return SetMeal::withoutGlobalScope()->where($this->where())->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2022/3/4 10:41 上午
     */
    public function setExcelFields(): array
    {
        return [
            // 特别注意：数值类型的字段不在排在第2位
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'name' => '套餐名称',
            'explain' => '套餐说明',
            'price_desc' => '套餐价格',
            'shop_num' => '商城数量',
            'status_desc' => '套餐状态',
            'sort' => '排序',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2022/3/4 10:41 上午
     */
    public function setFileName(): string
    {
        return '套餐列表';
    }
}
