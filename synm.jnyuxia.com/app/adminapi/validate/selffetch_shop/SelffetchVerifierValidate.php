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

namespace app\adminapi\validate\selffetch_shop;


use app\common\model\SelffetchShop;
use app\common\model\SelffetchVerifier;
use app\common\model\User;
use app\common\validate\BaseValidate;

/**
 * 核销员验证器
 * Class SelffetchVerifierValidate
 * @package app\adminapi\validate\selffetch_shop
 */
class SelffetchVerifierValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'user_id' => 'require|checkUserId',
        'selffetch_shop_id' => 'require|checkSelffetchShopId',
        'mobile' => 'require',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'name.require' => '核销员名称不能为空',
        'user_id.require' => '用户不能为空',
        'selffetch_shop_id.require' => '自提门店不能为空',
        'mobile.require' => '联系电话不能为空',
        'mobile.mobile' => '不是有效的手机号码',
        'status.require' => '核销员状态不能为空',
        'status.in' => '核销员状态取值范围[0,1]',
    ];

    public function sceneAdd()
    {
        return $this->only(['selffetch_shop_id','user_id','name','mobile','status'])
            ->append('user_id','checkUser');
    }

    public function sceneEdit()
    {
        return $this->only(['id','selffetch_shop_id','user_id','name','mobile','status']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneStatus()
    {
        return $this->only(['id','status']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检查自提门店是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 7:09 下午
     */
    public function checkSelffetchShopId($value,$rule,$data)
    {
        $result = SelffetchShop::where('id', $value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '自提门店不存在';
        }
        return true;
    }

    /**
     * @notes 检查用户是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 7:11 下午
     */
    public function checkUserId($value,$rule,$data)
    {
        $result = User::where('id', $value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '用户不存在';
        }

        return true;
    }

    /**
     * @notes 检验同一门店是否已存在相同用户
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/9/26 6:56 下午
     */
    public function checkUser($value,$rule,$data)
    {
        $where[] = ['user_id', '=', $value];
        $where[] = ['selffetch_shop_id', '=', $data['selffetch_shop_id']];
        //编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }

        $result = SelffetchVerifier::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '同一门店不可存在多个相同用户';
        }

        return true;
    }

    /**
     * @notes 检查核销员名称是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 7:10 下午
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        $where[] = ['selffetch_shop_id', '=', $data['selffetch_shop_id']];
        //编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }

        $result = SelffetchVerifier::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '核销员名称已存在';
        }

        return true;
    }

    /**
     * @notes 检查核销员ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/11 7:30 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = SelffetchVerifier::where('id', $value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '核销员不存在';
        }
        return true;
    }
}