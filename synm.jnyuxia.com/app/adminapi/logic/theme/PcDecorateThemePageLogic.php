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
namespace app\adminapi\logic\theme;
use app\common\{enum\PcDecorateThemePageEnum, enum\ShopPageEnum, model\PcDecorateThemePage};

/**
 * pc装修逻辑层
 * Class PcDecorateThemePageLogic
 * @package app\adminapi\logic\theme
 */
class PcDecorateThemePageLogic
{
    /**
     * @notes 获取首页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/11/30 11:59
     */
    public function index()
    {

        $index = PcDecorateThemePage::where(['type'=>PcDecorateThemePageEnum::TYPE_HOME])->field('id,name,create_time,update_time')
            ->find()
            ->toArray();

        //todo 暂时拿当前域名
        $uri = request()->domain().'/pc';

        return [
            'home'      => $index,
            'uri'       => $uri
        ];
    }


    /**
     * @notes 获取页面
     * @param int $type 页面类型
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/11/30 12:00
     */
    public function getPage(int $type)
    {

        $page = PcDecorateThemePage::where(['type' => $type])
            ->field('id,name,type,content,common')
            ->find()
            ->toArray();

        return $page;

    }

    /**
     * @notes 设置页面
     * @param array $param
     * @return PcDecorateThemePage
     * @author cjhao
     * @date 2021/11/30 14:44
     */
    public function setPage(array $param)
    {
        return PcDecorateThemePage::where(['type'=>$param['type']])
                    ->update($param);
    }



    /**
     * @notes 获取pc页面
     * @param string $type
     * @return array
     * @author cjhao
     * @date 2021/12/6 16:57
     */
    public function getPcPage(string $type):array
    {
        $pageList = ShopPageEnum::PC_SHOP_PAGE;
        $list = [];
        foreach ($pageList as $page){
            if($type === $page['type']){
                $list[] = $page;
            }
        }
        return $list;
    }


}