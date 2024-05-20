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
namespace app\common\service;

use app\common\exception\WechatException;
use app\common\logic\BaseLogic;
use EasyWeChat\{
    Factory,
    Kernel\Http\StreamResponse,
    Kernel\Exceptions\Exception,

};

/**
 * 微信功能类
 * Class WeChatService
 * @package app\common\service
 */
class WeChatService extends BaseLogic
{
    /**
     * @notes 公众号-根据code获取微信信息
     * @param array $params
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Overtrue\Socialite\Exceptions\AuthorizeFailedException
     * @author cjhao
     * @date 2021/8/16 14:55
     */
    public static function getOaResByCode(array $params)
    {
        $config = WeChatConfigService::getOaConfig();
        $app = Factory::officialAccount($config);

        $response = $app->oauth
            ->scopes(['snsapi_userinfo'])
            ->userFromCode($params['code'])
            ->getRaw();

        if (!isset($response['openid']) || empty($response['openid'])) {
            throw new Exception('获取openID失败');
        }
        return $response;
    }


    /**
     * @notes 小程序-根据code获取微信信息
     * @param $post
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @author cjhao
     * @date 2021/8/16 14:57
     */
    public static function getMnpResByCode($post)
    {
        $config = WeChatConfigService::getMnpConfig();

        $app = Factory::miniProgram($config);
        $response = $app->auth->session($post['code']);
        if (!isset($response['openid']) || empty($response['openid'])) {
            throw new Exception('获取openID失败');
        }
        return $response;
    }

    /**
     * @notes 公众号跳转url
     * @param $url
     * @return string
     * @author cjhao
     * @date 2021/8/16 15:00
     */
    public static function getCodeUrl($url)
    {

        $config = WeChatConfigService::getOaConfig();
        $app = Factory::officialAccount($config);
        $response = $app
            ->oauth
            ->scopes(['snsapi_userinfo'])
            ->redirect($url);

        return $response;
    }

    /**
     * @notes 生成小程序码，使用wxacode.getUnlimited接口
     * @param array $param $param 参数配置 page:页面路径；scene：页面参数；saveDir：保存路径；fileName：文件名
     * @param string $type 返回类型：resource时返回资源类型,file保存并返回文件,base64返回base64
     * @return mixed|string
     * @author cjhao
     * @date 2021/8/16 14:43
     */
    public static function makeMpQrCode(array $param, string $type = 'resource')
    {
        try {

            $page = $param['page'] ?? '';
            $scene = $param['scene'] ?? 'null';
            $saveDir = $param['save_dir'] ?? 'uploads/qr_code/user_share/';
            $fileName = $param['file_name'] ?? time() . '.png';

            $config = WeChatConfigService::getMnpConfig();

            $app = Factory::miniProgram($config);
            $response = $app->app_code->getUnlimit($scene, [
                'page' => $page,
            ]);

            if (is_array($response) && isset($response['errcode'])) {
                //开启错误提示，小程序未发布和页面不存在，返回提示
                if (41030 === $response['errcode']) {
                    throw new Exception('所传page页面不存在，或者小程序没有发布');
                }

                throw new Exception($response['errmsg']);
            }

            $contents = $response->getBody()->getContents();
            switch ($type){
                case 'file':
                    if ($response instanceof StreamResponse) {
                        $fileName = $response->saveAs($saveDir, $fileName);
                        $contents = $saveDir . $fileName;
                    }
                    break;
                case 'base64':
                    $mpBase64 = chunk_split(base64_encode($contents));
                    $contents = 'data:image/png;base64,' . $mpBase64;
            }

            self::$returnData = $contents;
            return true;

        } catch (Exception $e) {
            self::$returnData = $e->getMessage();
            return false;

        }

    }

    /**
     * @notes 获取直播间
     * @param int $start
     * @param int $limit
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author cjhao
     * @date 2021/11/27 10:00
     */
    public static function getLiveRoom(int $start = 0,int $limit = 25)
    {
        try {
            $config = WeChatConfigService::getMnpConfig();
            $app = Factory::miniProgram($config);
            $result = $app->broadcast->getRooms($start, $limit);
            if( 0 !=$result['errcode']){
                throw new WechatException($result['errmsg'],$result['errcode']);
            }
            return $result;
        } catch (Exception |\think\Exception $e) {
            return $e->getMessage();
        }

    }

}