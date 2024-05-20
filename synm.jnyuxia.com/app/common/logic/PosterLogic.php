<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likeshop_gitee/likeshop
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\common\logic;

use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\storage\Driver;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use Endroid\QrCode\QrCode;

/**
 * 海报逻辑层
 * Class PosterLogic
 * @package app\common\logic
 */
class PosterLogic extends BaseLogic
{
    /**
     * @notes 生成分销海报
     * @param $user
     * @param $content
     * @param $urlType
     * @param $terminal
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/6 15:09
     */
    public static function generate($user, $content, $urlType, $terminal)
    {
        // 获取分享海报背景图
        $bgImg = self::getBgImg();

        // 二维码保存路径
        $saveDir = 'resource/image/shopapi/qr_code/';
        if(!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        $saveKey = 'uid'.$user['id'].$urlType.$terminal;
        $qrCodeName = md5($saveKey) . '.png';
        $qrCodeUrl = public_path() . $saveDir . $qrCodeName;

        // 删除旧的二维码
        self::delOldQrCode($qrCodeUrl, $saveDir, $qrCodeName);

        // 生成二维码
        if($urlType == 'path'){
            // 小程序码
           self::makeMnpQrcode($user['code'], $content, $saveDir, $qrCodeName);
        }else{
            // 二维码
            $qrCode = new QrCode();
            $qrCode->setText($content);
            $qrCode->setSize(1000);
            $qrCode->setWriterByName('png');
            $qrCode->writeFile($qrCodeUrl);
        }

        // 获取海报配置
        $posterConfig = self::posterConfig();

        // 用户头像判断
        $userAvatar = FileService::setFileUrl($user['avatar']);
        $userAvatar = FileService::getFileUrl($userAvatar, 'share');
        if(!check_file_exists($userAvatar)){
            //如果不存在，使用默认头像
            $userAvatar = public_path().ConfigService::get('default_image', 'user_avatar');
        }
        // 使用分享背景图创建一个图像
        $bgResource = imagecreatefromstring(file_get_contents($bgImg));
        // 合成头像
        self::writeImg($bgResource, $userAvatar, $posterConfig['head_pic'],true);
        // 合成昵称
        $nickname = filterEmoji($user['nickname']);
        self::writeText($bgResource, $nickname, $posterConfig['nickname']);
        // 合成提示文本
        $notice = '长按识别二维码 >>';
        self::writeText($bgResource, $notice, $posterConfig['notice']);
        // 合成提示文本
        $title = auto_adapt($posterConfig['title']['font_size'], 0, $posterConfig['title']['font_face'], '邀请你一起来赚大钱', $posterConfig['title']['w'],$posterConfig['title']['y'],getimagesize($bgImg));
        self::writeText($bgResource, $title, $posterConfig['title']);
        // 合成邀请码
        self::writeText($bgResource, '邀请码 '.$user['code'], $posterConfig['code_text']);
        // 合成二维码
        self::writeImg($bgResource, $qrCodeUrl, $posterConfig['qr']);

        imagepng($bgResource, $qrCodeUrl);

        $fileName = $saveDir . $qrCodeName;
        $localUrl = ROOT_PATH.'/'.$fileName;
        self::upload($localUrl, $fileName);

        return $fileName . '?v=' . time();
    }

    /**
     * @notes 获取分享海报背景
     * @return array|int|mixed|string
     * @author Tab
     * @date 2021/8/6 11:27
     */
    public static function getBgImg()
    {
        // 分享海报背景图
        $bgImg = ConfigService::get('default_image', 'distribution_share_bg');

        // 存储引擎
        $storage = ConfigService::get('storage', 'default', 'local');
        if ($storage == 'local') {
            return public_path() .$bgImg;
        }

        // 非本地存储引擎
        $bgImg = FileService::getFileUrl($bgImg);
        if (!check_file_exists($bgImg)) {
            return ConfigService::get('share', 'poster');
        }
        return $bgImg;
    }

    /**
     * @notes 删除旧的二维码
     * @param $qrCodeUrl
     * @param $saveDir
     * @param $qrCodeName
     * @return bool
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/6 14:07
     */
    public static function delOldQrCode($qrCodeUrl, $saveDir, $qrCodeName)
    {
        // 获取存储引擎
        $config = [
            'default' => ConfigService::get('storage', 'default', 'local'),
            'engine'  => ConfigService::get('storage_engine')
        ];

        if ($config['default'] == 'local') {
            // 删除本地文件
            @unlink($qrCodeUrl);
        }else{
            // 删除非本地存储引擎上的文件
            $storageDriver = new Driver($config);
            $fileName = $saveDir . $qrCodeName;
            $storageDriver->delete($fileName);
        }
    }

    /**
     * @notes 海报配置
     * @return array
     * @author Tab
     * @date 2021/8/6 11:46
     */
    public static function posterConfig()
    {
        return [
            // 会员头像
            'head_pic' => [
                'w' => 80, 'h' => 80, 'x' => 30, 'y' => 680,
            ],
            // 会员昵称
            'nickname' => [
                'color' => '#333333', 'font_face' => public_path().'resource/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'x' => 120, 'y' => 730,
            ],
            // 标题
            'title' => [
                'color' => '#333333', 'font_face' => public_path().'resource/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'w' => 360, 'x' => 30, 'y' => 810,
            ],
            // 提醒、长按扫码
            'notice' => [
                'color' => '#333333', 'font_face' => public_path().'resource/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'x' => 30, 'y' => 880,
            ],
            // 邀请码文本
            'code_text' => [
                'color' => '#FF2C3C', 'font_face' => public_path().'resource/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'x' => 355, 'y' => 930,
            ],
            // 二维码
            'qr' => [
                'w' => 170,'h' => 170, 'x' => 370, 'y' => 730,
            ],
        ];
    }

    /**
     * @notes 获取小程序码
     * @param $code
     * @param $content
     * @param $saveDir
     * @param $qrCodeName
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author Tab
     * @date 2021/8/6 14:16
     */
    public static function makeMnpQrcode($code,$content,$saveDir,$qrCodeName)
    {
        $config = WeChatConfigService::getMnpConfig();
        $app = Factory::miniProgram($config);
        $response = $app->app_code->get($content.'?invite_code='.$code, [
            'width' => 170,
        ]);
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            // 保存小程序码
            $response->saveAs($saveDir, $qrCodeName);
            return true;
        }
        return false;
    }

    /**
     * @notes 写入图像
     * @param $bgResource
     * @param $img
     * @param $config
     * @param false $isRounded
     * @return mixed
     * @author Tab
     * @date 2021/8/6 14:40
     */
    public static function writeImg($bgResource, $img, $config, $isRounded = false){
        $picImg = imagecreatefromstring(file_get_contents($img));
        //切成圆角返回头像资源
        $isRounded ? $picImg = rounded_corner($picImg) : '';
        $picW = imagesx($picImg);
        $picH = imagesy($picImg);

        // 圆形头像大图合并到海报
        imagecopyresampled($bgResource, $picImg,
            $config['x'],
            $config['y'],
            0, 0,
            $config['w'],
            $config['h'],
            $picW,
            $picH
        );

        return $bgResource;
    }

    /**
     * @notes 写入文本
     * @param $bgResource
     * @param $text
     * @param $config
     * @return mixed
     * @author Tab
     * @date 2021/8/6 14:42
     */
    public static function writeText($bgResource, $text, $config){
        $fontUri = $config['font_face'];
        $fontSize = $config['font_size'];
        $color = substr($config['color'],1);
        //颜色转换
        $color= str_split($color, 2);
        $color = array_map('hexdec', $color);
        if (empty($color[3]) || $color[3] > 127) {
            $color[3] = 0;
        }
        $fontColor = imagecolorallocatealpha($bgResource, $color[0], $color[1], $color[2], $color[3]);
        imagettftext($bgResource, $fontSize,0, $config['x'], $config['y'], $fontColor, $fontUri, $text);
        return $bgResource;
    }

    /**
     * @notes 根据不同的存储引擎存储海报
     * @param $localUrl
     * @param $fileName
     * @throws \think\Exception
     * @author Tab
     * @date 2021/8/6 15:00
     */
    public static function upload($localUrl, $fileName)
    {
        $config = [
            'default' => ConfigService::get('storage', 'default', 'local'),
            'engine' => ConfigService::get('storage_engine')
        ];

        if ($config['default'] != 'local') {
            $storageDriver = new Driver($config);
            if (!$storageDriver->fetch($localUrl, $fileName)) {
                throw new \think\Exception('图片保存出错:' . $storageDriver->getError());
            }
            //删除本地图片
            unlink($fileName);
        }
    }


    /**
     * @notes 获取商品海报配置
     */
    public static function getGoodsConfig($id) {
        $defaultStyle = ConfigService::get("poster", 'default_style', 1);
        $id = empty($id) ? $defaultStyle : $id;
        $config = ConfigService::get('poster', $id, self::defaultGoodsConfig($id));
        $config = self::stringToInteger($config);
        return $config;
    }

    /**
     * @notes 设置商品海报配置
     */
    public static function setGoodsConfig($params) {
        ConfigService::set('poster', $params['id'], $params);
        ConfigService::set('poster', 'default_style', $params['id']);
    }


    /**
     * @notes 商品海报默认配置
     */
    public static function defaultGoodsConfig($id) {
        return [
            "id" => $id,
            "background_type" => 1,
            "background_url" => '',
            "show" => [
                "user_avtar" => 1,
                "user_name" => 1,
                "goods_img" => 1,
                "goods_name" => 1,
                "goods_sale_price" => 1,
                "goods_origin_price" => 1,
                "qrcode" => 1,
                "qrcode_title" => 1,
            ],
            "qrcode_align" => 2,
            "style" => [
                "user_name" => '#333333',
                "goods_sale_price" => '#FF0610',
                "goods_origin_price" => '#999999',
                "goods_name" => '#333333',
                "qrcode_title" => '#999999',
            ]
        ];
    }

    /**
     * @notes 获取邀请海报配置
     */
    public static function getDistributionConfig() {
        $config = ConfigService::get('poster', 'distribution', self::defaultDistributionConfig());
        $config = self::stringToInteger($config);
        return $config;
    }

    /**
     * @notes 邀请海报默认配置
     */
    public static function defaultDistributionConfig() {
        return [
            "background_type" => 1,
            "background_url" => '',
            "show" => [
                "user_avtar" => 1,
                "user_name" => 1,
                "slogan" => 1,
                "qrcode" => 1,
                "slogan_code" => 1,
            ],
            "slogan" => '邀请你一起来赚大钱',
            "style" => [
                "user_name" => '#333333',
                "slogan_text" => '#FF0610',
                "slogan_code" => '#999999',
            ]
        ];
    }

    /**
     * @notes 设置邀请海报配置
     */
    public static function setDistributionConfig($params) {
        ConfigService::set('poster', 'distribution', $params);
    }

    /**
     * @notes 字符串数字 转 纯数字
     */
    public static function stringToInteger($config) {
        foreach($config as $key => $value) {
            if ($key == 'style' || $key == 'background_url' || $key == 'slogan') {
                continue;
            }
            if ($key == 'show') {
                foreach($config[$key] as $k => $v) {
                    $config[$key][$k] = (int)$v;
                }
                continue;
            }
            $config[$key] = (int)$value;
        }
        return $config;
    }
}
