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

namespace app\adminapi\validate\marketing;


use app\common\enum\GoodsEnum;
use app\common\model\Goods;
use app\common\validate\BaseValidate;

/**
 * 拼团活动数据验证器
 * Class TeamValidate
 * @package app\adminapi\validate\marketing
 */
class TeamValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'require|number',
        'name'       => 'require|min:2',
//        'min_buy'    => 'require|number',
        'max_buy'    => 'require|number',
        'people_num' => 'require|number|peopleMin',
        'start_time' => 'require|dateFormat:Y-m-d H:i:s',
        'end_time'   => 'require|dateFormat:Y-m-d H:i:s',
        'effective_time'  => 'require|gt:0',
//        'is_coupon'       => 'require|in:0,1',
//        'is_distribution' => 'require|in:0,1',
        'is_automatic'    => 'require|in:0,1',
        'goods'           => 'require|array|checkGoods'
    ];

    protected $message = [
        'id.require'         => '缺少id字段',
        'name.require'       => '请输入活动名称',
        'min_buy.require'    => '请选择或填写起购限制',
        'max_buy.require'    => '请选择或填写每单限制',
        'start_time.require' => '请选择活动开始时间',
        'end_time.require'   => '请选择活动结束时间',
        'people_num.require' => '请输入成团所需人数',
        'people_num.min'     => '成团人数不能少于2人',
        'effective_time.require'  => '请输入成团有效期',
        'effective_time.gt'  => '成团有效期须大于0',
        'is_coupon.require'       => '请选择是否允许使用优惠券',
        'is_distribution.require' => '请选择是否参与分销',
        'is_automatic.require'    => '请选择是否自动虚拟成团',
    ];

    /**
     * @notes 添加场景验证
     * @return TeamValidate
     * @author 张无忌
     * @date 2021/8/2 14:36
     */
    public function sceneAdd()
    {
        return $this->remove('id', 'require');
    }

    /**
     * @notes 只验证id参数
     * @return TeamValidate
     * @author 张无忌
     * @date 2021/8/2 14:36
     */
    public function sceneId()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 验证活动商品合法性
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 张无忌
     * @date 2021/7/27 14:49
     */
    public function checkGoods($value, $rule, $data)
    {
        unset($value);
        unset($rule);
        if (empty($data['goods'])) {
            return '拼团商品不可为空';
        }

        if (count($data['goods']) > 25) {
            return '选择的商品不能超过25个';
        }
        $goodsList = Goods::column('type','id');

        foreach ($data['goods'] as $item) {
            if (empty($item)) {
                return '商品规格不可为空';
            }
            if (!is_numeric($item['goods_id']) || $item['goods_id'] <=0) {
                return '商品格式异常';
            }
            $goodsType = $goodsList[$item['goods_id']] ?? '';
            if(empty($goodsType)){
                return '商品信息错误';
            }
            if(GoodsEnum::GOODS_VIRTUAL == $goodsType){
                return '虚拟商品不能参加营销活动';
            }
            foreach ($item['items'] as $val) {
                if (empty($val)) {
                    return '缺少规格相关参数';
                }
                if (empty($val['item_id'])) {
                    return '规格中缺少item_id参数';
                }
                if (empty($val['team_price'])) {
                    return '规格中缺少team_price参数';
                }
            }
            if (isset($item['virtual_sales_num']) && $item['virtual_sales_num'] != '' && (!is_numeric($item['virtual_sales_num']) || $item['virtual_sales_num'] <=0)) {
                return '虚拟销量值错误';
            }
            if (isset($item['virtual_click_num']) && $item['virtual_click_num'] != '' && (!is_numeric($item['virtual_click_num']) || $item['virtual_click_num'] <=0)) {
                return '虚拟浏览量值错误';
            }
        }
        return true;
    }

    /**
     * @notes 验证参团人数
     * @param $value
     * @return bool|string
     * @author 张无忌
     * @date 2021/10/14 9:56
     */
    public static function peopleMin($value)
    {
        if ($value <= 1) {
            return '拼团人数最少需设2人起';
        }

        return true;
    }
}