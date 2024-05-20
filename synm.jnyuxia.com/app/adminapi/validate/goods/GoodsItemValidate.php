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
namespace app\adminapi\validate\goods;
use app\common\{enum\FreightEnum,
    enum\GoodsEnum,
    model\Freight,
    model\GoodsItem,
    model\GoodsSpec,
    model\GoodsSpecValue,
    validate\BaseValidate};

/**
 * 商品规格验证器
 * Class GoodsItemValidate
 * @package app\adminapi\validate\goods
 */
class GoodsItemValidate extends BaseValidate{
    protected $rule = [
        'spec_value'            => 'requireIf:spec_type,'.GoodsEnum::SEPC_TYPE_MORE.'|checkSpecValue',
        'spec_value_list'       => 'requireIf:spec_type,'.GoodsEnum::SEPC_TYPE_MORE.'|checkSpecValueList',
    ];

    protected $message = [
        'spec_value.requireIf'          => '请输入商品规格项',
        'spec_value_list.requireIf'     => '请输入商品规格信息',
    ];

    //验证添加规格不允许重复
    public function checkSpecValue($value, $rule, $data)
    {

        $specNameArray = [];
        $specValueArray = [];

        //编辑时验证规格名和规格值
        if(isset($data['id'])){
            $specIds = GoodsSpec::where(['goods_id'=>$data['id']])->column('id');
            $specVluaeIds = GoodsSpecValue::where(['goods_id'=>$data['id']])->column('id');
        }


        foreach ($value as $specVal){
            //编辑时验证数据是否缺少
            if(isset($data['id'])){
                if(!isset($specVal['id'])){
                    return '规格名id缺少';
                }
                if($specVal['id'] > 0 && !in_array($specVal['id'],$specIds)){
                    return '规格名信息不匹配';
                }
            }
            if(empty($specVal['name'])){
                return '规格名称不能为空';
            }
            //验证规格名是否存在重复
            if(in_array($specVal['name'],$specNameArray)){
                return '规格名：'.$specVal['name'].'重复';
            }
            $specNameArray[] = $specVal['name'];

            //验证规格值是否存在重复
            foreach ($specVal['spec_list'] as $spec){

                //编辑时验证数据是否缺少
                if(isset($data['id'])){
                    if(!isset($spec['id'])){
                        return '规格值id缺少';
                    }
                    if($spec['id'] > 0 && !in_array($spec['id'],$specVluaeIds)){
                        return '规格值信息不匹配';
                    }
                }
                if(empty($spec['value'])){
                    return '规格值不能为空';
                }
                if(in_array($spec,$specValueArray)){
                    return '规格值：'.$spec.'重复';
                }
                $specValueArray[] = $spec;
            }
            $specValueArray = [];

        }
        return true;

    }


    //验证规格
    public function checkSpecValueList($value, $rule, $data){

        //编辑时验证规格信息
        if(isset($data['id'])){
            $itemIds = GoodsItem::where(['goods_id'=>$data['id']])->column('id');
        }
        $chargeWay = 0;
        if(3 == $data['express_type']){
            $chargeWay = Freight::where(['id'=>$data['express_template_id']])->value('charge_way');
        }
        //单规格验证
        if (GoodsEnum::SEPC_TYPE_SIGNLE == $data['spec_type']) {

            foreach ($value as $spec_list) {

                //编辑时验证单规格信息
                if(isset($data['id'])){
                    //编辑时必须带上id，新增留空
                    if(!isset($spec_list['id'])){
                        return '规格id缺少';
                    }
                    if($spec_list['id'] > 0 && !in_array($spec_list['id'],$itemIds)){
                        return '规格信息不匹配';
                    }
                }

                //验证必填是否缺少信息
                if ('' == $spec_list['sell_price']) {
                    return '请输入的价格';
                }
                if($spec_list['sell_price'] < 0){
                    return '价格不能小于零';
                }
//                if ('' == $spec_list['lineation_price']) {
//                    return '请输入划线价格';
//                }
//                if($spec_list['lineation_price'] < 0){
//                    return '划线价格不能小于零';
//                }
                if ('' == $spec_list['stock']) {
                    return '请输入库存';
                }
                if($spec_list['stock'] < 0){
                    return '库存不能小于零';
                }
                if(FreightEnum::CHARGE_WAY_VOLUME == $chargeWay && (empty($spec_list['volume'])  || $spec_list['volume'] <= 0)){
                    return '当前运费模板是按体积计算运费，请输入体积';
                }
                if(FreightEnum::CHARGE_WAY_WEIGHT == $chargeWay && (empty($spec_list['weight'])  || $spec_list['weight'] <= 0)){
                    return '当前运费模板是按重量计算运费，请输入重量';
                }
            }

        }else {
            //转换数据结构，ids为索引
            $spec_value_list = array_column($value, null, 'ids');

            foreach ($data['server_spec_value_list'] as $spec_list){

                $spec = $spec_value_list[$spec_list['ids']] ?? [];
                if (empty($spec)) {
                    return '规格信息错误';
                }

                //编辑时验证多规格信息
                if(isset($data['id'])){
                    //编辑时必须带上id，新增留空
                    if(!isset($spec['id'])){
                        return '规格id缺少';
                    }
                    if($spec['id'] > 0 && !in_array($spec['id'],$itemIds)){
                        return '规格信息不匹配';
                    }
                }

                //验证必填是否缺少信息
                if('' == $spec['sell_price'] ){
                    return '请输入' . $spec_list['spec_value'] . '的价格';
                }
                if ($spec['sell_price'] < 0) {
                    return $spec_list['spec_value'] . '的价格不能小于零';
                }
//                if('' == $spec['lineation_price']){
//                    return '请输入' . $spec_list['spec_value'] . '的划线价格';
//                }
//                if ($spec['lineation_price'] < 0) {
//                    return $spec_list['spec_value'] . '的划线价格不能小于零';
//                }
                if('' == $spec['stock']){
                    return '请输入' . $spec_list['spec_value'] . '的库存';
                }
                if ($spec['stock'] < 0) {
                    return $spec_list['spec_value'] . '的库存不能小于零';
                }
                if(FreightEnum::CHARGE_WAY_VOLUME == $chargeWay && empty($spec['volume'])){
                    return '当前运费模板是按体积计算运费，请填写规格：'.$spec_list['spec_value'] .'的体积';
                }
                if(FreightEnum::CHARGE_WAY_VOLUME == $chargeWay &&  $spec['volume'] <= 0){
                    return '当前运费模板是按体积计算运费，规格：'.$spec_list['spec_value'] .'的体积必须大于零';
                }
                if(FreightEnum::CHARGE_WAY_WEIGHT == $chargeWay &&  empty($spec['volume'])){
                    return '当前运费模板是按重量计算运费，请填写规格：'.$spec_list['spec_value'] .'的重量';
                }
                if(FreightEnum::CHARGE_WAY_WEIGHT == $chargeWay && $spec['weight'] <= 0){
                    return '当前运费模板是按重量计算运费，规格：'.$spec_list['spec_value'] .'的重量必须大于零';
                }

            }
        }

        return true;




    }
}