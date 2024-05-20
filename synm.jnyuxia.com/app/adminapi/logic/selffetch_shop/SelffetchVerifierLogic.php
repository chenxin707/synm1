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

namespace app\adminapi\logic\selffetch_shop;


use app\common\logic\BaseLogic;
use app\common\model\SelffetchVerifier;
use app\common\service\FileService;

/**
 * 核销员逻辑层
 * Class SelffetchVerifierLogic
 * @package app\adminapi\logic\selffetch_shop
 */
class SelffetchVerifierLogic extends BaseLogic
{
    /**
     * @notes 添加核销员
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/11 7:24 下午
     */
    public function add($params)
    {
        $selffetch_verifier = new SelffetchVerifier;
        $selffetch_verifier->selffetch_shop_id = $params['selffetch_shop_id'];
        $selffetch_verifier->user_id = $params['user_id'];
        $selffetch_verifier->sn = create_number_sn((new SelffetchVerifier()),'sn',8);
        $selffetch_verifier->name = $params['name'];
        $selffetch_verifier->mobile = $params['mobile'];
        $selffetch_verifier->status = $params['status'];
        return $selffetch_verifier->duokaiSave();
    }

    /**
     * @notes 编辑核销员
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 7:37 下午
     */
    public function edit($params)
    {
        $selffetch_verifier = SelffetchVerifier::find($params['id']);
        $selffetch_verifier->selffetch_shop_id = $params['selffetch_shop_id'];
        $selffetch_verifier->user_id = $params['user_id'];
        $selffetch_verifier->name = $params['name'];
        $selffetch_verifier->mobile = $params['mobile'];
        $selffetch_verifier->status = $params['status'];
        return $selffetch_verifier->duokaiSave();
    }

    /**
     * @notes 核销员详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 7:49 下午
     */
    public function detail($params)
    {
        $info = SelffetchVerifier::withoutGlobalScope()
            ->aliasSid('sv')
            ->alias('sv')
            ->join('user u', 'sv.user_id = u.id')
            ->join('selffetch_shop ss', 'ss.id = sv.selffetch_shop_id')
            ->field('sv.*,u.nickname,u.avatar,ss.name as shop_name')
            ->where('sv.id',$params['id'])
            ->find()
            ->toArray();

        $info['avatar'] = trim($info['avatar']) ? FileService::getFileUrl($info['avatar']) : '';
        return $info;
    }

    /**
     * @notes 修改核销员状态
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/8/11 8:21 下午
     */
    public function status($params)
    {
        $selffetch_verifier = SelffetchVerifier::find($params['id']);
        $selffetch_verifier->status = $params['status'];
        return $selffetch_verifier->duokaiSave();
    }

    /**
     * @notes 删除核销员
     * @param $params
     * @return bool
     * @author ljj
     * @date 2021/8/11 8:27 下午
     */
    public function del($params)
    {
        return SelffetchVerifier::duokaiDestroy($params['id']);
    }
}