<?php
/**
 * Class Map of default stage
 */

use abc\core\engine\AAttribute;
use abc\core\lib\ACart;
use abc\core\lib\ACustomer;
use abc\core\lib\AJobManager;
use abc\core\lib\AJson;
use abc\core\lib\ALog as ALog;
use abc\core\lib\ABackup as ABackup;
use abc\core\lib\AOrder;
use abc\core\lib\AOrderManager;
use abc\core\lib\APromotion;
use abc\core\lib\AResourceManager;
use Illuminate\Events\Dispatcher as EventDispatcher;

return [
    'AViewRender'     => \abc\core\view\AViewDefaultRender::class,
    'ALog'            => [
        ALog::class,
        [

            'app'      => 'application.log',
            'security' => 'security.log',
            'warn'     => 'application.log',
            'debug'    => 'debug.log',
        ],
    ],
    'AResourceManager'=> AResourceManager::class,
    'ABackup'         => ABackup::class,
    'AJobManager'     => AJobManager::class,
    'AJson'           => AJson::class,
    'ACustomer'       => ACustomer::class,
    'AAttribute'      => AAttribute::class,
    'APromotion'      => APromotion::class,
    'ACart'           => ACart::class,
    'AOrder'          => AOrder::class,
    'AOrderManager'   => AOrderManager::class,
    'EventDispatcher' => [EventDispatcher::class, null],
];
