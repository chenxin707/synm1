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
use app\common\{model\User, logic\BaseLogic, enum\ShopPageEnum, service\pay\ToutiaoPayService, service\WeChatService};
use think\Exception;


/**
 * 分享逻辑层
 * Class ShareLogic
 * @package app\shopapi\logic
 */
class ShareLogic extends BaseLogic
{
    /**
     * @notes 获取小程序码
     * @param int $userId
     * @return array
     * @author cjhao
     * @date 2021/9/11 16:35
     */
    public function getMnpQrCode(int $userId,array $params)
    {

        $id = $params['id'] ?? '';
        $user = User::where(['id'=>$userId])->field('id,nickname,code')->find();
        $mpParam['scene'] = 'invite_code='.$user['code'];
        $mpParam['page'] = $params['page'] ?? '';
        if(empty($mpParam['page'])){
            $mpParam['page'] = substr(ShopPageEnum::HOME_PAGE['mobile'],1);
        }
        if($id){
            $mpParam['scene'] .= '&id='.$id;
        }
        $res = WeChatService::makeMpQrCode($mpParam,'base64');
        return $res;
    }

    /**
     * @notes 分享砍价小程序码
     * @param int $userId
     * @param array $params
     * @return bool|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/28 17:28
     */
    public function getMnpQrCodeInitiate(int $userId,array $params)
    {
        $param['page'] = substr(ShopPageEnum::HOME_PAGE['mobile'],1);
        $param['scene'] = 'initiate_id='.$params['initiate_id'];
        $param['page'] = substr(ShopPageEnum::INITIATE['mobile'],1);
        $res = WeChatService::makeMpQrCode($param,'base64');
        return $res;
    }


    /**
     * @notes 获取今日头条小程序码
     * @param int $userId
     * @param array $params
     * @return bool
     * @throws \Exception
     * @author cjhao
     * @date 2021/11/25 9:59
     */
    public function getTouTiaoQrcode(int $userId,array $params){
        try{

            $id = $params['id'] ?? '';
            $user = User::where(['id'=>$userId])->field('id,nickname,code')->find();
            $mpParam['appname'] = $params['appname'];
            $mpParam['page'] = $params['page'] .'?'.'invite_code='.$user['code'].'&id='.$id;
            if($id){
                $mpParam['page'] .= '&id='.$id;
            }
            $toutiaoService = (new ToutiaoPayService());
            $res = $toutiaoService->getQrcode($mpParam);

            self::$returnData = $res;
            return true;

        }catch (Exception $e){
            self::$returnData = $e->getMessage();
            return false;
        }


    }
}