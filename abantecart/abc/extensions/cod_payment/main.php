<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2018 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 */

if (!class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

$controllers = array(
    'storefront' => array('responses/extension/cod_payment'),
    'admin'      => array(),
);

$models = array(
    'storefront' => array('extension/cod_payment'),
    'admin'      => array(),
);

$languages = array(
    'storefront' => array(
        'cod_payment/cod_payment',
    ),
    'admin'      => array(
        'cod_payment/cod_payment',
    ),
);

$templates = array(
    'storefront' => array(
        'responses/cod_payment.tpl',
    ),
    'admin'      => array(),
);