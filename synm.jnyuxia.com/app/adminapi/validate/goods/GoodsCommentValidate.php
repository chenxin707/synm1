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

namespace app\adminapi\validate\goods;


use app\common\model\GoodsComment;
use app\common\validate\BaseValidate;

class GoodsCommentValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|array|checkId',
        'status' => 'require|in:0,1,2',
    ];

    public function sceneReply()
    {
        return $this->only(['id']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    public function sceneStatus()
    {
        return $this->only(['id','status'])
            ->append('id','checkStatus');
    }

    /**
     * @notes 检查商品评价ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/12 5:20 下午
     */
    public function checkId($value,$rule,$data)
    {
        foreach ($value as $val) {
            $result = GoodsComment::findOrEmpty($val);
            if ($result->isEmpty()) {
                return '存在非法ID';
            }
        }

        return true;
    }

    /**
     * @notes 检验评价审核状态
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/9/9 2:44 下午
     */
    public function checkStatus($value,$rule,$data)
    {
        foreach ($value as $val) {
            $result = GoodsComment::findOrEmpty($val);
            if ($result['status'] > 0) {
                return '存在已审核评价，请重新提交';
            }
        }
        return true;
    }
}