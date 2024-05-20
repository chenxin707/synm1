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

use app\common\service\FileService;
use think\model\concern\SoftDelete;

class OrderGoods extends BaseModel
{
    //订单商品快照(json)
    protected $json = ['goods_snap'];

    /**
     * @notes 订单商品图片获取器
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2021/8/6 3:33 下午
     */
    public function getGoodsImageAttr($value,$data)
    {
        $goods_image = $data['goods_snap']->image;
        return empty($goods_image) ? '' : FileService::getFileUrl($goods_image);
    }

    /**
     * @notes 订单商品规格获取器
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2021/8/6 3:35 下午
     */
    public function getSpecValueStrAttr($value,$data)
    {
        return $data['goods_snap']->spec_value_str;
    }

    /**
     * @notes 商品名称获取器
     * @param $value
     * @param $data
     * @return mixed
     * @author Tab
     * @date 2021/8/10 11:32
     */
    public function getGoodsNameAttr($value,$data)
    {
        return $data['goods_snap']->goods_name;
    }

    /**
     * @notes 商品编码
     * @param $value
     * @param $data
     * @return string
     * @author cjhao
     * @date 2022/5/5 10:53
     */
    public function getCodeAttr($value, $data)
    {
        return $data['goods_snap']->code ?? '';
    }

//    /**
//     * @notes 返回商品id
//     * @param $value
//     * @param $data
//     * @return string
//     * @author cjhao
//     * @date 2022/9/26 16:51
//     */
//    public function getGoodsIdAttr($value, $data)
//    {
//        return $data['goods_snap']->goods_id ?? '';
//    }
}