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

namespace app\adminapi\validate\settings\delivery;


use app\common\model\Freight;
use app\common\validate\BaseValidate;

class FreightValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'charge_way' => 'require|in:1,2,3',
        'region' => 'require|array|checkRegion',
    ];

    protected $message = [
        'name.require' => '模版名称不能为空',
        'charge.require' => '计费方式不能为空',
        'charge.in' => '计费方式的取值范围在[1,2,3]',
        'region.require' => '配送区域不能为空',
        'region.array' => '配送区域必须为数组',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','charge_way','region']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','charge_way','region']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检查模版名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/30 2:18 下午
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        //编辑
        if (isset($data['id'])) {
            $where[] = ['id','<>',$data['id']];
        }

        $result = Freight::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '模版名称已存在';
        }
        return true;
    }

    /**
     * @notes 检查配送区域数据是否完整
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/30 2:30 下午
     */
    public function checkRegion($value,$rule,$data)
    {
        foreach ($value as $val) {
            if ($val['region_id'] == '' || $val['first_unit'] == '' || $val['first_money'] == '' || $val['continue_unit'] == '' || $val['continue_money'] == '') {
                return '参数缺失';
            }
            if ($val['first_unit'] < 0 || $val['first_money'] < 0 || $val['continue_unit'] < 0 || $val['continue_money'] < 0) {
                return '配送区域不能存在小于零的数据';
            }
        }

        return true;
    }

    /**
     * @notes 检查ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/30 5:26 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Freight::where('id',$value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '运费模版不存在';
        }
        return true;
    }
}