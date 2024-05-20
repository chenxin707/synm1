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

use app\common\{enum\ShopPageEnum,
    enum\ThemePageEnum,
    service\ThemeService,
    service\WeChatService,
    model\DecorateThemePage};


/**
 * 主题页面逻辑层
 * Class DecoratePageLogic
 * @package app\adminapi\logic\decorate
 */
class DecorateThemePageLogic
{

    /**
     * @notes 主题首页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/8/13 18:29
     */
    public function index():array
    {
        $index = DecorateThemePage::where(['is_home'=>1])
                ->field('id,name,create_time,update_time')
                ->find()
                ->toArray();

        $h5Uri = request()->domain().'/mobile'.ShopPageEnum::HOME_PAGE['mobile'];
        $res = WeChatService::makeMpQrCode(['page' =>substr(ShopPageEnum::HOME_PAGE['mobile'],1)],'base64');
        $mpBase64 = '';
        if(true == $res){
            $mpBase64 = WeChatService::getReturnData();
        }

        return [
            'home'  => $index,
            'mp'    => [
                'qr_code'   => $mpBase64,
            ],
            'oa'    => [
                'uri'       => $h5Uri,
//                'qr_code'   => $oaBase64,
            ],
        ];
    }

    /**
     * @notes 主题页设置为主页
     * @param int $id
     * @return bool
     * @author cjhao
     * @date 2021/8/6 16:33
     */
    public function setHome(int $id): bool
    {
        DecorateThemePage::where(['is_home' => 1])->update(['is_home' => 0]);
        DecorateThemePage::where(['id' => $id])->update(['is_home' => 1]);
        return true;
    }


    /**
     * @notes 新增装修页面
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/8/5 10:58
     */
    public function add(array $params): int
    {
        $id = DecorateThemePage::order('id desc')->value('id');
        $decoratePage = new DecorateThemePage();
        $decoratePage->theme_id = 1;//todo 后面整合主题功能时需要修改
        $decoratePage->name     = '默认页面'.$id+1;
        $decoratePage->type     = $params['type'] ?? 1;
        $decoratePage->content  = $params['content'] ?? [];
        $decoratePage->common   = $params['common'] ?? [];
        $decoratePage->duokaiSave();
        return $decoratePage->id;
    }

    /**
     * @notes 获取装修页面
     * @param int $id
     * @return array
     * @author cjhao
     * @date 2021/8/5 10:59
     */
    public function getPage(array $params)
    {
        //todo 传id获取某个页面；传type获取某个类型的页面
        if (isset($params['id'])) {
            $where = ['id' => $params['id']];
        } else {
            $where = ['type' => $params['type']];
        }

        $page = DecorateThemePage::where($where)
            ->field('id,name,type,is_home,content,common')
            ->find()->toArray();

        if(ThemePageEnum::TYPE_HOME === $page['type']){
            $config['source'] = 'admin';

            $page['content'] = ThemeService::getModuleData($page['content'],$config);
        }
        return $page;
    }


    /**
     * @notes 更新装修页面
     * @param array $params
     * @return bool
     * @author cjhao
     * @date 2021/8/5 11:14
     */
    public function edit(array $params): array
    {
        DecorateThemePage::duokaiUpdate($params, [], ['name', 'content', 'common'], '', false);
        $isPreview = $params['is_preview'] ?? 0;
        $data = [];
        $url = '';
        if($isPreview){
            //TODO 暂时拿当前域名
            switch ($params['type']){
                case ThemePageEnum::TYPE_HOME:
                    if($params['is_home']){
                        $url = request()->domain().'/mobile'.ShopPageEnum::HOME_PAGE['mobile'];
                    }else{
                        $url = request()->domain().'/mobile'.ShopPageEnum::MICRO_PAGE['mobile'].'?id='.$params['id'];
                    }

                    break;
                case ThemePageEnum::TYPE_GOODS_CATEGORY:
                    $url = request()->domain().'/mobile'.ShopPageEnum::GOODS_CATEGORY_PAGE['mobile'];
                    break;
                case ThemePageEnum::TYPE_MEMBER_CENTRE:
                    $url = request()->domain().'/mobile'.ShopPageEnum::MEMBER_CENTRE_PAGE['mobile'];
                    break;
            }

            $data['url'] = $url;
        }

        return $data;

    }

    /**
     * @notes 删除主题页面
     * @param int $id
     * @return bool
     * @author cjhao
     * @date 2021/8/9 19:30
     */
    public function del(int $id): bool
    {
        DecorateThemePage::duokaiDestroy($id);
        return true;
    }


    /**
     * @notes 获取商城页面
     * @return array
     * @author cjhao
     * @date 2021/8/12 16:27
     */
    public function getShopPage(string $type):array
    {
        $pageList = ShopPageEnum::SHOP_PAGE;
        $list = [];
        foreach ($pageList as $page){
            if($type === $page['type']){
                $list[] = $page;
            }
        }
        return $list;

    }


}