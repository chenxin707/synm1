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

class GoodsGatherLog extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    /**
     * @notes 商品类型
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2023/3/13 3:12 下午
     */
    public function getGoodsTypeDescAttr($value,$data)
    {
        return GoodsEnum::getGoodsTypeDesc($data['goods_type']);
    }

    /**
     * @notes 商品分类
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2023/3/13 3:12 下午
     */
    public function getGoodsCategoryDescAttr($value,$data)
    {
        $result = GoodsCategory::where('id',$data['goods_category'])->findOrEmpty()->toArray();
        if (empty($result)) {
            return '';
        }
        $name_arr[] = $result['name'];
        if ($result['pid'] > 0) {
            $result = GoodsCategory::where('id',$result['pid'])->findOrEmpty()->toArray();
            $name_arr[] = $result['name'];
            if ($result['pid'] > 0) {
                $result = GoodsCategory::where('id',$result['pid'])->findOrEmpty()->toArray();
                $name_arr[] = $result['name'];
            }
        }

        return implode('/',$name_arr);
    }

    /**
     * @notes 采集数
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2023/3/13 3:20 下午
     */
    public function getGatherNumAttr($value,$data)
    {
        return GoodsGather::where(['log_id'=>$data['id']])->count();
    }

    /**
     * @notes 采集成功数
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2023/3/13 3:20 下午
     */
    public function getGatherSuccessNumAttr($value,$data)
    {
        return GoodsGather::where(['log_id'=>$data['id'],'gather_status'=>YesNoEnum::YES])->count();
    }

    /**
     * @notes 采集失败数
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2023/3/13 3:20 下午
     */
    public function getGatherFailNumAttr($value,$data)
    {
        return GoodsGather::where(['log_id'=>$data['id'],'gather_status'=>YesNoEnum::NO])->count();
    }
}