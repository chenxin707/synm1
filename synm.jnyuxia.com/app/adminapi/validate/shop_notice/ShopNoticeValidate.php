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

namespace app\adminapi\validate\shop_notice;


use app\common\model\ShopNotice;
use app\common\validate\BaseValidate;

class ShopNoticeValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'content' => 'require',
        'sort' => 'number|max:5',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'name.require' => '公告标题不能为空',
        'content.require' => '公告内容不能为空',
        'sort.number' => '排序必须为纯数字',
        'sort.max' => '排序最大不能超过五位数',
        'status.require' => '公告状态不能为空',
        'status.in' => '公告状态取值范围[0,1]',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','content','sort','status']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','content','sort','status']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneStatus()
    {
        return $this->only(['id','status'])
            ->append('status','require');
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检查公告标题是否已存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/23 12:00 下午
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name', '=', $value];
        // 编辑的情况，要排除自身ID
        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $result = ShopNotice::where($where)->select()->toArray();
        if ($result) {
            return '公告标题已存在';
        }
        return true;
    }

    /**
     * @notes 检查商城公告ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/23 2:40 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = ShopNotice::findOrEmpty($value);
        if ($result->isEmpty()) {
            return '商城公告不存在';
        }
        return true;
    }
}