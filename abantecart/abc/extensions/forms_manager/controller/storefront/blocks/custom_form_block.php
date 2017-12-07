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
use abc\ABC;
use abc\core\engine\AController;
use abc\core\engine\AForm;

if (!class_exists('abc\ABC')) {
	header('Location: static_pages/?forbidden='.basename(__FILE__));
}

/**
 * Class ControllerBlocksCustomFormBlock
 * @property \abc\models\storefront\ModelToolFormsManager $model_tool_forms_manager
 */
class ControllerBlocksCustomFormBlock extends AController {

	public $data = array();
	protected $validators = '';
	protected $validated_types;

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this,__FUNCTION__);

		$this->validated_types = array(
			'D' => 'date',
			'E' => 'email',
			'N' => 'number',
			'F' => 'phone',
			'A' => 'ipaddress'
		);

		$this->loadLanguage('forms_manager/forms_manager');

		$instance_id = func_get_arg(0);
		$block_data = $this->getBlockContent($instance_id);
		$this->view->assign('block_framed',$block_data['block_framed']);
		$this->view->assign('content',$block_data['content']);
		$this->view->assign('heading_title', $block_data['title'] );
		$this->view->assign('stat_url', $this->html->getURL('r/extension/banner_manager') );
		$this->view->assign('error_required', $this->language->get('error_required'));
		$this->view->assign('template_dir', ABC::env('RDIR_TEMPLATE'));

		$this->view->batchAssign($this->data);

		if($block_data['content']){

			$this->document->addScript(ABC::env('DIR_EXTENSIONS') . 'forms_manager'.ABC::env('DIRNAME_STORE').'js/form_check.js');

			// need to set wrapper for non products listing blocks
			if($this->view->isTemplateExists($block_data['block_wrapper'])){
				$this->view->setTemplate( $block_data['block_wrapper'] );
			}
			$this->processTemplate();
		}
		//init controller data
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

	protected function getBlockContent($instance_id) {

		$block_info = $this->layout->getBlockDetails($instance_id);
		$custom_block_id = $block_info['custom_block_id'];
		$descriptions = $this->layout->getBlockDescriptions($custom_block_id);

		if ( $descriptions[$this->config->get('storefront_language_id')] ) {
			$key = $this->config->get('storefront_language_id');
		} else {
			$key = $descriptions ? key($descriptions) : null;
		}

		if ( $descriptions[$key]['content'] ) {
			$content = unserialize($descriptions[$key]['content']);
		} else {
			$content = array('form_id' => null);
		}

		$this->loadModel('tool/forms_manager');
		$form_data = $this->model_tool_forms_manager->getForm($content['form_id']);

		if ( empty($form_data) ) {
			return array();
		}

		$form = new AForm();
		$this->data['form_name'] = $form_data['form_name'];
		$form->loadFromDb($form_data['form_name']);

		$form_info = $form->getForm();
		$form_info['controller'] = $form_info['controller'] . '&form_id=' . $content['form_id'];
		$form->setForm($form_info);

		if ( isset($this->session->data['custom_form_'.$content['form_id']]['errors']) ) {
			$form->setErrors($this->session->data['custom_form_'.$content['form_id']]['errors']);
			unset($this->session->data['custom_form_'.$content['form_id']]['errors']);
		}

		$output = array(
			'title' => ( $key ? $descriptions[$key]['title'] : '' ),
			'content' => $form->getFormHtml(),
			'block_wrapper' => ( $key ? $descriptions[$key]['block_wrapper'] : 0 ),
			'block_framed' => ( $key ? (int)$descriptions[$key]['block_framed'] : 0 ),
		);

		return $output;
	}


}
