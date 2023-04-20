<?php

return [
    'debug' => false,

    // 不需要验证登录的控制器
    'no_login_controller' => [
        'login',
    ],

    // 不需要验证登录的节点
    'no_login_node'       => [
        'login/index',
        'login/out',
    ],

    // 不需要验证权限的控制器
    'no_auth_controller'  => [
        'ajax',
        'login',
        'index',
    ],

    // 不需要验证权限的节点
    'no_auth_node'        => [
        'login/index',
        'login/out',
    ],
    
    // 跳转页面的成功模板文件
    'dispatch_success_tmpl'   => app_path()  . DIRECTORY_SEPARATOR . 'common/tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    // 跳转页面的失败模板文件
    'dispatch_error_tmpl'   => app_path()  . DIRECTORY_SEPARATOR . 'common/tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
];