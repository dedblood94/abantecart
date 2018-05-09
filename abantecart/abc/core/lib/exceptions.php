<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\core\lib;

use abc\core\ABC;

if (!class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

require_once(ABC::env('DIR_LIB').'exceptions/aexception.php');
require_once(ABC::env('DIR_LIB').'exceptions/exception.php');
require_once(ABC::env('DIR_LIB').'exceptions/handler.php');
require_once(ABC::env('DIR_LIB').'exceptions/exception_codes.php');

$config = [
    'debug' => (ABC::env('DEBUG') ? true : false),
];
new AHandleExceptions($config, new AExceptionHandler($config['debug']));
