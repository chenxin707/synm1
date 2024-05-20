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

namespace app\adminapi\logic\goods;


use app\common\enum\GoodsEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\GoodsGather;
use app\common\model\GoodsGatherGoods;
use app\common\model\GoodsGatherLog;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Db;

class GoodsGatherLogic extends BaseLogic
{
    /**
     * @notes 采集
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2023/3/13 6:35 下午
     */
    public function gather($params)
    {
        Db::startTrans();
        try {
            $apikey = ConfigService::get('goods_gather', 'key_99api', '');

            //添加采集记录
            $gather_log = GoodsGatherLog::duokaiCreate([
                'goods_type' => $params['goods_type'],
                'goods_category' => $params['goods_category'],
                'delivery_content' => $params['delivery_content'] ?? '',
            ]);

            $gather_goods_data = [];
            foreach ($params['gather_url'] as $url) {
                //分解采集连接
                $url_arr = parse_url($url);
                if (!isset($url_arr['host'])) {
                    $gather_data[] = [
                        'log_id' => $gather_log->id,
                        'gather_url' => $url,
                        'gather_status' => YesNoEnum::NO,
                    ];
                    continue;
                }

                //判断域名归属
                $url_host_arr = explode('.',$url_arr['host']);
                if (in_array('taobao',$url_host_arr)) { //淘宝
                    //分解请求参数
                    parse_str($url_arr['query'],$url_query_arr);
                    if (isset($url_query_arr['id'])) {
                        $query_url = 'https://api09.99api.com/taobao/detail?apikey='.$apikey.'&itemid='.$url_query_arr['id'];
                        $result = \Requests::post($query_url);
                        $result = json_decode($result->body,true);
                        if ($result['retcode'] == 4016) {
                            throw new \think\Exception('余额不足', 10006);
                        }
                    } else {
                        $result = '';
                    }
                    $channel = 1;
                    $gather_status = (isset($result['retcode']) && $result['retcode'] == '0000') ? 1 : 0;
                } elseif (in_array('tmall',$url_host_arr)) { //天猫
                    //分解请求参数
                    parse_str($url_arr['query'],$url_query_arr);
                    if (isset($url_query_arr['id'])) {
                        $query_url = 'https://api09.99api.com/tmall/detail?apikey='.$apikey.'&itemid='.$url_query_arr['id'];
                        $result = \Requests::post($query_url);
                        $result = json_decode($result->body,true);
                        if ($result['retcode'] == 4016) {
                            throw new \think\Exception('余额不足', 10006);
                        }
                    } else {
                        $result = '';
                    }
                    $channel = 2;
                    $gather_status = (isset($result['retcode']) && $result['retcode'] == '0000') ? 1 : 0;
                }  elseif (in_array('jd',$url_host_arr)) { //京东
                    //分解请求参数
                    $url_query_arr = pathinfo($url);
                    if (isset($url_query_arr['filename'])) {
                        $query_url = 'https://api09.99api.com/jd/detail?apikey='.$apikey.'&itemid='.$url_query_arr['filename'];
                        $result = \Requests::post($query_url);
                        $result = json_decode($result->body,true);
                        if ($result['retcode'] == 4016) {
                            throw new \think\Exception('余额不足', 10006);
                        }
                    } else {
                        $result = '';
                    }
                    $channel = 3;
                    $gather_status = (isset($result['retcode']) && $result['retcode'] == '0000') ? 1 : 0;
                }  elseif (in_array('1688',$url_host_arr)) { //1688
                    //分解请求参数
                    $url_query_arr = pathinfo($url);
                    $url_query_arr = explode('?',$url_query_arr['filename']);
                    $url_query_arr = explode('.',$url_query_arr[0]);
                    if (isset($url_query_arr[0])) {
                        $query_url = 'https://api09.99api.com/alibaba/detail?apikey='.$apikey.'&itemid='.$url_query_arr[0];
                        $result = \Requests::post($query_url);
                        $result = json_decode($result->body,true);
                        if ($result['retcode'] == 4016) {
                            throw new \think\Exception('余额不足', 10006);
                        }
                    } else {
                        $result = '';
                    }
                    $channel = 4;
                    $gather_status = (isset($result['retcode']) && $result['retcode'] == '0000') ? 1 : 0;
                } else {
                    $result = '';
                    $gather_status = 0;
                    $channel = 0;
                }

                $gather = GoodsGather::duokaiCreate([
                    'log_id' => $gather_log->id,
                    'gather_url' => $url,
                    'gather_info' => $result,
                    'gather_status' => $gather_status,
                    'channel' => $channel,
                ]);

                if ($gather_status == 1) {
                    $gather_goods_data[] = $this->makeGoods($gather_log,$gather);
                }
            }

            (new GoodsGatherGoods())->duokaiSaveAll($gather_goods_data);


            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 处理商品数据
     * @param $gather_log
     * @param $gather
     * @return array
     * @author ljj
     * @date 2023/3/16 10:09 上午
     */
    public function makeGoods($gather_log,$gather)
    {
        $goods_data['gather_id'] = $gather['id'];
        $goods_data['type'] = $gather_log['goods_type'];
        $goods_data['code'] = create_code();
        $goods_data['category_id'][] = $gather_log['goods_category'];
        $goods_data['delivery_content'] = $gather_log['delivery_content'];

        switch ($gather['channel']) {
            case GoodsEnum::GATHER_CHANNEL_TMALL:
            case GoodsEnum::GATHER_CHANNEL_TAOBAO:
                $goods_data['name'] = $gather['gather_info']['data']['item']['title'];
                $goods_data['content'] = $gather['gather_info']['data']['item']['desc'];

                //处理轮播图
                $goods_image = $gather['gather_info']['data']['item']['images'];
                foreach ($goods_image as $key=>$image_val) {
                    $goods_image[$key] = checkHttp($image_val);
                }

                //处理商品规格
                $spec_type = 1;
                $sku = $gather['gather_info']['data']['item']['sku'];
                $props = $gather['gather_info']['data']['item']['props'];
                if (count($sku) > 1) {
                    unset($sku[0]);
                    $sku = array_values($sku);
                    $spec_type = 2;
                }
                if ($spec_type == 1) {
                    $spec_value = '';
                    $spec_value_list[] = [
                        'image' => '',
                        'sell_price' => $sku[0]['price'],
                        'lineation_price' => '',
                        'cost_price' => '',
                        'stock' => $sku[0]['quantity'],
                        'volume' => '',
                        'weight' => '',
                        'bar_code' => '',
                    ];
                } else {
                    //多规格
                    //处理规格项数据
                    foreach ($props as $props_val) {
                        $has_image = '';
                        $spec_list = [];
                        foreach ($props_val['values'] as $val) {
                            if (isset($val['image'])) {
                                $has_image = 1;
                            }
                            $spec_list[] = [
                                'vid' => $val['vid'],
                                'value' => $val['name'],
                                'image' => $val['image'] ?? '',
                            ];
                        }
                        $spec_value[] = [
                            'pid' => $props_val['pid'],
                            'has_image' => $has_image,
                            'name' => $props_val['name'],
                            'spec_list' => $spec_list,
                        ];
                    }
                    //处理规格项信息数据
                    foreach ($sku as $sku_val) {
                        $prop_path_arr = explode(';',$sku_val['propPath']);
                        foreach ($prop_path_arr as $key=>$val) {
                            $prop_path_arr[$key] = explode(':',$val);
                        }
                        $spec_value_list[] = [
                            'ids' => "",
                            'prop_path' => $prop_path_arr,
                            'value' => [],
                            'spec_value_str' => [],
                            'image' => $sku_val['image'] ?? '',
                            'sell_price' => $sku_val['price'],
                            'lineation_price' => '',
                            'cost_price' => '',
                            'stock' => $sku_val['quantity'],
                            'volume' => '',
                            'weight' => '',
                            'bar_code' => '',
                        ];
                    }
                    $spec_list = array_column($spec_value,null,'pid');
                    foreach ($spec_value_list as $key=>$val) {
                        $ids = [];
                        $value = [];
                        foreach ($val['prop_path'] as $path_val) {
                            $spec_list_arr = $spec_list[$path_val[0]];
                            foreach ($spec_list_arr['spec_list'] as $spec_list_key=>$spec_list_val) {
                                if ($spec_list_val['vid'] == $path_val[1]) {
                                    $ids[] = $spec_list_key;
                                    $value[] = $spec_list_val['value'];
                                }
                            }
                        }
                        $spec_value_list[$key]['ids'] = implode(',',$ids);
                        $spec_value_list[$key]['value'] = $value;
                        $spec_value_list[$key]['spec_value_str'] = implode(',',$value);
                    }
                }
                break;
            case GoodsEnum::GATHER_CHANNEL_JD:
                $goods_data['name'] = $gather['gather_info']['data']['item']['name'];
                $goods_data['content'] = $gather['gather_info']['data']['item']['desc'];

                //处理轮播图
                $goods_image = $gather['gather_info']['data']['item']['images'];
                foreach ($goods_image as $key=>$image_val) {
                    $goods_image[$key] = checkHttp($image_val);
                }

                //处理商品规格
                $spec_type = 1;
                $sku = $gather['gather_info']['data']['item']['sku'];
                $sale_prop = $gather['gather_info']['data']['item']['saleProp'];
                $sku_prop = $gather['gather_info']['data']['item']['skuProps'];
                if (count($sku) > 1) {
                    $spec_type = 2;
                }
                if ($spec_type == 1) {
                    $spec_value = '';
                    $spec_value_list[] = [
                        'image' => '',
                        'sell_price' => $sku[0]['price'] ?? 0,
                        'lineation_price' => '',
                        'cost_price' => '',
                        'stock' => $sku[0]['stockState'] ?? 0,
                        'volume' => '',
                        'weight' => '',
                        'bar_code' => '',
                    ];
                } else {
                    //多规格
                    //处理规格项数据
                    $spec_value = [];
                    foreach ($sale_prop as $sale_prop_key=>$sale_prop_val) {
                        $spec_list = [];
                        if (empty($sale_prop_val)) {
                            break;
                        }
                        foreach ($sku_prop[$sale_prop_key] as $sku_prop_val_key=>$sku_prop_val) {
                            if (empty($sku_prop_val)) {
                                continue;
                            }
                            $spec_list[] = [
                                'key' => $sale_prop_key,
                                'value' => $sku_prop_val,
                                'image' => '',
                            ];
                        }
                        if (empty($spec_list)) {
                            continue;
                        }
                        $spec_value[] = [
                            'has_image' => '',
                            'name' => $sale_prop_val,
                            'spec_list' => $spec_list,
                        ];
                    }
                    //处理规格项信息数据
                    $spec_list = array_column($spec_value,'spec_list');
                    foreach ($sku as $sku_val) {
                        $ids = [];
                        $value = [];
                        foreach ($spec_list as $spec_list_val) {
                            foreach ($spec_list_val as $spec_list_val_key=>$spec_list_val_val) {
                                if ($spec_list_val_val['value'] == $sku_val[$spec_list_val_val['key']]) {
                                    $ids[] = $spec_list_val_key;
                                    $value[] = $spec_list_val_val['value'];
                                }
                            }
                        }
                        $spec_value_list[] = [
                            'ids' => implode(',',$ids),
                            'value' => $value,
                            'spec_value_str' => implode(',',$value),
                            'image' => '',
                            'sell_price' => $sku_val['price'] ?? 0,
                            'lineation_price' => '',
                            'cost_price' => '',
                            'stock' => $sku_val['stockState'] ?? 0,
                            'volume' => '',
                            'weight' => '',
                            'bar_code' => '',
                        ];
                    }
                }
                break;
            case GoodsEnum::GATHER_CHANNEL_1688:
                $goods_data['name'] = $gather['gather_info']['data']['title'];
                $goods_data['content'] = $gather['gather_info']['data']['desc'];

                //处理轮播图
                $goods_image = $gather['gather_info']['data']['images'];
                foreach ($goods_image as $key=>$image_val) {
                    $goods_image[$key] = checkHttp($image_val);
                }

                //处理商品规格
                $spec_type = 1;
                $sku = $gather['gather_info']['data']['skuMap'];
                $props = $gather['gather_info']['data']['skuProps'];
                if (count($sku) > 1) {
                    $spec_type = 2;
                }
                if ($spec_type == 1) {
                    $spec_value = '';
                    $sku = array_values($sku);
                    $spec_value_list[] = [
                        'image' => '',
                        'sell_price' => $sku[0]['discountPrice'] ?? 0,
                        'lineation_price' => '',
                        'cost_price' => '',
                        'stock' => $sku[0]['canBookCount'],
                        'volume' => '',
                        'weight' => '',
                        'bar_code' => '',
                    ];
                } else {
                    //多规格
                    //处理规格项数据
                    foreach ($props as $props_val) {
                        $has_image = '';
                        $spec_list = [];
                        foreach ($props_val['value'] as $val) {
                            if (isset($val['imageUrl']) && $val['imageUrl'] != '') {
                                $has_image = 1;
                            }
                            $spec_list[] = [
                                'value' => $val['name'],
                                'image' => $val['imageUrl'] ?? '',
                            ];
                        }
                        $spec_value[] = [
                            'has_image' => $has_image,
                            'name' => $props_val['prop'],
                            'spec_list' => $spec_list,
                        ];
                    }
                    //处理规格项信息数据
                    foreach ($sku as $sku_val) {
                        $spec_attrs = explode('&gt;',$sku_val['specAttrs']);
                        $spec_value_list[] = [
                            'ids' => "",
                            'spec_attrs' => $spec_attrs,
                            'value' => [],
                            'spec_value_str' => [],
                            'image' => '',
                            'sell_price' => $sku_val['discountPrice'] ?? 0,
                            'lineation_price' => '',
                            'cost_price' => '',
                            'stock' => $sku_val['canBookCount'],
                            'volume' => '',
                            'weight' => '',
                            'bar_code' => '',
                        ];
                    }
                    $spec_list = array_column($spec_value,'spec_list');
                    foreach ($spec_value_list as $key=>$val) {
                        $ids = [];
                        $value = [];
                        foreach ($val['spec_attrs'] as $spec_attrs_key=>$spec_attrs_val) {
                            $spec_list_arr = $spec_list[$spec_attrs_key];
                            foreach ($spec_list_arr as $spec_list_key=>$spec_list_val) {
                                if ($spec_list_val['value'] == $spec_attrs_val) {
                                    $ids[] = $spec_list_key;
                                    $value[] = $spec_list_val['value'];
                                }
                            }
                        }
                        $spec_value_list[$key]['ids'] = implode(',',$ids);
                        $spec_value_list[$key]['value'] = $value;
                        $spec_value_list[$key]['spec_value_str'] = implode(',',$value);
                    }
                }
                break;
        }

        $goods_data['goods_image'] = $goods_image;
        $goods_data['spec_type'] = $spec_type;
        $goods_data['spec_value'] = $spec_value;
        $goods_data['spec_value_list'] = $spec_value_list;

        return $goods_data;
    }

    /**
     * @notes 删除采集记录
     * @param $params
     * @return bool
     * @author ljj
     * @date 2023/3/14 9:41 上午
     */
    public function del($params)
    {
        GoodsGatherLog::destroy($params['log_id']);

        return true;
    }

    /**
     * @notes 采集商品详情
     * @param $params
     * @return array
     * @author ljj
     * @date 2023/3/16 10:56 上午
     */
    public function gatherGoodsDetail($params)
    {
        $result = GoodsGatherGoods::where(['gather_id'=>$params['id']])->findOrEmpty()->toArray();

        return $result;
    }

    /**
     * @notes 编辑采集商品
     * @param $params
     * @return bool
     * @author ljj
     * @date 2023/3/16 11:38 上午
     */
    public function gatherGoodsEdit($params)
    {
        GoodsGatherGoods::duokaiUpdate([
            'type' => $params['type'],
            'code' => $params['code'],
            'name' => $params['name'],
            'category_id' => $params['category_id'],
            'goods_image' => $params['goods_image'],
            'video_source' => $params['video_source'],
            'video_cover' => $params['video_cover'],
            'video' => $params['video'],
            'brand_id' => $params['brand_id'],
            'unit_id' => $params['unit_id'],
            'supplier_id' => $params['supplier_id'],
            'poster' => $params['poster'] ?? '',
            'is_express' => $params['is_express'],
            'is_selffetch' => $params['is_selffetch'],
            'express_type' => $params['express_type'],
            'express_money' => $params['express_money'],
            'express_template_id' => $params['express_template_id'],
            'is_virtualdelivery' => $params['is_virtualdelivery'] ?? 1,
            'after_pay' => $params['after_pay'] ?? 1,
            'after_delivery' => $params['after_delivery'] ?? 1,
            'delivery_content' => $params['delivery_content'] ?? '',
            'content' => $params['content'] ?? '',
            'stock_warning' => $params['stock_warning'],
            'virtual_sales_num' => $params['virtual_sales_num'],
            'virtual_click_num' => $params['virtual_click_num'],
            'spec_type' => $params['spec_type'],
            'spec_value' => $params['spec_value'],
            'spec_value_list' => $params['spec_value_list'],
            'is_address' => $params['is_address'] ?? 0,
        ],['gather_id'=>$params['gather_id']]);

        return true;
    }

    /**
     * @notes 创建商品
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2023/3/16 12:09 下午
     */
    public function createGoods($params)
    {
        Db::startTrans();
        try {
            $save_url = 'uploads/'.(isset($params['adminInfo']['shop_sn']) ? $params['adminInfo']['shop_sn'].'/' : '').'admin/images/';

            foreach ($params['ids'] as $id) {
                $gather_goods = GoodsGatherGoods::where(['gather_id'=>$id])->withoutField('id,gather_id,goods_id,create_time,update_time,delete_time')->findOrEmpty()->toArray();
                $gather_goods['status'] = $params['status'];

                //处理轮播图
                foreach ($gather_goods['goods_image'] as $key=>$val) {
                    $val = checkHttp($val);
                    $file = saveImageToLocal(md5($val).'.jpg',$val,$save_url);
                    if (!empty($file)) {
                        $gather_goods['goods_image'][$key] = $file['uri'];
                    } else {
                        unset($gather_goods['goods_image'][$key]);
                    }
                }
                $gather_goods['goods_image'] = array_values($gather_goods['goods_image']);

                //处理详情图片
                $content = getContentImage($gather_goods['content']);
                if (isset($content[1]) && is_array($content[1])) {
                    foreach ($content[1] as $key=>$val) {
                        $val = checkHttp($val);
                        $file = saveImageToLocal(md5($val).'.jpg',$val,$save_url);
                        $img = (isset($file['uri']) && !empty($file['uri'])) ? FileService::getFileUrl($file['uri']) : '';
                        $gather_goods['content'] = str_replace($content[1][$key],$img,$gather_goods['content']);

                    }
                }

                //处理规格项图片
                if ($gather_goods['spec_value']) {
                    foreach ($gather_goods['spec_value'] as $key=>$val) {
                        foreach ($val['spec_list'] as $key_son=>$val_son) {
                            if (empty($val_son['image'])) {
                                continue;
                            }
                            $val_son['image'] = checkHttp($val_son['image']);
                            $file = saveImageToLocal(md5($val_son['image']).'.jpg',$val_son['image'],$save_url);
                            $gather_goods['spec_value'][$key]['spec_list'][$key_son]['image'] = $file['uri'] ?? '';
                        }
                    }
                }

                //处理规格项信息图片
                foreach ($gather_goods['spec_value_list'] as $key=>$val) {
                    if (empty($val['image'])) {
                        continue;
                    }
                    $val['image'] = checkHttp($val['image']);
                    $file = saveImageToLocal(md5($val['image']).'.jpg',$val['image'],$save_url);
                    $gather_goods['spec_value_list'][$key]['image'] = $file['uri'] ?? '';
                }

                //添加商品
                if (GoodsEnum::SEPC_TYPE_MORE == $gather_goods['spec_type']) {
                    $gather_goods['server_spec_value_list'] = cartesian_product(array_converting(array_column($gather_goods['spec_value'],'spec_list')));
                    $spec_value_list = array_column($gather_goods['spec_value_list'], null, 'ids');
                    foreach ($gather_goods['server_spec_value_list'] as $serverKey=>$serverValue) {
                        if (!isset($spec_value_list[$serverValue['ids']])) {
                            unset($gather_goods['server_spec_value_list'][$serverKey]);
                        }
                    }
                }
                $goods = (new GoodsLogic)->setBase($gather_goods);
                (new GoodsLogic)->addGoodsItem($goods,$gather_goods);

                //更新商品采集商品信息表
                GoodsGatherGoods::duokaiUpdate(['goods_id'=>$goods->id],['gather_id'=>$id]);
                //更新商品采集表
                GoodsGather::duokaiUpdate(['status'=>1],['id'=>$id]);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }
}