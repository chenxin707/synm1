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


use app\common\model\Express;
use app\common\validate\BaseValidate;

class ExpressValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'sort' => 'number|max:5',
    ];

    protected $message = [
        'name.require' => '快递公司名称不能为空',
        'sort.number' => '排序只能是纯数字',
        'sort.max' => '排序最大不能超过五位数',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','sort']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','sort']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检查快递公司名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/7/29 4:29 下午
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        // 编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }

        $result = Express::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '快递公司名称已存在';
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
     * @date 2021/7/29 5:17 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Express::where('id',$value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '快递公司不存在';
        }
        return true;
    }
}