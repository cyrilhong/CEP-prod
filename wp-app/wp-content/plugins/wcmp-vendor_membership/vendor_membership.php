<?php

/**
 * Plugin Name: WCMp Vendor Membership
 * Plugin URI: https://wc-marketplace.com
 * Description: Create unlimited numbers of vendor levels and control their accessibilities and capabilities on your site.
 * Author: WC Marketplace
 * Version: 1.0.9
 * Author URI: https://wc-marketplace.com
 * 
 * Text Domain: wcmp-vendor_membership
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('WCMp_Vendor_Membership_Dependencies')) {
    require_once 'includes/class-wcmp-vendor-membership-dependencies.php';
}
require_once 'includes/wcmp-vendor-membership-core-functions.php';
require_once 'vendor_membership_config.php';

if (!defined('WCMP_VENDOR_MEMBERSHIP_PLUGIN_TOKEN')) {
    exit;
}
if (!defined('WCMP_VENDOR_MEMBERSHIP_TEXT_DOMAIN')) {
    exit;
}
if (!WCMp_Vendor_Membership_Dependencies::woocommerce_plugin_active_check()) {
    add_action('admin_notices', 'wvm_woocommerce_inactive_notice');
}

if (!WCMp_Vendor_Membership_Dependencies::WCMp_plugin_active_check()) {
    add_action('admin_notices', 'wvm_wcmp_inactive_notice');
}
if (class_exists('WCMp')) {
    if (!class_exists('WCMP_Vendor_Membership')) {
        if (!isset($_SESSION)) {
            session_start();
        }
        require_once( 'classes/class-wcmp-vendor-membership.php' );
        global $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership = new WCMP_Vendor_Membership(__FILE__);
        $GLOBALS['WCMP_Vendor_Membership'] = $WCMP_Vendor_Membership;
        // Activation Hooks
        register_activation_hook(__FILE__, array('WCMP_Vendor_Membership', 'activate_wcmp_vendor_membership'));
        register_activation_hook(__FILE__, 'flush_rewrite_rules');
        // Deactivation Hooks
        register_deactivation_hook(__FILE__, array('WCMP_Vendor_Membership', 'deactivate_wcmp_vendor_membership'));
    }
}