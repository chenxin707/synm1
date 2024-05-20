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

namespace app\adminapi\logic\settings\delivery;


use app\common\logic\BaseLogic;
use app\common\model\Freight;
use app\common\model\FreightConfig;
use app\common\model\Region;
use think\facade\Db;

class FreightLogic extends BaseLogic
{
    /**
     * @notes 添加运费模版
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2021/7/30 2:46 下午
     */
    public function add($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //模版数据入库
            $freight = new Freight;
            $freight->name = $params['name'];
            $freight->charge_way = $params['charge_way'];
            $freight->remark = $params['remark'];
            $freight->duokaiSave();

            //模版配置数据入库
            $freight_config = new FreightConfig;
            $data = [];
            foreach ($params['region'] as $val) {
                $data[] = [
                    'freight_id' => $freight->id,
                    'region_id' => $val['region_id'],
                    'first_unit' => $val['first_unit'],
                    'first_money' => $val['first_money'],
                    'continue_unit' => $val['continue_unit'],
                    'continue_money' => $val['continue_money'],
                ];
            }
            $freight_config->duokaiSaveAll($data);

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
     * @notes 编辑运费模版
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2021/7/30 6:06 下午
     */
    public function edit($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新运费模版
            $freight = Freight::find($params['id']);
            $freight->name = $params['name'];
            $freight->charge_way = $params['charge_way'];
            $freight->remark = $params['remark'];
            $freight->duokaiSave();

            //删除旧的运费模版配置
            FreightConfig::duokaiDestroy(function($query) use($params){
                $query->where('freight_id',$params['id']);
            });

            //添加新的运费模版配置
            $data = [];
            foreach ($params['region'] as $val) {
                $data[] = [
                    'freight_id' => $freight->id,
                    'region_id' => $val['region_id'],
                    'first_unit' => $val['first_unit'],
                    'first_money' => $val['first_money'],
                    'continue_unit' => $val['continue_unit'],
                    'continue_money' => $val['continue_money'],
                ];
            }
            $freight_config = new FreightConfig;
            $freight_config->duokaiSaveAll($data);

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
     * @notes 查看运费模版详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/7/30 6:52 下午
     */
    public function detail($params)
    {
        $freight = Freight::field('id,name,charge_way,remark')->find($params['id']);
        $freight->region->toArray();
        $freight = $freight->toArray();
        foreach ($freight['region'] as &$val) {
            if ($val['region_id'] == 100000) {
                $val['region_name'] = '全国统一运费';
            }else {
                $val['region_name'] = implode('、',Region::where('id','in', $val['region_id'])->column('name'));
            }
        }

        return $freight;
    }

    /**
     * @notes 删除运费模版
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/7/30 7:00 下午
     */
    public function del($params)
    {
        return Freight::duokaiDestroy($params['id']);
    }
}