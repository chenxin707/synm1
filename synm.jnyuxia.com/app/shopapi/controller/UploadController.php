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

namespace app\shopapi\controller;


use app\common\enum\FileEnum;
use app\common\service\UploadService;
use app\common\validate\UploadValidate;
use Exception;
use think\response\Json;

class UploadController extends BaseShopController
{
    /**
     * @notes 用户图片上传
     * @return Json
     * @author 张无忌
     * @date 2021/8/25 19:55
     */
    public function image()
    {
        (new UploadValidate())->goCheck(null,['file'=>$this->request->file()]);
        try {
            $saveDir = 'uploads/'.$this->userInfo['shop_sn'].'/user/images';
            $result = UploadService::image(0, $this->userId,FileEnum::SOURCE_USER,$saveDir);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}