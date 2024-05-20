<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model;


use app\common\enum\GoodsEnum;
use app\common\enum\YesNoEnum;
use think\model\concern\SoftDelete;

class GoodsGather extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 设置json类型字段
    protected $json = ['gather_info'];
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;


    /**
     * @notes 商品信息
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2023/3/14 2:52 下午
     */
    public function getGoodsInfoAttr($value,$data)
    {
        $result = [];
        if ($data['gather_status'] == YesNoEnum::YES) {
//            switch ($data['channel']) {
//                case GoodsEnum::GATHER_CHANNEL_TMALL:
//                case GoodsEnum::GATHER_CHANNEL_TAOBAO:
//                    $result['goods_name'] = $data['gather_info']['data']['item']['title'];
//                    $result['goods_image'] = isset($data['gather_info']['data']['item']['images'][0]) ? ltrim($data['gather_info']['data']['item']['images'][0],'//') : '';
//                    $result['goods_image'] = checkHttp($result['goods_image']);
//                    $result['is_more_sku'] = 0;
//                    unset($data['gather_info']['data']['item']['sku'][0]);
//                    if (count($data['gather_info']['data']['item']['sku']) > 1) {
//                        $result['is_more_sku'] = 1;
//                    }
//                    break;
//                case GoodsEnum::GATHER_CHANNEL_JD:
//                    $result['goods_name'] = $data['gather_info']['data']['item']['name'];
//                    $result['goods_image'] = isset($data['gather_info']['data']['item']['images'][0]) ? ltrim($data['gather_info']['data']['item']['images'][0],'//') : '';
//                    $result['goods_image'] = checkHttp($result['goods_image']);
//                    $result['is_more_sku'] = 0;
//                    if (count($data['gather_info']['data']['item']['sku']) > 1) {
//                        $result['is_more_sku'] = 1;
//                    }
//                    break;
//                case GoodsEnum::GATHER_CHANNEL_1688:
//                    $result['goods_name'] = $data['gather_info']['data']['title'];
//                    $result['goods_image'] = isset($data['gather_info']['data']['images'][0]) ? ltrim($data['gather_info']['data']['images'][0],'//') : '';
//                    $result['goods_image'] = checkHttp($result['goods_image']);
//                    $result['is_more_sku'] = 0;
//                    if (count($data['gather_info']['data']['skuMap']) > 1) {
//                        $result['is_more_sku'] = 1;
//                    }
//                    break;
//            }

            $goods_gather_goods = GoodsGatherGoods::where(['gather_id'=>$data['id']])->findOrEmpty()->toArray();
            $result['goods_name'] = $goods_gather_goods['name'];
            $result['goods_image'] = $goods_gather_goods['goods_image'][0] ?? 0;
            $result['goods_image'] = checkHttp($result['goods_image']);
            $result['is_more_sku'] = 0;
            if ($goods_gather_goods['spec_type'] == 2) {
                $result['is_more_sku'] = 1;
            }
        }

        return $result;
    }

    /**
     * @notes 采集状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2023/3/14 2:55 下午
     */
    public function getGatherStatusDescAttr($value,$data)
    {
        return YesNoEnum::getGatherStatusDesc($data['gather_status']);
    }

    /**
     * @notes 采集渠道
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2023/3/14 2:56 下午
     */
    public function getChannelDescAttr($value,$data)
    {
        return GoodsEnum::getGatherChannelDesc($data['channel']);
    }
}