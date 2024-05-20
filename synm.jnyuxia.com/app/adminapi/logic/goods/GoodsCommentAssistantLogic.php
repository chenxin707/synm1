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

namespace app\adminapi\logic\goods;

use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\Goods;
use app\common\model\GoodsComment;
use app\common\model\GoodsCommentImage;
use app\common\model\GoodsItem;
use app\common\service\FileService;
use think\facade\Db;

class GoodsCommentAssistantLogic extends BaseLogic
{
    /**
     * @notes 添加虚拟评价
     * @param $params
     * @author Tab
     * @datetime 2022/1/18 9:53
     */
    public static function add($params)
    {
        Db::startTrans();
        try {
            $commentTime = strtotime($params['comment_time']);
            if ($commentTime === false) {
                throw new \Exception('评论时间格式错误');
            }
            if ($commentTime > time()) {
                throw new \Exception('评论时间不能超过当前时间');
            }

            $goods = Goods::findOrEmpty($params['goods_id']);
            if ($goods->isEmpty()) {
                throw new \Exception('商品不存在');
            }

            $virtual = [
                'sn' => create_user_sn(),
                'avatar' => FileService::setFileUrl($params['avatar']),
                'nickname' => $params['nickname'],
                'level_id' => $params['level_id'],
                'goods_name' => $goods['name'],
                'goods_image' => $goods['image']
            ];

            $item = GoodsItem::where('goods_id', $params['goods_id'])->findOrEmpty();

            $commentData = [
                'goods_id' => $params['goods_id'],
                'item_id' => $item['id'],
                'spec_value_str' => $item['spec_value_str'],
                'user_id' => 0,
                'order_goods_id' => 0,
                'goods_comment' => $params['goods_comment'],
                'service_comment' => $params['goods_comment'],
                'description_comment' => $params['goods_comment'],
                'express_comment' => $params['goods_comment'],
                'comment' => $params['comment'],
                'status' => YesNoEnum::YES,
                'virtual' => json_encode($virtual),
                'create_time' => $commentTime
            ];

            $newComment = GoodsComment::duokaiCreate($commentData);

            $commentImagesData = [];
            if (isset($params['comment_images']) && is_array($params['comment_images'])) {
                foreach($params['comment_images'] as $item) {
                    $commentImagesData[] = [
                        'comment_id' => $newComment['id'],
                        'uri' => FileService::setFileUrl($item),
                    ];
                }
            }
            if (count($commentImagesData)) {
                $goodsCommentImage = new GoodsCommentImage();
                $goodsCommentImage->duokaiSaveAll($commentImagesData);
            }


            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }
}
