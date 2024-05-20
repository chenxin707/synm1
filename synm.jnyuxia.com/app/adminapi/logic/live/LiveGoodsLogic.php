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
namespace app\adminapi\logic\live;

use app\common\exception\WechatException;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\HttpException;
use think\Exception;

/**
 * 直播商品逻辑层
 * Class LiveGoodsLogic
 * @package app\adminapi\logic\live
 */
class LiveGoodsLogic
{
    /**
     * @notes 直播商品列表
     * @param int $limitOffset
     * @param int $limitLength
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @author cjhao
     * @date 2021/11/27 10:52
     */
    public function lists(int $limitOffset,int $limitLength,int $type){
        try{
            $config = WeChatConfigService::getMnpConfig();
            $app = Factory::miniProgram($config);
            $accessToken = $app->access_token->getToken()['access_token'];

            $params = ['access_token'=>$accessToken,'status'=>$type,'offset'=>$limitOffset-1,'limit'=>$limitLength];

            $result = $app->broadcast->getApproved($params);
            if (0  != $result['errcode']) {
                throw new WechatException( $result['errmsg'],$result['errcode'] );
            }
            $goodsList = [];
            foreach ($result['goods'] as $goods){
                $goodsList[] = [
                    'goods_id'      => $goods['goodsId'],
                    'name'          => $goods['name'],
                    'image'         => $goods['coverImgUrl'],
                    'price_type'    => $goods['priceType'],
                    'url'           => $goods['url'],
                    'price'         => $goods['price'],
                    'price2'        => $goods['price2'],
                ];
            }

            $list = [
                'lists'         => $goodsList,
                'count'         => $result['total'],
                'page_no'       => $limitOffset,
                'page_size'     => $limitLength,
            ];
            return $list;
        }catch (HttpException|Exception $e){
            return $e->getMessage();

        }


    }
    /**
     * @notes 添加直播商品
     * @param array $post
     * @return bool|string
     * @author cjhao
     * @date 2021/11/23 16:25
     */
    public function add(array $post)
    {
        try {
            $data = [
                'coverImgUrl' => $post['image'],
                'name' => $post['name'],
                'priceType' => $post['price_type'],
                'price' => $post['price'],
                'price2' => $post['price2'],
                'url' => $post['url'],
            ];


            $config = WeChatConfigService::getMnpConfig();
            $app = Factory::miniProgram($config);
            $result = $app->broadcast->create($data);
            if (0 != $result['errcode']) {
                throw new WechatException($result['errmsg'],$result['errcode']);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除直播商品
     * @param int $goods_id
     * @author cjhao
     * @date 2021/11/23 18:02
     */
    public function del(int $goodsId)
    {
        try {

            $config = WeChatConfigService::getMnpConfig();
            $app = Factory::miniProgram($config);
            $result = $app->broadcast->delete($goodsId);
            if (0 != $result['errcode']) {
                throw new WechatException($result['errmsg'],$result['errcode']);
            }
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }
}