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
declare(strict_types=1);

namespace app\common\cache;


use think\App;
use think\Cache;

/**
 * 缓存基础类，用于管理缓存
 * Class BaseCache
 * @package app\common\cache
 */
abstract class BaseCache extends Cache
{
    protected $tagName;         //缓存标签
    protected $tagSid = '';     //商家标签


    public function __construct($tagSid = true)
    {

        parent::__construct(app());
        if(true === $tagSid){
            $this->tagSid = '_'.request()->sid;
        }
        //按商家id区别缓存，方便清掉某个商家缓存
        $this->tagName = 'sid'.$this->tagSid;

    }

    /**
     * @notes 重写父类set，自动打上标签
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @param true $tagSid
     * @return bool
     * @author cjhao
     * @date 2021/10/13 17:07
     */
    public function set($key, $value, $ttl = null,$tagSid = true): bool
    {

        if(true === $tagSid){
            $key .= $this->tagSid;
        }
        return $this->store()->tag($this->tagName)->set($key, $value, $ttl);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $key     缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($key, $default = null,$tagSid = true)
    {
        if(true === $tagSid){
            $key .= $this->tagSid;
        }
        return $this->store()->get($key, $default);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $key 缓存变量名
     * @return bool
     */
    public function delete($key,$tagSid = true): bool
    {
        if(true === $tagSid){
            $key .= $this->tagSid;
        }
        return $this->store()->delete($key);
    }

    /**
     * @notes 清除缓存类所有缓存
     * @return bool
     * @author 令狐冲
     * @date 2021/8/19 16:38
     */
    public function deleteTag()
    {
        return $this->tag($this->tagName)->clear();
    }

}