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
namespace abc\controllers\storefront;
use abc\core\engine\AController;

if (! defined ( 'DIR_APP' )) {
	header ( 'Location: assets/static_pages/' );
}
class ControllerCommonFooterTop extends AController {
	public function main() {
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->processTemplate('common/footer_top.tpl');
        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
}