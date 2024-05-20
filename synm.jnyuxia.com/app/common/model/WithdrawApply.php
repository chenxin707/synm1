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

namespace app\common\model;

use app\common\enum\WithdrawEnum;
use app\common\service\FileService;

/**
 * 提现模型
 * Class WithdrawApply
 * @package app\common\model
 */
class WithdrawApply extends BaseModel
{
    /**
     * @notes 类型获取器
     * @param $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/6 18:48
     */
    public function getTypeDescAttr($value)
    {
        return WithdrawEnum::getTypeDesc($value);
    }

    /**
     * @notes 状态获取器
     * @param $value
     * @return string|string[]
     * @author Tab
     * @date 2021/8/6 18:51
     */
    public function getStatusDescAttr($value)
    {
        return WithdrawEnum::getStatusDesc($value);
    }
    /**
     * @notes 转账凭证
     * @param $value
     * @return string|string[]
     * @author cjhao
     * @date 2022/3/17 10:57
     */
    public function getTransferVoucherAttr($value){
        if($value){
            return FileService::getFileUrl($value);
        }
        return $value;
    }

    /**
     * @notes 转账时间
     * @param $value
     * @return false|string
     * @author cjhao
     * @date 2022/3/17 11:26
     */
    public function getTransferTimeAttr($value){
        if($value){
            return date('Y-m-d H:i:s',$value);
        }
        return $value;
    }
}