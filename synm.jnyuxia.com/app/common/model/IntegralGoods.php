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


use app\common\enum\IntegralGoodsEnum;
use app\common\service\FileService;
use think\model\concern\SoftDelete;

/**
 * 积分商品模型
 * Class IntegralGoods
 * @package app\common\model
 */
class IntegralGoods extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';

    // 设置内容图片域名
    public function getContentAttr($value, $data)
    {
        $preg = '/(<img .*?src=")[^https|^http](.*?)(".*?>)/is';
        $fileUrl = FileService::getFileUrl();
        return preg_replace($preg, "\${1}$fileUrl\${2}\${3}", $value);
    }

    // 去除内容图片域名
    public function setContentAttr($value, $data)
    {
        $fileUrl = FileService::getFileUrl();
        $content = str_replace($fileUrl, '/', $value);
        return $content;
    }


    /**
     * @notes 商品类型描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/30 11:59
     */
    public function getTypeDescAttr($value, $data)
    {
        return IntegralGoodsEnum::getTypeDesc($data['type']);
    }


    /**
     * @notes 兑换所需描述
     * @param $value
     * @param $data
     * @return string
     * @author 段誉
     * @date 2022/3/30 11:59
     */
    public function getNeedDescAttr($value, $data)
    {
        $desc = $data['need_integral'] . '积分+' . $data['need_money'] . '元';

        if ($data['type'] == IntegralGoodsEnum::TYPE_BALANCE || $data['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_INTEGRAL) {
            $desc = $data['need_integral'] . '积分';
        }

        return $desc;
    }

}