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

use app\common\enum\LuckyDrawEnum;

class LuckyDrawPrize extends BaseModel
{
    /**
     * @notes 获取奖品类型描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author Tab
     * @date 2021/11/24 14:15
     */
    public function getTypeDescAttr($value, $data)
    {
        return LuckyDrawEnum::getPrizeTypeDesc($data['type']);
    }

    /**
     * @notes 还原入库前的概率
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/11/24 14:17
     */
    public function getProbabilityDescAttr($value, $data)
    {
        return (int)$data['probability'] / 100;
    }

    /**
     * @notes 奖品类型对应的值
     * @param $value
     * @param $data
     * @return float|int
     * @author ljj
     * @date 2023/5/5 6:01 下午
     */
    public function getTypeValueAttr($value, $data)
    {
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 1:
                case 3:
                    $value = round(floatval($value),2);
                break;
                case 2:
                    $value = intval($value);
                    break;
            }
        }

        return $value;
    }
}