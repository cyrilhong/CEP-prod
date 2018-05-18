<?php
//include my core functions
include_once('fnc.core.php');
//add the menu
add_action('admin_menu', 'ws_alipay_menu_constructor');
//init
add_action('init', 'ws_alipay_init');
//admin_init
add_action('admin_init', 'ws_alipay_admin_init');
//register the taxonomy
//add_action( 'admin_init', 'ws_alipay_register_taxonomy' );
//load the languages pack
add_action('init', 'ws_alipay_languages');
//add a short code of my plugin for pages
add_shortcode('zfb', 'ws_alipay_shortcode_parser');
//dos with first active my plugin
register_activation_hook(__FILE__, 'ws_alipay_activate');
add_action('init', 'ws_alipay_request_handle');

