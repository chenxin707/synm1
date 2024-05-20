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

namespace app\adminapi\validate\kefu;

use app\common\model\Kefu;
use app\common\validate\BaseValidate;

/**
 * 客服验证器
 * Class KefuValidate
 * @package app\adminapi\validate\kefu
 */
class KefuValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'admin_id' => 'require|number|checkIsKefu',
        'avatar' => 'require',
        'nickname' => 'require|unique:' . Kefu::class . ',nickname',
        'disable' => 'require|in:0,1',
        'sort' => 'gt:0|integer'
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
        'admin_id.require' => '请选择管理员',
        'admin_id.number' => '管理员选择异常',
        'avatar.require' => '请选择头像',
        'nickname.require' => '请填写客服昵称',
        'nickname.unique' => '该客服昵称已存在',
        'disable.require' => '请选择客服状态',
        'disable.in' => '状态错误',
        'sort.gt' => '排序需大于0',
        'sort.integer'  => '排序值只能是整数',

    ];


    /**
     * @notes 添加客服场景
     * @return KefuValidate
     * @author 段誉
     * @date 2022/3/8 18:31
     */
    public function sceneAdd()
    {
        return $this->remove('id', true);
    }


    /**
     * @notes 编辑场景
     * @return KefuValidate
     * @author 段誉
     * @date 2022/3/8 18:31
     */
    public function sceneEdit()
    {
        return $this->remove('admin_id', true);
    }


    /**
     * @notes 删除场景
     * @return KefuValidate
     * @author 段誉
     * @date 2022/3/8 18:31
     */
    public function sceneDel()
    {
        return $this->only(['id'])->append('id', 'checkIsDel');
    }


    /**
     * @notes 客服详情场景
     * @return KefuValidate
     * @author 段誉
     * @date 2022/3/8 18:55
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 客服状态场景
     * @return KefuValidate
     * @author 段誉
     * @date 2022/3/8 18:32
     */
    public function sceneStatus()
    {
        return $this->only(['id', 'disable']);
    }


    /**
     * @notes 校验管理员是否已为客服
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/8 17:32
     */
    protected function checkIsKefu($value, $rule, $data = [])
    {
        $check = Kefu::where(['admin_id' => $value])->findOrEmpty();

        if (!$check->isEmpty()) {
            return "该管理员已是客服";
        }

        return true;
    }


    /**
     * @notes 校验客服是否存在
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/8 17:31
     */
    protected function checkIsDel($value, $rule, $data = [])
    {
        $check = Kefu::where(['id' => $value])->findOrEmpty();

        if ($check->isEmpty()) {
            return "客服信息不存在";
        }

        return true;
    }
}