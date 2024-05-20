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

class RegionCache extends BaseCache
{
    protected $key;
    public function __construct()
    {
        parent::__construct(false);
        $this->tagName = 'systemCache';
        $this->key = 'region';

    }

    /**
     * @notes 获取地址信息
     * @return false|mixed
     * @author Tab
     * @date 2021/7/17 11:24
     */
    public function getRegion()
    {
        $region = $this->get($this->key);
        if($region) {
            return $region;
        }
        return false;
    }

    /**
     * @notes 设置地填信息缓存
     * @param $data
     * @param $expire
     * @author Tab
     * @date 2021/7/17 11:32
     */
    public function setRegion($data, $expire)
    {
        $this->set($this->key, $data, $expire);
    }
}