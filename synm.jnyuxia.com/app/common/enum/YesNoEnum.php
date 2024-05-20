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


namespace app\common\enum;

/**
 * 通过枚举类，枚举只有两个值的时候使用
 * Class YesNoEnum
 * @package app\common\enum
 */
class YesNoEnum
{
    const YES = 1;
    const NO = 0;

    /**
     * @notes 获取禁用状态
     * @param bool $value
     * @return string|string[]
     * @author 令狐冲
     * @date 2021/7/8 19:02
     */
    public static function getDisableDesc($value = true)
    {
        $data = [
            self::YES => '禁用',
            self::NO => '正常'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 获取显示隐藏状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/7/31 6:05 下午
     */
    public static function getIsShowDesc($value = true)
    {
        $data = [
            self::YES => '显示',
            self::NO => '隐藏'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }

    /**
     * @notes 获取启用停用状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2021/8/11 8:01 下午
     */
    public static function getIsOpenDesc($value = true)
    {
        $data = [
            self::YES => '启用',
            self::NO => '停用'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }


    /**
     * @notes 采集状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2023/3/14 2:54 下午
     */
    public static function getGatherStatusDesc($value = true)
    {
        $data = [
            self::YES => '成功',
            self::NO => '失败'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }
}