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


use app\common\enum\FileEnum;
use app\common\model\Config;
use app\common\model\File;
use app\common\service\storage\Driver as StorageDriver;
use EasyWeChat\Factory;
use Exception;
use think\file\UploadedFile;

class UploadService
{
    /**
     * 上传图片
     * @notes
     * @param $cid
     * @param int $user_id
     * @param string $save_dir
     * @return array
     * @throws Exception
     * @author 张无忌
     * @date 2021/7/28 16:48
     */
    public static function image($cid, $source_id = 0,$source = FileEnum::SOURCE_BACKSTAGE, $save_dir='uploads/images')
    {
        try {
            $default = Config::withoutGlobalScope()->where(['type' => 'storage', 'name' => 'default','sid'=>0])->value('value') ?: 'local';
            $engine = Config::withoutGlobalScope()->where(['type' => 'storage','name'=>$default,'sid'=>0])->value('value');
            $engine = $engine ? [$default=>json_decode($engine,true)] : ['local'=>[]];
            $config = [
                'default' => $default,
                'engine'  => $engine,
            ];

            // 2、执行文件上传
            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();

            // 3、处理文件名称
            if (strlen($fileInfo['name']) > 128) {
                $file_name = substr($fileInfo['name'], 0, 123);
                $file_end = substr($fileInfo['name'], strlen($fileInfo['name'])-5, strlen($fileInfo['name']));
                $fileInfo['name'] = $file_name.$file_end;
            }

            // 4、写入数据库中
            $file = File::duokaiCreate([
                'cid'         => $cid,
                'type'        => FileEnum::IMAGE_TYPE,
                'name'        => $fileInfo['name'],
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'source'      => $source,
                'source_id'   => $source_id,
                'create_time' => time(),
            ]);

            // 5、返回结果
            return [
                'id'   => $file['id'],
                'cid'  => $file['cid'],
                'type' => $file['type'],
                'name' => $file['name'],
                'uri'  => FileService::getFileUrl($file['uri']),
                'url'  => $file['uri']
            ];

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @notes 视频上传
     * @param $cid
     * @param int $user_id
     * @param string $save_dir
     * @return array
     * @throws Exception
     * @author 张无忌
     * @date 2021/7/29 9:41
     */
    public static function video($cid,$source_id = 0,$source = FileEnum::SOURCE_BACKSTAGE, $save_dir='uploads/video')
    {
        try {
            $default = Config::withoutGlobalScope()->where(['type' => 'storage', 'name' => 'default','sid'=>0])->value('value') ?: 'local';
            $engine = Config::withoutGlobalScope()->where(['type' => 'storage','name'=>$default,'sid'=>0])->value('value');
            $engine = $engine ? [$default=>json_decode($engine,true)] : ['local'=>[]];
            $config = [
                'default' => $default,
                'engine'  => $engine,
            ];
            // 2、执行文件上传
            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();

            // 3、处理文件名称
            if (strlen($fileInfo['name']) > 128) {
                $file_name = substr($fileInfo['name'], 0, 123);
                $file_end = substr($fileInfo['name'], strlen($fileInfo['name'])-5, strlen($fileInfo['name']));
                $fileInfo['name'] = $file_name.$file_end;
            }

            // 4、写入数据库中
            $file = File::duokaiCreate([
                'cid'         => $cid,
                'type'        => FileEnum::VIDEO_TYPE,
                'name'        => $fileInfo['name'],
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'source'      => $source,
                'source_id'   => $source_id,
                'create_time' => time(),
            ]);

            // 5、返回结果
            return [
                'id'   => $file['id'],
                'cid'  => $file['cid'],
                'type' => $file['type'],
                'name' => $file['name'],
                'uri'  => FileService::getFileUrl($file['uri']),
                'url'  => $file['uri']
            ];

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @notes 上传素材
     * @param $url
     * @author cjhao
     * @date 2021/11/22 17:04
     */
    public static function wechatMaterial(UploadedFile $file)
    {
        try {
            $dir = './uploads/temp';
            $original_name = $file->getOriginalName();
            $file->move($dir,$original_name);
            $config = WeChatConfigService::getMnpConfig();

            $app = Factory::miniProgram($config);
            $path = ROOT_PATH . '/uploads/temp/' . $original_name;

            $result = $app->media->uploadImage($path);
            unlink($path);
            return ['media_id' => $result['media_id'], 'url' => FileService::getFileUrl('uploads/temp/' . $original_name)];

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}