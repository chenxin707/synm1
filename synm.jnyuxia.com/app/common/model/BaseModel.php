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


namespace app\common\model;

use app\common\service\FileService;
use think\Collection;
use think\Model;
use app\common\trait;

/**
 * 基础模型
 * Class BaseModel
 * @package app\common\model
 */
class BaseModel extends Model
{
    use trait\DuokaiSoftDelete;

    protected $deleteTime = 'delete_time';

    // 当前访问的商户ID
    public static $sid;

    // 定义全局查询范围
    protected $globalScope = ['sid'];

    /**
     * @notes 初始化
     * @author Tab
     * @date 2021/12/13 17:41
     */
    public static function init()
    {
        parent::init();
        // 获取商户ID
        self::getSid();
    }

    /**
     * @notes sid查询范围
     * @param $query
     * @return bool
     * @author Tab
     * @date 2021/12/13 18:13
     */
    public function scopeSid($query)
    {
        $query->where('sid', self::$sid);
        return true;
    }

    /**
     * @notes 带别名的sid
     * 使用场景：join连表查询时
     * 用法：先关闭原有的全局查询范围，再手动添加带别名的查询范围，别名可以填参与连表的任一数据表的别名
     * @author Tab
     * @date 2021/12/16 11:33
     */
    public function scopeAliasSid($query, $alias)
    {
        $query->where($alias . '.sid', self::$sid);
        return true;
    }

    /**
     * @notes 关闭全局查询范围
     * @author Tab
     * @date 2021/12/15 10:10
     */
    public function duokaiWithoutGlobalScope()
    {
        $this->globalScope = [];

        return $this;
    }

    /**
     * @notes 获取当前操作的商户id
     * @author Tab
     * @date 2021/12/13 17:46
     */
    public static function getSid()
    {
        self::$sid = request()->sid;
        return self::$sid;
    }

    /**
     * @notes 多开save
     * 新增
     * 原版使用方法一：实例模型，模型属性赋值，save()
     * 多开版使用方法一: 实例模型，模型属性赋值，doukaiSave()  会自动写入sid，不受全局查询范围影响
     * 原版使用方法二：实例模型,创建属性一维数组$data, save($data)
     * 多开版使用方法二：实例模型,创建属性一维数组$data, duokaiSave($data) 会自动写入sid，不受全局查询范围影响
     * 更新(注意事项：根据条件查询获取不到模型时，会自动识别为是新增操作)
     * 原版使用方法：根据条件查询获得模型，模型属性赋值，save()
     * 多开版使用方法：根据条件查询获得模型，模型属性赋值，doukaiSave() 受全局范围影响，保证更新的是当前商户的数据
     * @param array $data
     * @param string|null $sequence
     * @param bool $scopeOpen 是否打开全局查询范围
     * @param bool $writeSid  是否自动写入sid
     * @return bool
     * @author Tab
     * @date 2021/12/16 16:11
     */
    public function duokaiSave(array $data = [], string $sequence = null, bool $scopeOpen = true, bool $writeSid = true): bool
    {
        if (!$scopeOpen) {
            // 关闭全局查询范围
            $this->globalScope = [];
        }

        if ($writeSid) {
            // 自动写入sid
            $data['sid'] = request()->sid;
        }

        return $this->save($data, $sequence);
    }

    /**
     * @notes 多开saveAll
     * 批量新增
     * 原版使用方法：实例模型,创建属性二维数组$data,saveAll($data)
     * 多开版使用方法：实例模型,创建属性二维数组$data,duokaiSaveAll($data) 会自动写入sid，不受全局查询范围影响
     * 批量更新(需改动TP底层saveAll、update方法来控制关闭全局查询范围，重写方法中的关闭全局查询范围不生效)
     * 原版使用方法：实例模型,创建属性二维数组$data,含主键id, saveAll($data)
     * 多开版使用方法：实例模型,创建属性二维数组$data,含主键id, duokaiSaveAll($data) 一定要关闭全局查询范围才会生效，否则报错
     * @param iterable $dataSet
     * @param bool $replace
     * @param bool $scopeOpen 是否开启全局查询
     * @param bool $writeSid  是否自动写入sid
     * @return Collection
     * @throws \Exception
     * @author Tab
     * @date 2021/12/17 9:38
     */
    public function duokaiSaveAll(iterable $dataSet, bool $replace = true, bool $scopeOpen = true, bool $writeSid = true): Collection
    {
        if (!$scopeOpen) {
            // 关闭全局查询范围
            $this->globalScope = [];
        }

        $newDataSet = $dataSet;
        if ($writeSid) {
            $newDataSet = [];
            // 自动写入sid
            foreach ($dataSet as $item) {
                $item['sid'] = request()->sid;
                $newDataSet[] = $item;
            }
        }

        return $this->saveAll($newDataSet, $replace, $scopeOpen);
    }

    /**
     * @notes 多开create
     * 原版使用方法：创建一维数组$data,create($data)
     * 多开版使用方法：创建一维数组$data,duokaiCreate($data) 会自动写入sid，不受全局查询范围影响
     * @param array $data
     * @param array $allowField
     * @param bool $replace
     * @param string $suffix
     * @param bool $writeSid 是否自动写入sid
     * @return Model
     * @author Tab
     * @date 2021/12/16 16:53
     */
    public static function duokaiCreate(array $data, array $allowField = [], bool $replace = false, string $suffix = '', bool $writeSid = true): Model
    {
        if ($writeSid) {
            // 自动写入sid
            $data['sid'] = request()->sid;
        }

        return self::create($data, $allowField, $replace, $suffix);
    }

    /**
     * @notes 多开update
     * 带主键的但不带查询条件的更新需要关闭全局查询
     * 带查询条件的更新需开启全局查询
     * @param array $data
     * @param array $where
     * @param array $allowField
     * @param string $suffix
     * @param bool $scopeOpen
     * @return BaseModel|void
     * @author Tab
     * @date 2021/12/17 14:59
     */
    public static function duokaiUpdate(array $data, $where = [], array $allowField = [], string $suffix = '', bool $scopeOpen = true)
    {
        self::update($data, $where, $allowField, $suffix, $scopeOpen);
    }

    // ==================================================================

    /**
     * @notes 公共处理图片,补全路径
     * @param $value
     * @return string
     * @author 张无忌
     * @date 2021/9/10 11:02
     */
    public function getImageAttr($value)
    {
        return trim($value) ? FileService::getFileUrl($value) : '';
    }

    /**
     * @notes 公共图片处理,去除图片域名
     * @param $value
     * @return mixed|string
     * @author 张无忌
     * @date 2021/9/10 11:04
     */
    public function setImageAttr($value)
    {
        return trim($value) ? FileService::setFileUrl($value) : '';
    }
}