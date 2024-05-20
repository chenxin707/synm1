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

namespace app\adminapi\validate\goods;


use app\common\enum\GoodsEnum;
use app\common\model\Freight;
use app\common\model\Goods;
use app\common\model\GoodsBrand;
use app\common\model\GoodsCategory;
use app\common\model\GoodsSupplier;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

class GoodsGatherValidate extends BaseValidate
{
    protected $rule = [
        'goods_type' => 'require|in:1,2',
        'goods_category' => 'require|checkCategory',
        'gather_url' => 'require|array',
        'log_id' => 'require',
        'id' => 'require',
        'delivery_content' => 'requireIf:goods_type,2',

        'gather_id' => 'require',
        'name'                  => 'require|max:64|unique:'.Goods::class.',name',
        'code'                  => 'require|max:32|unique:'.Goods::class.',code',
        'type'                  => 'require|in:'.GoodsEnum::GOODS_REALITY.','.GoodsEnum::GOODS_VIRTUAL,
        'category_id'           => 'require|array|checkCategory',
        'goods_image'           => 'require|array|max:10',
        'video_source'          => 'in:1,2',
        'brand_id'              => 'checkBrand',
        'supplier_id'           => 'checkSupplier',
        'express_type'          => 'require|in:1,2,3',
        'express_money'         => 'requireIf:express_type,2|regex:money',
        'express_template_id'   => 'requireIf:express_type,3|checkTemplateId',
        'is_express'            => 'require|in:0,1',
        'is_selffetch'          => 'require|in:0,1|checkDelivery',
        'is_virtualdelivery'    => 'requireIf:type,2|in:0,1',
        'after_pay'             => 'requireIf:type,2|in:0,'.GoodsEnum::AFTER_PAY_AUTO.','.GoodsEnum::AFTER_PAY_HANDOPERSTION,
        'after_delivery'        => 'requireIf:type,2|in:0,'.GoodsEnum::AFTER_DELIVERY_AUTO.','.GoodsEnum::AFTER_DELIVERY_HANDOPERSTION,
        'stock_warning'         => 'number',
        'virtual_sales_num'     => 'number',
        'virtual_click_num'     => 'number',
        'spec_type'             => 'require|in:'.GoodsEnum::SEPC_TYPE_SIGNLE.','.GoodsEnum::SEPC_TYPE_MORE,
        'spec_value'            => 'requireIf:spec_type,'.GoodsEnum::SEPC_TYPE_MORE.'|array',
        'spec_value_list'       => 'require|array',

        'ids'                   => 'require|array',
        'status'                => 'require|in:0,1',
    ];

    protected $message = [
        'goods_type.require' => '请选择商品类型',
        'goods_type.in' => '商品类型值错误',
        'goods_category.require' => '请选择商品分类',
        'gather_url.require' => '商品链接不能为空',
        'gather_url.array' => '商品链接值错误',
        'log_id.require' => '参数错误',
        'id.require' => '参数错误',
        'delivery_content.requireIf' => '请输入发货内容',

        'name.require'                  => '请选择商品名称',
        'name.max'                      => '商品名称不可以超过64个字符',
        'name.unique'                   => '商品名称已存在',
        'code.require'                  => '请输入商品编码',
        'code.max'                      => '商品编码不可以超过32个字符',
        'code.unique'                   => '商品编码已存在',
        'type.require'                  => '请选择商品类型',
        'type.in'                       => '商品类型错误',
        'goods_image.require'           => '请上传商品轮播图',
        'goods_image.array'             => '商品轮播图信息错误',
        'goods_image.max'               => '商品轮播图不能超过5张',
        'express_type.require'          => '请选择配送设置',
        'express_money.requireIf'       => '请输入运费',
        'express_money.regex'           => '运费必须大于零，且保留两位小数',
        'express_template_id.requireIf' => '请选择运费模板',
        'is_express.require'            => '请选择物流',
        'is_express.in'                 => '物流支持类型错误',
        'is_selffetch.require'          => '请选择物流',
        'is_selffetch.in'               => '物流支持类型错误',
        'is_virtualdelivery.requireIf'  => '请选择物流',
        'is_virtualdelivery.in'         => '物流支持类型错误',
        'after_pay.requireIf'           => '请选择买家付款后发货方式',
        'after_pay.in'                  => '买家付款后发货方式类型错误',
        'after_delivery.requireIf'      => '请选择发货后是否自动完成订单',
        'after_delivery.in'             => '发货后是否自动完成订单类型错误',
        'stock_warning.number'          => '库存预警只能输入正整数字',
        'virtual_sales_num.number'      => '虚拟销量只能输入正整数字',
        'virtual_click_num.number'      => '虚拟浏览量只能输入正整数字',
        'spec_type.require'             => '请选择规格',
        'spec_type.in'                  => '商品规格类型错误',
        'spec_value.requireIf'          => '请输入规格项',
        'spec_value.array'              => '规格项值错误',
        'spec_value_list.require'       => '请输入规格项信息',
        'spec_value_list.array'         => '规格项信息值错误',

        'ids.require'                   => '参数错误',
        'ids.array'                     => '参数格式错误',
        'status.require'                => '请选择状态值',
        'status.in'                     => '状态值错误',
    ];


    public function sceneGather()
    {
        return $this->only(['goods_type','goods_category','gather_url','delivery_content'])
            ->append('gather_url','checkGather');
    }

    public function sceneDel()
    {
        return $this->only(['log_id']);
    }

    public function sceneGatherGoodsDetail()
    {
        return $this->only(['id']);
    }

    public function sceneGatherGoodsEdit()
    {
        return $this->only(['gather_id','name','code','type','category_id','goods_image','video_source','brand_id','supplier_id','express_type','express_money','express_template_id','is_express','is_selffetch','is_virtualdelivery','after_pay','after_delivery','stock_warning','virtual_sales_num','virtual_click_num','spec_type','spec_value','spec_value_list']);
    }

    public function sceneCreateGoods()
    {
        return $this->only(['ids','status']);
    }


    /**
     * @notes 校验商品分类
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2023/3/13 3:33 下午
     */
    public function checkCategory($value,$rule,$data)
    {
        $result = GoodsCategory::where(['id'=>$value])->findOrEmpty();

        if($result->isEmpty()){
            return '商品分类不存在';
        }

        return true;
    }

    /**
     * @notes 校验采集
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2023/3/13 3:52 下午
     */
    public function checkGather($value,$rule,$data)
    {
        $key_99api = ConfigService::get('goods_gather', 'key_99api', '');
        if(empty($key_99api)){
            return '99Apikey还未配置，请前往配置';
        }

        return true;
    }

    /**
     * @notes 校验品牌
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2023/3/16 11:12 上午
     */
    public function checkBrand($value,$rule,$data)
    {
        if (empty($value)) {
            return true;
        }
        if (!(GoodsBrand::find($value))) {
            return '商品品牌不存在';
        }
        return true;
    }

    /**
     * @notes 校验供应商
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2023/3/16 11:12 上午
     */
    public function checkSupplier($value,$rule,$data)
    {
        if (empty($value)) {
            return true;
        }
        if (!(GoodsSupplier::find($value))) {
            return '供应商不存在';
        }
        return true;

    }

    /**
     * @notes 检查运费模版是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/8/2 5:54 下午
     */
    public function checkTemplateId($value,$rule,$data)
    {
        if ($data['express_type'] == 3) {
            $result = Freight::where('id',$value)->findOrEmpty();
            if ($result->isEmpty()) {
                return '运费模版不存在';
            }
        }
        return true;
    }

    /**
     * @notes 校验物流
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2023/3/16 11:13 上午
     */
    public function checkDelivery($value,$rule,$data)
    {
        if(GoodsEnum::GOODS_REALITY == $data['type'] && empty($value) && empty($data['is_express'])){
            return '至少选择一个物流支持';
        }
        return true;


    }
}