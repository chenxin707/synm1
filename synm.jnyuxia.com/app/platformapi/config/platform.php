<?php
/**
 * 平台管理后台配置文件
 */
return [
    //唯一标识，密码盐、路径加密等
    'unique_identification' => env('project.unique_identification', 'likeshop'),

    //平台后台管理员token（登录令牌）配置
    'platform_admin_token' => [
        'expire_duration' => 3600 * 8,//token过期时长(单位秒）
        'be_expire_duration' => 3600,//token临时过期前时长，自动续期
    ],
    // 登录安全限制
    'login' => [
        // 是否开启登录安全限制 0-不开启 1-开启
        'login_restrictions' => 1,
        // 密码输入错误次数限制
        'password_error_times' => 3,
        // 限制登录分钟数
        'limit_login_time' => 5,
    ],
    // 默认图片
    'default_image' => [
        'platform_admin_avatar' => 'resource/image/platformapi/default/avatar.png',
        'platform_ico' => 'resource/image/platformapi/default/favicon.ico',
        'platform_login_image' => 'resource/image/platformapi/default/login.png',
        'paltform_ico_example' => 'resource/image/platformapi/default/ico_example.png',
        'paltform_login_image_example' => 'resource/image/platformapi/default/login_image_example.png',
    ],
    'platform_name' => '平台管理中心',
];