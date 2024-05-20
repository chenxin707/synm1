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


namespace app\common\cache;


use app\common\model\Kefu;
use app\common\model\KefuSession;

/**
 * 客服token缓存
 * Class KefuTokenCache
 * @package app\common\cache
 */
class KefuTokenCache extends BaseCache
{

    private $prefix = 'token_kefu_';

    public function __construct()
    {
        parent::__construct();
        $this->prefix = config('project.kefu_token.prefix');
    }

    /**
     * @notes 通过token获取缓存管理员信息
     * @param $token
     * @return array|false|mixed
     * @author 段誉
     * @date 2022/3/9 18:57
     */
    public function getKefuInfo($token)
    {
        //直接从缓存获取
        $info = $this->get($this->prefix . $token,null,false);
        if ($info) {
            return $info;
        }

        //从数据获取信息被设置缓存(可能后台清除缓存）
        $info = $this->setKefuInfo($token);
        if ($info) {
            return $info;
        }

        return false;
    }


    /**
     * @notes 通过有效token设置管理信息缓存
     * @param $token
     * @return array|false|mixed
     * @throws \Exception
     * @author 段誉
     * @date 2022/3/9 18:57
     */
    public function setKefuInfo($token)
    {
        $kefuSession = KefuSession::where([['token', '=', $token], ['expire_time', '>', time()]])->findOrEmpty();

        if ($kefuSession->isEmpty()) {
            return [];
        }

        $kefu = Kefu::with(['admin','platform_shop'])
            ->where('id', '=', $kefuSession->kefu_id)
            ->findOrEmpty();

        $kefuInfo = [
            'id'            => $kefu['id'],
            'admin_id'      => $kefu['admin_id'],
            'nickname'      => $kefu['nickname'],
            'account'       => $kefu['admin']['account'] ?? '',
            'token'         => $token,
            'terminal'      => $kefuSession['terminal'],
            'expire_time'   => $kefuSession['expire_time'],
            'shop_sn'       => $kefu['platform_shop']['sn'],
            'sid'           => $kefu['sid'],
        ];
        $this->set($this->prefix . $token, $kefuInfo, new \DateTime(Date('Y-m-d H:i:s', $kefuSession->expire_time)),false);
        return $this->getKefuInfo($token);
    }




    /**
     * @notes 删除缓存
     * @param $token
     * @return bool
     * @author 段誉
     * @date 2022/3/9 18:57
     */
    public function deleteKefuInfo($token)
    {
        return $this->delete($this->prefix . $token,false);
    }


}