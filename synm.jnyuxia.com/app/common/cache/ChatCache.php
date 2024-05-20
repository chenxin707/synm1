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


use think\facade\Cache;

/**
 * 聊天缓存
 * Class ChatTokenCache
 * @package app\common\cache
 */
class ChatCache
{

    private $redis;

    public function __construct()
    {
        $this->redis = Cache::store('redis')->handler();
    }

    /**
     * @notes 设置缓存
     * @param $key
     * @param $val
     * @param null $time
     * @return false
     * @author 段誉
     * @date 2021/12/20 12:13
     */
    public function set($key, $val, $time = null)
    {
        if (empty($key)) {
            return false;
        }
        return $this->redis->set($key, $val, $time);
    }


    /**
     * @notes 获取缓存
     * @param $key
     * @return false
     * @author 段誉
     * @date 2021/12/20 12:14
     */
    public function get($key)
    {
        if (empty($key)) {
            return false;
        }
        return $this->redis->get($key);
    }


    /**
     * @notes 删除指定
     * @param $key
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }


    /**
     * @notes 清空
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function flashAll()
    {
        return $this->redis->flushAll();
    }


    /**
     * @notes 获取集合
     * @param $key
     * @param boolean $Sidtag
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:11
     */
    public function sMembers($key,$Sidtag = true)
    {
        if(true === $Sidtag){
            $key = 'sid_'.request()->sid.'_'.$key;
        }
        return $this->redis->sMembers($key);
    }


    /**
     * @notes 设置缓存时间
     * @param $key
     * @param $ttl
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function expire($key, $ttl)
    {
        return $this->redis->expire($key, $ttl);
    }


    /**
     * @notes 向集合添加成员
     * @param $key
     * @param $val
     * @param boolean $Sidtag
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:04
     */
    public function sadd($key, $val,$Sidtag = true)
    {
        if(true === $Sidtag){
            $key = 'sid_'.request()->sid.'_'.$key;
        }
        return $this->redis->sAdd($key, $val);
    }


    /**
     * @notes 移除集合成员
     * @param $key
     * @param $val
     * @param boolean $Sidtag
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:04
     */
    public function srem($key, $val,$Sidtag = true)
    {
        if(true === $Sidtag){
            $key = 'sid_'.request()->sid.'_'.$key;
        }
        return $this->redis->sRem($key, $val);
    }

    /**
     * @notes 对象转数组
     * @param $key
     * @param boolean $Sidtag
     * @return array|false
     * @author 段誉
     * @date 2021/12/20 12:03
     */
    public function getSmembersArray($key,$Sidtag = true)
    {
        $res = $this->sMembers($key,$Sidtag);
        if (is_object($res)) {
            return (array)$res;
        }
        return $res;
    }


    /**
     * @notes 相似keys
     * @param $prefix
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function keys($prefix)
    {
        return $this->redis->keys($prefix.'*');
    }

}