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

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\engine\AForm;
use abc\core\engine\AResource;

if (!defined ( 'DIR_APP' ) || !IS_ADMIN) {
	header('Location: assets/static_pages/');
}

class ControllerPagesCatalogProductRelations extends AController{
	private $error = array ();
	public $data = array ();

	public function main(){

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$product_id = (int)$this->request->get['product_id'];
		$language_id = $this->language->getContentLanguageID();

		$this->loadLanguage('catalog/product');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->loadModel('catalog/product');

		if ($product_id && ($this->request->is_GET())) {
			$product_info = $this->model_catalog_product->getProduct($product_id);
			if (!$product_info) {
				$this->session->data['warning'] = $this->language->get('error_product_not_found');
				abc_redirect($this->html->getSecureURL('catalog/product'));
			}
		}

		if ($this->request->is_POST()) {
			$this->model_catalog_product->updateProductLinks($product_id, $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			abc_redirect($this->html->getSecureURL('catalog/product_relations', '&product_id=' . $product_id));
		}

		$this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$this->document->initBreadcrumb(array (
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false
		));
		$this->document->addBreadcrumb(array (
				'href'      => $this->html->getSecureURL('catalog/product'),
				'text'      => $this->language->get('heading_title'),
				'separator' => ' :: '
		));
		$this->document->addBreadcrumb(array (
				'href'      => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $product_id),
				'text'      => $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product') . ' - ' . $this->data['product_description'][$language_id]['name'],
				'separator' => ' :: '
		));
		$this->document->addBreadcrumb(array (
				'href'      => $this->html->getSecureURL('catalog/product_relations', '&product_id=' . $product_id),
				'text'      => $this->language->get('tab_relations'),
				'separator' => ' :: ',
				'current'   => true
		));

		$this->loadModel('catalog/category');
		$this->data['categories'] = array ();
		$results = $this->model_catalog_category->getCategories(0);
		foreach ($results as $r) {
			$this->data['categories'][$r['category_id']] = $r['name'];
		}

		$this->loadModel('setting/store');
		$this->data['stores'] = array (0 => $this->language->get('text_default'));
		$results = $this->model_setting_store->getStores();
		foreach ($results as $r) {
			$this->data['stores'][$r['store_id']] = $r['name'];
		}

		$this->data['product_category'] = $this->model_catalog_product->getProductCategories($product_id);
		$this->data['product_store'] = $this->model_catalog_product->getProductStores($product_id);
		$this->data['product_related'] = $this->model_catalog_product->getProductRelated($product_id);

		$this->data['active'] = 'relations';
		//load tabs controller
		$tabs_obj = $this->dispatch('pages/catalog/product_tabs', array ($this->data));
		$this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
		unset($tabs_obj);

		$this->data['category_products'] = $this->html->getSecureURL('product/product/category');
		$this->data['related_products'] = $this->html->getSecureURL('product/product/related');
		$this->data['action'] = $this->html->getSecureURL('catalog/product_relations', '&product_id=' . $product_id);
		$this->data['form_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');
		$this->data['update'] = $this->html->getSecureURL('listing_grid/product/update_relations_field',
				'&id=' . $product_id);
		$form = new AForm('HS');

		$form->setForm(array (
				'form_name' => 'productFrm',
				'update'    => $this->data['update'],
		));

		$this->data['form']['id'] = 'productFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(array (
				'type'   => 'form',
				'name'   => 'productFrm',
				'action' => $this->data['action'],
				'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"',
		));
		$this->data['form']['submit'] = $form->getFieldHtml(array (
				'type'  => 'button',
				'name'  => 'submit',
				'text'  => $this->language->get('button_save'),
				'style' => 'button1',
		));
		$this->data['form']['cancel'] = $form->getFieldHtml(array (
				'type'  => 'button',
				'href'  => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $product_id),
				'name'  => 'cancel',
				'text'  => $this->language->get('button_cancel'),
				'style' => 'button2',
		));
		$this->data['cancel'] = $this->html->getSecureURL('catalog/product');

		$this->loadModel('catalog/category');
		$this->data['categories'] = array ();
		$results = $this->model_catalog_category->getCategories(0);

		foreach ($results as $r) {
			$this->data['categories'][$r['category_id']] = $r['name'];
		}

		$this->data['form']['fields']['category'] = $form->getFieldHtml(array (
				'type'        => 'checkboxgroup',
				'name'        => 'product_category[]',
				'value'       => $this->data['product_category'],
				'options'     => $this->data['categories'],
				'style'       => 'chosen',
				'placeholder' => $this->language->get('text_select_category'),
		));
		//load only prior saved products
		$this->data['products'] = array ();
		if (count($this->data['product_related'])) {
			$ids = array ();
			foreach ($this->data['product_related'] as $id) {
				$ids[] = (int)$id;
			}

			$this->loadModel('catalog/product');
			$filter = array ('subsql_filter' => 'p.product_id in (' . implode(',', $ids) . ')');
			$results = $this->model_catalog_product->getProducts($filter);

			$product_ids = array ();
			foreach ($results as $result) {
				$product_ids[] = (int)$result['product_id'];
			}
			//get thumbnails by one pass
			$resource = new AResource('image');
			$thumbnails = $resource->getMainThumbList(
					'products',
					$product_ids,
					$this->config->get('config_image_grid_width'),
					$this->config->get('config_image_grid_height')
			);

			foreach ($results as $r) {
				$thumbnail = $thumbnails[$r['product_id']];
				$this->data['products'][$r['product_id']]['name'] = $r['name'] . " (" . $r['model'] . ")";
				$this->data['products'][$r['product_id']]['image'] = $thumbnail['thumb_html'];
			}
		}

		$this->data['form']['fields']['related'] = $form->getFieldHtml(array (
				'type'        => 'multiselectbox',
				'name'        => 'product_related[]',
				'value'       => $this->data['product_related'],
				'options'     => $this->data['products'],
				'style'       => 'chosen',
				'ajax_url'    => $this->html->getSecureURL('r/product/product/products', '&exclude[]=' . $product_id),
				'placeholder' => $this->language->get('text_select_from_lookup'),
		));

		$this->data['form']['fields']['store'] = $form->getFieldHtml(array (
				'type'    => 'checkboxgroup',
				'name'    => 'product_store[]',
				'value'   => $this->data['product_store'],
				'options' => $this->data['stores'],
				'style'   => 'chosen',
		));
		if ($this->config->get('config_embed_status')) {
			$this->data['embed_url'] = $this->html->getSecureURL('common/do_embed/product',
					'&product_id=' . $product_id);
		}
		$this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');
		$this->view->assign('help_url', $this->gen_help_url('product_relations'));
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/catalog/product_relations.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
}