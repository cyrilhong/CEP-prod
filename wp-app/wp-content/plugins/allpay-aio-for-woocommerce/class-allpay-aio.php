<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Allpay AIO Payment Gateway
 * Plugin Name: Allpay AIO for Woocommerce
 * Plugin URI: http://innovext.com
 * Description: Woocommerce 歐付寶/綠界全方位金流模組
 * Version: 1.1.7
 * Text Domain: allpay-aio-for-woocommerce
 * Author URI: contact@innovext.com
 * Author: 因創科技
 */
add_action('plugins_loaded', 'innovext_allpay_aio_gateway_init', 200);

function innovext_allpay_aio_gateway_init() {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once 'includes/class-wc-gateway-allpay-aio.php';
    require_once 'includes/class-wc-gateway-allpay-aio-addon.php';

    add_filter('woocommerce_payment_gateways', 'add_innovext_allpay_aio_gateway');
    load_plugin_textdomain('allpay-aio-for-woocommerce', FALSE, basename(dirname(__FILE__)) . '/languages/');
}

/**
 * Add the gateway to WooCommerce
 *
 * @access public
 * @param array $methods
 * @package WooCommerce/Classes/Payment
 * @return array
 */
function add_innovext_allpay_aio_gateway($available_gateways) {
    $available_gateways[] = 'WC_innovext_allpay_aio';
    return $available_gateways;
}