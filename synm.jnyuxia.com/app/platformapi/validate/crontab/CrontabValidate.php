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

namespace app\platformapi\validate\crontab;

use app\common\validate\BaseValidate;
use Cron\CronExpression;

/**
 * 定时任务验证器
 */
class CrontabValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
        'type' => 'require|in:1',
        'command' => 'require',
        'status' => 'require|in:1,2,3',
        'expression' => 'require|checkExpression',
        'id' => 'require',
    ];

    protected $message = [
        'name.require' => '请输入定时任务名称',
        'type.require' => '请选择类型',
        'type.in' => '类型值错误',
        'command.require' => '请输入命令',
        'status.require' => '请选择状态',
        'status.in' => '状态值错误',
        'expression.require' => '请输入运行规则',
        'id.require' => '参数缺失',
    ];

    /**
     * @notes 添加定时任务场景
     * @return CrontabValidate
     * @author Tab
     * @date 2021/12/23 10:55
     */
    public function sceneAdd()
    {
        return $this->remove('id', 'require');
    }

    /**
     * @notes 查看定时任务详情场景
     * @return CrontabValidate
     * @author Tab
     * @date 2021/12/23 10:55
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 删除定时任务场景
     * @return CrontabValidate
     * @author Tab
     * @date 2021/12/23 10:56
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 获取规则执行时间场景
     * @return CrontabValidate
     * @author Tab
     * @date 2021/12/23 10:56
     */
    public function sceneExpression()
    {
        return $this->only(['expression']);
    }

    /**
     * @notes 校验运行规则
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/12/23 10:56
     */
    public function checkExpression($value, $rule, $data)
    {
        if(CronExpression::isValidExpression($value) === false) {
            return '定时任务运行规则错误';
        }
        return true;
    }
}