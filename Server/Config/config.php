<?php
return [
    'route_model' => 1, //1代表pathinfo,2代表普通url模式
    'default_route' => 'site/index',
    'app_namespace' => 'App',
    'not_found_template' => '404.html', //默认是在View文件夹下面
    'session_start' =>false,
    'components' => [
        'view' => [
            'class' => 'Swoolefy\Core\View',
        ],

        'log' => [
            'class' => 'Swoolefy\Tool\Log',
        ],
    ],
];