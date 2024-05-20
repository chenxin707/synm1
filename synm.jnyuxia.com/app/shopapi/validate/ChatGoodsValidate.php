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

namespace app\shopapi\validate;


use app\common\enum\ChatGoodsEnum;
use app\common\validate\BaseValidate;

/**
 * 聊天-商品验证
 * Class ChatGoodsValidate
 * @package app\shopapi\validate
 */
class ChatGoodsValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|integer',
        'type' => 'require',
        'activity_id' => 'integer',
    ];


    protected $message = [
        'goods_id.require' => '商品id参数缺失',
        'goods_id.integer' => '商品id类型错误',
        'type.require' => '商品类型参数缺失',
        'activity_id.integer' => '商品活动参数类型错误',
    ];

    // 验证类型
    protected function checkType($value, $rule, $data = [])
    {
        if (in_array($value,ChatGoodsEnum::GOODS_TYPE)) {
            return '商品类型错误';
        }

        if ($value != ChatGoodsEnum::GOODS_NORMAL && empty($data['activity_id'])) {
            return '活动参数缺失';
        }

        return true;
    }


}