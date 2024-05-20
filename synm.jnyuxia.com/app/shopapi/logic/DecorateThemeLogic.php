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
namespace app\shopapi\logic;

use app\common\{
    enum\ThemePageEnum,
    enum\YesNoEnum,
    model\Distribution,
    model\SelffetchVerifier,
    service\ThemeService,
    enum\ThemeConfigEnum,
    model\DecorateThemePage,
    model\DistributionConfig,
    model\DecorateThemeConfig};

/**
 * 装修主题逻辑层
 * Class DecorateThemeLogic
 * @package app\shopapi\logic
 */
class DecorateThemeLogic
{

    /**
     * @notes 主题首页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/12 18:35
     */
    public function index(int $userId)
    {
        $homePage = DecorateThemePage::where(['is_home' => 1])
            ->field('id,content,common')
            ->find()->toArray();
        //替换组件内容
        $config = $this->getMenuConfig($userId);
        $config['user_id'] = $userId;
        $homePage['content'] = ThemeService::getModuleData($homePage['content'],$config);
        return $homePage;

    }

    /**
     * @notes 获取主题配置
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/13 16:00
     */
    public function getConfig()
    {

        $themeConfig = DecorateThemeConfig::where(['type'=>[ThemeConfigEnum::TYPE_THEME_COLOR,ThemeConfigEnum::TYPE_BOTTOM_NA,ThemeConfigEnum::TYPE_OPEN_AD]])
                ->field('content')->select()->toArray();

        //转换数据结构，方便前端渲染
        $configList = [];
        foreach ($themeConfig as $configVal){
            $configKey = array_Keys($configVal['content']);
            $configList[$configKey[0]] = array_values($configVal['content'])[0];
        }

        return $configList;
    }


    /**
     * @notes 主题页面
     * @param array $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/12 15:02
     */
    public function getPage(int $type,$userId,$goodsId)
    {

        $page = DecorateThemePage::where(['type' => $type])
            ->field('id,content,common')
            ->findOrEmpty()->toArray();

        //用户中心，判断是否需要屏蔽分销中心入口
        if(ThemePageEnum::TYPE_MEMBER_CENTRE == $type || ThemePageEnum::TYPE_HOME == $type){
            $config = $this->getMenuConfig($userId);
        }
        $config['page_type'] = $type;
        $config['user_id'] = $userId;
        $config['goods_id'] = $goodsId;

        $page['content'] = ThemeService::getModuleData($page['content'],$config);


        return $page;
    }

    /**
     * @notes 获取首页微页面
     * @param int $id
     * @return array
     * @author cjhao
     * @date 2021/9/1 14:53
     */
    public function getIndexPage(int $id):array
    {
        $page = DecorateThemePage::where(['id' => $id,'type'=>ThemePageEnum::TYPE_HOME])
            ->field('id,content,common')
            ->findOrEmpty()->toArray();
        //替换组件内容
        $page['content'] = ThemeService::getModuleData($page['content']);
        return $page;
    }


    /**
     * @notes 获取菜单配置 （是否屏蔽核销员、是否屏蔽核销菜单）
     * @param int $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/9/27 18:09
     */
    public function getMenuConfig(int $userId):array
    {
        $config['is_verifier'] = false;
        $config['is_distribution'] = false;

        $distributionConfig = DistributionConfig::column('value','key');
        $switch = $distributionConfig['switch'] ?? 0;//是否开启分销功能
        $open = $distributionConfig['open'] ?? 1;   //
        //分销开启，但是指定用户分销，且当前用户不是分销用户
        if(1 == $switch){
            if(3 == $open){
                $userDistribution = Distribution::where(['user_id'=>$userId,'is_distribution'=>1])->find();
                $userDistribution && $config['is_distribution']  = true;
            }else{
                $config['is_distribution']  = true;
            }
        }

        $userVerifier = SelffetchVerifier::where(['user_id'=>$userId,'status'=>YesNoEnum::YES])
            ->find();
        //用户为核销员
        if($userVerifier){
            $config['is_verifier'] = true;
        }

        return $config;
    }
}