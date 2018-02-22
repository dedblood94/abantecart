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

namespace install\controllers;

use abc\core\ABC;
use abc\core\engine\AController;

class ControllerPagesFinish extends AController
{
    public function main()
    {
        if ( ! ABC::env('DATABASES')) {
            header('Location: index.php?rt=license');
            exit;
        }

        $this->session->data['finish'] = 'true';
        unset($this->session->data ['ant_messages']); // prevent reinstall bugs with ant

        $this->view->assign('admin_secret', 'index.php?s='.ABC::env('ADMIN_SECRET'));

        $message = "Keep your e-commerce secure! <br /> Delete directory ".ABC::env('DIR_INSTALL')." install from your AbanteCart installation!";
        $this->view->assign('message', $message);

        $this->addChild('common/header', 'header', 'common/header.tpl');
        $this->addChild('common/footer', 'footer', 'common/footer.tpl');

        $this->processTemplate('pages/finish.tpl');
    }

}