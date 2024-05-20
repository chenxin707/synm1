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

namespace app\adminapi\validate\express_assistant;

use app\common\model\FaceSheetSender;
use app\common\model\Region;
use app\common\validate\BaseValidate;

/**
 * 发件人验证器
 */
class FaceSheetSenderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkSender',
        'name' => 'require|checkName',
        'mobile' => 'require|mobile',
        'province_id' => 'require|verifyLegitimacy',
        'city_id' => 'require|verifyLegitimacy',
        'district_id' => 'require|verifyLegitimacy',
        'address' => 'require',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'name.require' => '请输入发件人名称',
        'mobile.require' => '请输入发件人手机',
        'mobile.mobile' => '手机号码格式不正确',
        'province_id.require' => '请选择发件省份',
        'city_id.require' => '请选择发件城市',
        'district_id.require' => '请选择发件地区',
        'address.require' => '请输入详细地址',
    ];

    public function sceneAdd()
    {
        return $this->only(['name', 'mobile', 'province_id', 'city_id', 'district_id', 'address']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'mobile', 'province_id', 'city_id', 'district_id', 'address']);
    }

    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验发件人名称
     * @param $name
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/11/22 15:56
     */
    public function checkName($name, $rule, $data)
    {
        $where = [['name', '=', $name]];
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $template = FaceSheetSender::where($where)->findOrEmpty();
        if ($template->isEmpty()) {
            return true;
        }
        return '该发件人名称已被使用，请重新输入';
    }

    /**
     * @notes 校验地区编码合法性
     * @param $value
     * @author Tab
     * @date 2021/11/22 16:03
     */
    public function verifyLegitimacy($value)
    {
        $region = Region::findOrEmpty($value);
        if ($region->isEmpty()) {
            return '地区编码有误';
        }
        return true;
    }

    /**
     * @notes 校验发件人模板
     * @param $senderId
     * @author Tab
     * @date 2021/11/22 16:09
     */
    public function checkSender($senderId)
    {
        $sender = FaceSheetSender::findOrEmpty($senderId);
        if ($sender->isEmpty()) {
            return '发件人模板不存在';
        }
        return true;
    }
}