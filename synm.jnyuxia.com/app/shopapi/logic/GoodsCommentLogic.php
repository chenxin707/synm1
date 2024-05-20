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

namespace app\shopapi\logic;


use app\common\enum\GoodsCommentEnum;
use app\common\logic\BaseLogic;
use app\common\model\GoodsComment;
use app\common\model\GoodsCommentImage;
use app\common\model\OrderGoods;
use app\common\service\FileService;
use think\facade\Db;

class GoodsCommentLogic extends BaseLogic
{
    /**
     * @notes 添加商品评价
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/6 8:15 下午
     */
    public function add($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //获取订单商品信息
            $order_goods = OrderGoods::find($params['order_goods_id'])->append(['spec_value_str'])->toArray();

            //添加评价数据
            $goods_comment = new GoodsComment;
            $goods_comment->goods_id = $order_goods['goods_id'];
            $goods_comment->item_id = $order_goods['item_id'];
            $goods_comment->spec_value_str = $order_goods['spec_value_str'];
            $goods_comment->user_id = $params['user_id'];
            $goods_comment->order_goods_id = $params['order_goods_id'];
            $goods_comment->goods_comment = $params['goods_comment'];
            $goods_comment->service_comment = $params['service_comment'];
            $goods_comment->description_comment = $params['description_comment'];
            $goods_comment->express_comment = $params['express_comment'];
            $goods_comment->comment = $params['comment'] ?? '';
            $goods_comment->duokaiSave();

            //添加评价图片数据
            if (isset($params['image'])) {
                $image_data = [];
                foreach ($params['image'] as $val) {
                    $image_data[] = [
                        'comment_id' => $goods_comment->id,
                        'uri' => FileService::setFileUrl($val),
                    ];
                }
                $goods_comment_image = new GoodsCommentImage;
                $goods_comment_image->duokaiSaveAll($image_data);
            }

            //修改订单商品表评价状态
            OrderGoods::duokaiUpdate(['is_comment' => 1], ['id' => $params['order_goods_id']]);


            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 查看商品评价分类
     * @param $parmas
     * @return array
     * @author ljj
     * @date 2021/8/6 9:04 下午
     */
    public function commentCategory($parmas)
    {
        $all_count = GoodsComment::where('goods_id', $parmas['goods_id'])->where(['status'=>GoodsCommentEnum::APPROVED])->count();
        $image_count = GoodsComment::withoutGlobalScope()->aliasSid('gc')->alias('gc')->where('goods_id', $parmas['goods_id'])->where(['status'=>GoodsCommentEnum::APPROVED])->join('goods_comment_image gci', 'gc.id = gci.comment_id')->group('gci.comment_id')->count();
        $good_count = GoodsComment::where('goods_id', $parmas['goods_id'])->where('goods_comment','>',3)->where(['status'=>GoodsCommentEnum::APPROVED])->count();
        $medium_count = GoodsComment::where('goods_id', $parmas['goods_id'])->where('goods_comment','=',3)->where(['status'=>GoodsCommentEnum::APPROVED])->count();
        $bad_count = GoodsComment::where('goods_id', $parmas['goods_id'])->where('goods_comment','<',3)->where(['status'=>GoodsCommentEnum::APPROVED])->count();

        if($all_count == 0) {
            $percentStr = '100%';
            $star = 5;
        }else {
            $percent = round((($good_count / $all_count) * 100));
            $percentStr = round((($good_count / $all_count) * 100)).'%';
            if ($percent >= 100) {
                $star = 5;
            } else if ($percent >= 80) {
                $star = 4;
            } else if ($percent >= 60) {
                $star = 3;
            } else if ($percent >= 40) {
                $star = 2;
            } else if ($percent >= 20) {
                $star = 1;
            } else {
                $star = 0;
            }
        }

        return ['comment'=>
            [
                [
                    'id'    => 0,
                    'name'  => '全部',
                    'count' => $all_count
                ],
                [
                    'id'    => 1,
                    'name'  => '晒图',
                    'count' => $image_count
                ],
                [
                    'id'    => 2,
                    'name'  => '好评',
                    'count' => $good_count
                ],
                [
                    'id'    => 3,
                    'name'  => '中评',
                    'count' => $medium_count
                ],
                [
                    'id'    => 4,
                    'name'  => '差评',
                    'count' => $bad_count
                ]
            ] ,
            'percent'   => $percentStr,
            'star'   => $star,
        ];
    }

    /**
     * @notes 查看评价商品信息
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/9 3:12 下午
     */
    public function commentGoodsInfo($params)
    {
        $info = OrderGoods::field('id,goods_id,goods_name,goods_price,goods_num,goods_snap,total_price,total_pay_price')
            ->where('id', '=', $params['order_goods_id'])
            ->append(['goods_image'])
            ->hidden(['goods_snap'])
            ->find()
            ->toArray();

        return $info;
    }
}