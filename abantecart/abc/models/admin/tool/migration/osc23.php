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

namespace abc\models\admin;

use abc\core\ABC;
use abc\core\lib\ADB;

if ( ! class_exists('abc\core\ABC') || ! \abc\core\ABC::env('IS_ADMIN')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}
require_once 'interface_migration.php';

class Migration_Osc23 implements Migration
{

    private $data;
    private $config;
    /**
     * @var \abc\core\lib\ADB
     */
    private $src_db;
    private $error_msg;
    private $language_id_src;

    function __construct($migrate_data, $oc_config)
    {
        $this->config = $oc_config;
        $this->data = $migrate_data;
        $this->error_msg = "";
        /**
         * @var \abc\core\lib\ADB
         */
        if ($migrate_data) {
            $db_config = [
                'DB_DRIVER'   => 'mysql',
                'DB_HOST'     => $this->data['db_host'],
                'DB_NAME'     => $this->data['db_name'],
                'DB_USER'     => $this->data['db_user'],
                'DB_PASSWORD' => $this->data['db_password'],
            ];
            $this->src_db = new ADB($db_config);
        }
    }

    public function getName()
    {
        return 'OsCommerce';
    }

    public function getVersion()
    {
        return '2.3';
    }

    private function getSourceLanguageId()
    {
        if ( ! $this->language_id_src) {
            $result = $this->src_db->query("SELECT languages_id AS language_id
                                            FROM ".$this->data['db_prefix']."languages
                                            WHERE `code` = (SELECT `configuration_value`
                                                            FROM ".$this->data['db_prefix']."configuration
                                                            WHERE `configuration_key`='DEFAULT_LANGUAGE');");
            $this->language_id_src = $result->row['language_id'];
        }

        return $this->language_id_src;
    }

    public function getCategories()
    {
        $this->error_msg = "";

        // for now use default language
        $languages_id = $this->getSourceLanguageId();

        $categories_query
            = "SELECT	c.categories_id AS category_id,
                                    cd.categories_name AS name,
                                    '' AS description,
                                    c.categories_image AS image,
                                    c.parent_id,
                                    c.sort_order
                                FROM ".$this->data['db_prefix']."categories c, ".$this->data['db_prefix']."categories_description cd
                                WHERE c.categories_id = cd.categories_id AND cd.language_id = '".(int)$languages_id."'
                                ORDER BY c.sort_order, cd.categories_name";
        $categories = $this->src_db->query($categories_query, true);
        if ( ! $categories) {
            $this->error_msg = 'Migration Error: '.$this->src_db->error.'<br>';

            return false;
        }

        $result = array();
        foreach ($categories->rows as $item) {
            $result[$item['category_id']] = $item;
            $item['image'] = trim($item['image']);
            $result[$item['category_id']]['image'] = array();
            if ($item['image']) {
                $img_uri = $this->data['cart_url'];
                if (substr($img_uri, -1) != '/') {
                    $img_uri .= '/';
                }
                $img_uri .= 'images/';
                $result[$item['category_id']]['image']['db'] = str_replace(' ', '%20', $img_uri.pathinfo($item['image'], PATHINFO_BASENAME));
            }
        }

        return $result;
    }

    public function getManufacturers()
    {
        $this->error_msg = "";

        $sql_query
            = "SELECT manufacturers_id AS manufacturer_id, manufacturers_name AS name, manufacturers_image AS image
                      FROM ".$this->data['db_prefix']."manufacturers
                      ORDER BY manufacturers_name";
        $items = $this->src_db->query($sql_query, true);
        if ( ! $items) {
            $this->error_msg = 'Migration Error: '.$this->src_db->error.'<br>';

            return false;
        }

        $result = array();
        foreach ($items->rows as $item) {
            $result[$item['manufacturer_id']] = $item;
            $item['image'] = trim($item['image']);
            $result[$item['manufacturer_id']]['image'] = array();
            if ($item['image']) {
                $img_uri = $this->data['cart_url'];
                if (substr($img_uri, -1) != '/') {
                    $img_uri .= '/';
                }
                $img_uri .= 'images/';
                $result[$item['manufacturer_id']]['image']['db'] = str_replace(' ', '%20', $img_uri.pathinfo($item['image'], PATHINFO_BASENAME));
            }
        }

        return $result;
    }

    public function getProducts()
    {
        $this->error_msg = "";
        // for now use default language
        $languages_id = $this->getSourceLanguageId();

        $products_query
            = "SELECT   p.products_id AS product_id,
                                    p.products_model AS model,
                                    p.products_quantity AS quantity,
                                    '7' AS stock_status_id,
                                    p.products_image AS image,
                                    p.manufacturers_id AS manufacturer_id,
                                    '1' AS shipping,
                                    p.products_price AS price,
                                    pd.products_name AS name,
                                    pd.products_description AS description,
                                    '9' AS tax_class_id,
                                    p.products_date_available AS date_available,
                                    p.products_weight AS weight,
                                    '5' AS weight_class_id,
                                    p.products_status AS status,
                                    p.products_date_added AS date_added
                            FROM
                                ".$this->data['db_prefix']."products p,
                                ".$this->data['db_prefix']."products_description pd
                            WHERE
                                pd.products_id = p.products_id
                                AND pd.language_id = '".(int)$languages_id."'";
        $items = $this->src_db->query($products_query, true);
        if ( ! $items) {
            $this->error_msg = 'Migration Error: '.$this->src_db->error.'<br>';

            return false;
        }

        $result = array();
        foreach ($items->rows as $item) {
            $result[$item['product_id']] = $item;
            $item['image'] = trim($item['image']);
            $result[$item['product_id']]['image'] = array();
            if ($item['image']) {
                $img_uri = $this->data['cart_url'];
                if (substr($img_uri, -1) != '/') {
                    $img_uri .= '/';
                }
                $img_uri .= 'images/';
                $result[$item['product_id']]['image']['db'] = str_replace(' ', '%20', $img_uri.$item['image']);
                //additional images
                $imgs = $this->src_db->query("SELECT products_id AS product_id, image
                                                FROM ".$this->data['db_prefix']."products_images
                                                WHERE products_id = '".$item['product_id']."' ORDER BY products_id");
                foreach ($imgs->rows as $img) {
                    $uri = str_replace(' ', '%20', $img_uri.$img['image']);
                    if ( ! in_array($uri, $result[$img['product_id']]['image'])) {
                        $result[$img['product_id']]['image'][] = $uri;
                    }
                }
            }
        }

        //add categories id
        $sql_query
            = "SELECT categories_id AS category_id, products_id AS product_id
                      FROM ".$this->data['db_prefix']."products_to_categories";
        $items = $this->src_db->query($sql_query, true);
        if ( ! $items) {
            $this->error_msg = 'Migration Error: '.$this->src_db->error.'<br>';

            return false;
        }
        foreach ($items->rows as $item) {
            if ( ! empty($result[$item['product_id']])) {
                $result[$item['product_id']]['product_category'][] = $item['category_id'];
            }
        }

        return $result;
    }

    public function getCustomers()
    {
        $this->error_msg = "";
        $customers_query
            = "SELECT  c.customers_id AS customer_id,
                                    c.customers_firstname AS firstname,
                                    c.customers_lastname lastname,
                                    c.customers_email_address AS email,
                                    c.customers_telephone AS telephone,
                                    c.customers_fax AS fax,
                                    c.customers_password AS password,
                                    c.customers_newsletter AS newsletter
                            FROM ".$this->data['db_prefix']."customers c ";

        $customers = $this->src_db->query($customers_query, true);
        if ( ! $customers) {
            $this->error_msg = 'Migration Error: '.$this->src_db->error.'<br>';

            return false;
        }

        $result = array();
        foreach ($customers->rows as $customer) {
            $result[$customer['customer_id']] = $customer;
        }

        // add customers addresses
        $address_query
            = "SELECT a.customers_id AS customer_id,
                                a.entry_company AS company,
                                a.entry_firstname AS firstname,
                                a.entry_lastname AS lastname,
                                a.entry_street_address AS address_1,
                                a.entry_postcode AS postcode,
                                a.entry_city AS city,
                                a.entry_zone_id AS zone_id,
                                a.entry_country_id AS country_id
                          FROM ".$this->data['db_prefix']."address_book a ";
        $addresses = $this->src_db->query($address_query, true);
        if ( ! $addresses) {
            $this->error_msg = 'Migration Error: '.$this->src_db->error.'<br>';

            return false;
        }

        foreach ($addresses->row as $address) {
            $result[$address['customer_id']]['address'][] = $address;
        }

        return $result;
    }

    public function getOrders()
    {
        return array();
    }

    public function getErrors()
    {
        return $this->error_msg;
    }

    public function getCounts()
    {
        $products = $this->src_db->query("SELECT COUNT(*) AS cnt FROM ".$this->data['db_prefix']."products", true);
        $categories = $this->src_db->query("SELECT COUNT(*) AS cnt FROM ".$this->data['db_prefix']."categories", true);
        $manufacturers = $this->src_db->query("SELECT COUNT(*) AS cnt FROM ".$this->data['db_prefix']."manufacturers", true);
        $customers = $this->src_db->query("SELECT COUNT(*) AS cnt FROM ".$this->data['db_prefix']."customers", true);

        return array(
            'products'      => (int)$products->row['cnt'],
            'categories'    => (int)$categories->row['cnt'],
            'manufacturers' => (int)$manufacturers->row['cnt'],
            'customers'     => (int)$customers->row['cnt'],
        );
    }
}