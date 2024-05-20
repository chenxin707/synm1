<?php
namespace app\adminapi\controller\poster;

use app\adminapi\controller\BaseAdminController;
use app\common\logic\PosterLogic;

/**
 * 自定义海报
 */
class PosterController extends BaseAdminController
{
    /**
     * @notes 获取商品海报配置
     */
    public function getGoodsConfig()
    {
        $id = request()->get('id');
        $config = PosterLogic::getGoodsConfig($id);
        return $this->data($config);
    }

    /**
     * @notes 设置商品海报配置
     */
    public function setGoodsConfig()
    {
        $params = request()->post();
        $config = PosterLogic::setGoodsConfig($params);
        return $this->success('设置成功', [], 1, 1);
    }

    /**
     * @notes 获取邀请海报配置
     */
    public function getDistributionConfig()
    {
        $config = PosterLogic::getDistributionConfig();
        return $this->data($config);
    }

    /**
     * @notes 设置邀请海报配置
     */
    public function setDistributionConfig()
    {
        $params = request()->post();
        $config = PosterLogic::setDistributionConfig($params);
        return $this->success('设置成功', [], 1, 1);
    }
}
