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

namespace app\adminapi\lists\marketing;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\CouponEnum;
use app\common\lists\ListsExcelInterface;
use app\common\lists\ListsSearchInterface;
use app\common\model\CouponList;
use app\common\service\FileService;

class CouponRecordLists extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{
    /**
     * @notes 设置导出字段
     * @return string[]
     * @author heshihu
     * @date 2021/9/22 18:39
     */
    public function setExcelFields(): array
    {
        return [
            'id'                => '优惠券ID',
            'name'              => '优惠券名称',
            'nickname'          => '用户昵称',
            'coupon_code'       => '已领卷卷码',
            'invalid_time'      => '过期时间',
            'status_text'       => '已领劵状态',
            'use_time'          => '使用时间',
            'create_time'       => '领取时间',
        ];
    }

    /**
     * @notes 设置导出文件名
     * @return string
     * @author heshihu
     * @date 2021/9/22 18:39
     */
    public function setFileName(): string
    {
        return '领取记录列表';
    }


    /**
     * @notes 优惠券记录搜索条件
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:08
     */
    public function setSearch(): array
    {
        return [
            '=' => ['CL.status'],
            '%like%' => ['U.nickname', 'U.sn', 'C.name', 'C.sn'],
            "between_time" => 'CL.create_time'
        ];
    }

    /**
     * @notes 获取优惠券记录列表
     * @return array
     * @author 张无忌
     * @date 2021/7/29 18:08
     */
    public function lists(): array
    {
        $lists = (new CouponList())->duokaiWithoutGlobalScope()
            ->aliasSid('CL')
            ->alias('CL')
            ->field([
                'C.id, CL.id as cl_id, CL.order_id, CL.user_id',
                'CL.coupon_code,C.name,U.avatar,U.sn,U.nickname,CL.status',
                'CL.use_time,CL.invalid_time,CL.create_time'
            ])
            ->where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->join('coupon C', 'C.id = CL.coupon_id')
            ->join('user U', 'U.id = CL.user_id')
            ->order('CL.id desc')
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['avatar'] = FileService::getFileUrl($item['avatar']);
            $item['use_time'] = !$item['use_time'] ? 0 : date('Y-m-d H:i:s', $item['use_time']);
            $item['invalid_time'] = date('Y-m-d H:i:s', $item['invalid_time']);
            $item['status_text'] = CouponEnum::getUseStatusDesc($item['status']);
        }

        return $lists;
    }

    /**
     * @notes 获取优惠券记录数量
     * @return int
     * @author 张无忌
     * @date 2021/7/29 18:08
     */
    public function count(): int
    {
        return (new CouponList())->duokaiWithoutGlobalScope()
            ->aliasSid('CL')
            ->alias('CL')
            ->field([
                'CL.coupon_code,C.name,U.avatar,U.nickname,CL.status',
                'CL.use_time,CL.invalid_time,CL.create_time'
            ])
            ->where($this->searchWhere)
            ->join('coupon C', 'C.id = CL.coupon_id')
            ->join('user U', 'U.id = CL.user_id')
            ->count();
    }
}