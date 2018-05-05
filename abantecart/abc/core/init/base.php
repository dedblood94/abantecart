<?php

use abc\core\ABC;

$class_list = [
    'core/engine' => [
        'router',
        'page',
        'response',
        'api',
        'task',
        'dispatcher',
        'controller',
        'controller_api',
        'loader',
        'model',
        'registry',
        'resources',
        'html',
        'layout',
        'form',
        'extensions',
        'hook',
        'attribute',
        'language',
    ],
    'core/cache'  => [
        'cache',
    ],
    'core/view'   => [
        'view',
    ],
    'core/helper' => [
        'global',
        'helper',
        'html',
        'utils',
        'system_check',
    ],
    'core/lib'    => [
        'config',
        'db',
        'connect',
        'document',
        'image',
        'log',
        'mail',
        'message',
        'pagination',
        'request',
        'response',
        'session',
        'template',
        'xml2array',
        'data',
        'file',
        'download',
        'customer',
        'order',
        'order_status',
        'currency',
        'tax',
        'weight',
        'length',
        'cart',
        'user',
        'dataset',
        'encryption',
        'menu_control',
        'menu_control_storefront',
        'rest',
        'filter',
        'listing',
        'task_manager',
        'im',
        'csrf_token',
        'promotion',
        'json',
    ],
];
//load classes

$dir_app = ABC::env('DIR_APP');
require_once $dir_app.'core'.DS.'lib'.DS.'libBase.php';

foreach ($class_list as $sub_dir => $files) {
    $sub_dir = DS != '/' ? str_replace('/', DS, $sub_dir) : $sub_dir;
    foreach ($files as $name) {
        require_once $dir_app.$sub_dir.DS.$name.'.php';
    }
}

unset($class_list);

//load vendors classes
@include(ABC::env('DIR_VENDOR').'autoload.php');
