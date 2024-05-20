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

namespace app\platformapi\lists\settings;

use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\OperationLog;
use app\platformapi\lists\BasePlatformDataLists;

class LogLists extends BasePlatformDataLists implements ListsSearchInterface,ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author ljj
     * @date 2021/8/3 4:21 下午
     */
    public function setSearch(): array
    {
        return [
            '%like%' => ['admin_name','url','ip','type'],
            'between_time' => 'create_time',
        ];
    }

    /**
     * @notes 查看系统日志列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/3 4:21 下午
     */
    public function lists(): array
    {
        $lists = OperationLog::field('id,action,admin_name,admin_id,url,type,params,ip,create_time')
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id','desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 查看系统日志总数
     * @return int
     * @author ljj
     * @date 2021/8/3 4:23 下午
     */
    public function count(): int
    {
        return OperationLog::where($this->searchWhere)->count();
    }

    /**
     * @notes 设置导出字段
     * @return string[]
     * @author ljj
     * @date 2021/8/3 4:48 下午
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'id' => '记录ID',
            'admin_name' => '管理员',
            'admin_id' => '管理员ID',
            'url' => '访问链接',
            'type' => '访问方式',
            'params' => '访问参数',
            'ip' => '来源IP',
            'create_time' => '日志时间',
        ];
    }

    /**
     * @notes 设置默认表名
     * @return string
     * @author ljj
     * @date 2021/8/3 4:48 下午
     */
    public function setFileName(): string
    {
        return '系统日志';
    }
}