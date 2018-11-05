<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 05/11/2018
 * Time: 12:05
 */

namespace abc\controllers\storefront;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\helper\AHelperUtils;
use abc\core\lib\APromotion;
use abc\core\engine\AResource;

if (!class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

/**
 * Class ControllerPagesProductSpecial
 *
 * @package abc\controllers\storefront
 * @property \abc\models\storefront\ModelCatalogReview $model_catalog_review
 */
class ControllerPagesProductFeatured extends AController
{

    public $data = array();

    /**
     * Check if HTML Cache is enabled for the method
     *
     * @return array - array of data keys to be used for cache key building
     */
    public static function main_cache_keys()
    {
        return array('page', 'limit', 'sort', 'order');
    }

    public function main()
    {

        $request = $this->request->get;

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('product/featured');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->resetBreadcrumbs();
        $this->document->addBreadcrumb(array(
            'href'      => $this->html->getHomeURL(),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ));


        if ($this->config->get('config_require_customer_login') && !$this->customer->isLogged()) {
            abc_redirect($this->html->getSecureURL('account/login'));
        }

        $url = '';
        if (isset($request['page'])) {
            $url .= '&page='.$request['page'];
        }

        $this->document->addBreadcrumb(array(
            'href'      => $this->html->getNonSecureURL('product/featured', $url),
            'text'      => $this->language->get('heading_title'),
            'separator' => $this->language->get('text_separator'),
        ));

        if (isset($request['page'])) {
            $page = $request['page'];
        } else {
            $page = 1;
        }

        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
            $limit = $limit > 50 ? 50 : $limit;
        } else {
            $limit = $this->config->get('config_catalog_limit');
        }

        if (isset($request['sort'])) {
            $sorting_href = $request['sort'];
        } else {
            $sorting_href = $this->config->get('config_product_default_sort_order');
        }
        list($sort, $order) = explode("-", $sorting_href);
        if ($sort == 'name') {
            $sort = 'pd.'.$sort;
        } elseif ($sort == 'sort_order') {
            $sort = 'p.'.$sort;
        } elseif ($sort == 'price') {
            $sort = 'ps.'.$sort;
        }

        $this->loadModel('catalog/product');
        $promotion = new APromotion();

        $product_total =  $this->model_catalog_product->getFeaturedProducts(['total' => true]);


        if ($product_total) {
            $product_total = count($product_total);
            $this->loadModel('catalog/review');
            $this->loadModel('tool/seo_url');
            $this->loadModel('tool/image');

            $this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');

            $results = $this->model_catalog_product->getFeaturedProducts([
                'sort' => $sort,
                'order' => $order,
                'start' => ($page - 1) * $limit,
                'limit' => $limit,
                ]
            );

            $product_ids = array();
            foreach ($results as $result) {
                $product_ids[] = (int)$result['product_id'];
            }

            //Format product data specific for confirmation page
            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'products',
                $product_ids,
                $this->config->get('config_image_product_width'),
                $this->config->get('config_image_product_height')
            );
            $stock_info = $this->model_catalog_product->getProductsStockInfo($product_ids);

            $this->data['is_customer'] = false;
            if ($this->customer->isLogged() || $this->customer->isUnauthCustomer()) {
                $this->data['is_customer'] = true;
                $whishlist = $this->customer->getWishList();
            }

            foreach ($results as $result) {
                $thumbnail = $thumbnails[$result['product_id']];
                if ($this->config->get('enable_reviews')) {
                    $rating = $this->model_catalog_review->getAverageRating($result['product_id']);
                } else {
                    $rating = false;
                }

                $special = false;
                $discount = $promotion->getProductDiscount($result['product_id']);
                if ($discount) {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $discount,
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        )
                    );
                } else {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $result['price'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        )
                    );
                    $special = $promotion->getProductSpecial($result['product_id']);
                    if ($special) {
                        $special = $this->currency->format(
                            $this->tax->calculate($special, $result['tax_class_id'], $this->config->get('config_tax'))
                        );
                    }
                }

                $options = $this->model_catalog_product->getProductOptions($result['product_id']);
                if ($options) {
                    $add = $this->html->getSEOURL('product/product', '&product_id='.$result['product_id'], '&encode');
                } else {
                    if ($this->config->get('config_cart_ajax')) {
                        $add = '#';
                    } else {
                        $add = $this->html->getSecureURL(
                            'checkout/cart',
                            '&product_id='.$result['product_id'],
                            '&encode'
                        );
                    }
                }

                //check for stock status, availability and config
                $track_stock = false;
                $in_stock = false;
                $no_stock_text = $this->language->get('text_out_of_stock');
                $total_quantity = 0;
                $stock_checkout = $result['stock_checkout'] === ''
                    ? $this->config->get('config_stock_checkout')
                    : $result['stock_checkout'];
                if ($stock_info[$result['product_id']]['subtract']) {
                    $track_stock = true;
                    $total_quantity = $stock_info[$result['product_id']]['quantity'];
                    //we have stock or out of stock checkout is allowed
                    if ($total_quantity > 0 || $stock_checkout) {
                        $in_stock = true;
                    }
                }

                $in_wishlist = false;
                if ($whishlist && $whishlist[$result['product_id']]) {
                    $in_wishlist = true;
                }

                $this->data['products'][] = array(
                    'product_id'     => $result['product_id'],
                    'name'           => $result['name'],
                    'model'          => $result['model'],
                    'rating'         => $rating,
                    'stars'          => sprintf($this->language->get('text_stars'), $rating),
                    'price'          => $price,
                    'raw_price'      => $result['price'],
                    'call_to_order'  => $result['call_to_order'],
                    'options'        => $options,
                    'special'        => $special,
                    'thumb'          => $thumbnail,
                    'href'           => $this->html->getSEOURL(
                        'product/product',
                        '&product_id='.$result['product_id'],
                        '&encode'
                    ),
                    'add'            => $add,
                    'description'    => html_entity_decode($result['description'], ENT_QUOTES, ABC::env('APP_CHARSET')),
                    'blurb'          => $result['blurb'],
                    'track_stock'    => $track_stock,
                    'in_stock'       => $in_stock,
                    'no_stock_text'  => $no_stock_text,
                    'total_quantity' => $total_quantity,
                    'tax_class_id'   => $result['tax_class_id'],
                    'in_wishlist'   => $in_wishlist,
                    'product_wishlist_add_url' => $this->html->getURL(
                        'product/wishlist/add',
                        '&product_id='.$result['product_id']
                    ),
                    'product_wishlist_remove_url' => $this->html->getURL(
                        'product/wishlist/remove',
                        '&product_id='.$result['product_id']
                    ),
                );
            }

            if ($this->config->get('config_customer_price')) {
                $display_price = true;
            } elseif ($this->customer->isLogged()) {
                $display_price = true;
            } else {
                $display_price = false;
            }
            $this->data['display_price'] = $display_price;

            $sorts = array();
            $sorts[] = array(
                'text'  => $this->language->get('text_default'),
                'value' => 'p.sort_order-ASC',
                'href'  => $this->html->getURL('product/featured', $url.'&sort=p.sort_order&order=ASC', '&encode'),
            );

            $sorts[] = array(
                'text'  => $this->language->get('text_sorting_name_asc'),
                'value' => 'pd.name-ASC',
                'href'  => $this->html->getURL('product/featured', $url.'&sort=pd.name&order=ASC', '&encode'),
            );

            $sorts[] = array(
                'text'  => $this->language->get('text_sorting_name_desc'),
                'value' => 'pd.name-DESC',
                'href'  => $this->html->getURL('product/featured', $url.'&sort=pd.name&order=DESC', '&encode'),
            );

            $sorts[] = array(
                'text'  => $this->language->get('text_sorting_price_asc'),
                'value' => 'p.price-ASC',
                'href'  => $this->html->getURL('product/featured', $url.'&sort=price&order=ASC', '&encode'),
            );

            $sorts[] = array(
                'text'  => $this->language->get('text_sorting_price_desc'),
                'value' => 'p.price-DESC',
                'href'  => $this->html->getURL('product/featured', $url.'&sort=price&order=DESC', '&encode'),
            );

            $sort_options = array();
            foreach ($sorts as $item) {
                $sort_options[$item['value']] = $item['text'];
            }
            $sorting = $this->html->buildElement(
                array(
                    'type'    => 'selectbox',
                    'name'    => 'sort',
                    'options' => $sort_options,
                    'value'   => $sort.'-'.$order,
                )
            );

            $this->view->assign('sorting', $sorting);
            $this->view->assign('url', $this->html->getURL('product/featured'));

            $this->data['sorts'] = $sorts;
            $pagination_url = $this->html->getURL(
                'product/featured',
                '&sort='.$sorting_href.'&page={page}'.'&limit='.$limit,
                '&encode'
            );

            $this->data['pagination_bootstrap'] = $this->html->buildElement(
                array(
                    'type'       => 'Pagination',
                    'name'       => 'pagination',
                    'text'       => $this->language->get('text_pagination'),
                    'text_limit' => $this->language->get('text_per_page'),
                    'total'      => $product_total,
                    'page'       => $page,
                    'limit'      => $limit,
                    'url'        => $pagination_url,
                    'style'      => 'pagination',
                ));

            $this->data['sort'] = $sort;
            $this->data['order'] = $order;
            $this->data['review_status'] = $this->config->get('enable_reviews');
            $this->view->batchAssign($this->data);
            $this->view->setTemplate('pages/product/featured.tpl');
        } else {
            $this->view->assign('text_error', $this->language->get('text_empty'));
            $continue = $this->html->buildElement(
                array(
                    'type'  => 'button',
                    'name'  => 'continue_button',
                    'text'  => $this->language->get('button_continue'),
                    'style' => 'button',
                ));
            $this->view->assign('button_continue', $continue);
            $this->view->assign('continue', $this->html->getHomeURL());
            $this->view->setTemplate('pages/error/not_found.tpl');
        }
        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}
