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

/**
 * 微信公众号自动回复表
 * Class OfficialAccountReply
 * @package app\common\model
 */
class OfficialAccountReply extends BaseModel
{
    /**
     * @notes 回复类型获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/29 17:12
     */
    public function getReplyTypeDescAttr($value)
    {
        $desc = [
            1 => '关注回复',
            2 => '关键词回复',
            3 => '默认回复',
        ];
        return $desc[$value] ?? '';
    }

    /**
     * @notes 匹配类型获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/29 17:16
     */
    public function getMatchingTypeDescAttr($value)
    {
        $desc = [
            1 => '全匹配',
            2 => '模糊匹配',
        ];
        return $desc[$value] ?? '';
    }

    /**
     * @notes 内容类型获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/29 17:18
     */
    public function getContentTypeDescAttr($value)
    {
        $desc = [
            1 => '文本',
        ];
        return $desc[$value] ?? '';
    }

    /**
     * @notes 状态获取器
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/7/29 17:19
     */
    public function getStatusDescAttr($value)
    {
        $desc = ['禁用', '开启'];
        return $desc[$value] ?? '';
    }
}