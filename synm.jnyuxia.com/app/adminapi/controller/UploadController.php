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

namespace app\adminapi\controller;


use app\common\enum\FileEnum;
use app\common\service\UploadService;
use app\common\validate\UploadValidate;
use Exception;
use think\response\Json;

class UploadController extends BaseAdminController
{
    /**
     * @notes 上传图片
     * @return Json
     * @author 张无忌
     * @date 2021/7/28 16:57
     */
    public function image()
    {
        (new UploadValidate())->goCheck(null,['file'=>$this->request->file()]);
        try {
            $cid = $this->request->post('cid', 0);
            $saveDir = 'uploads/'.$this->adminInfo['shop_sn'].'/admin/images';
            $result = UploadService::image($cid,$this->adminId,FileEnum::SOURCE_BACKSTAGE,$saveDir);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 上传视频
     * @return Json
     * @author 张无忌
     * @date 2021/7/29 14:01
     */
    public function video()
    {
        (new UploadValidate())->goCheck(null,['file'=>$this->request->file()]);
        try {
            $cid = $this->request->post('cid', 0);
            $saveDir = 'uploads/'.$this->adminInfo['shop_sn'].'/admin/video';
            $result = UploadService::video($cid,$this->adminId,FileEnum::SOURCE_BACKSTAGE,$saveDir);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 上传素材
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/22 17:51
     */
    public function wechatMaterial()
    {
        $file = $this->request->file('file');
        $result = UploadService::wechatMaterial($file);
        if (is_array($result)) {
            return $this->success('', $result);
        }
        return $this->fail($result);

    }

}