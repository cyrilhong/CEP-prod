<?php


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');
if (!defined('WP_ROOT')) {
    //define('WP_ROOT' , $_SERVER['DOCUMENT_ROOT']);
    define('WP_ROOT', ABSPATH);
}
if (!defined('DR')) {
    define('DR', DIRECTORY_SEPARATOR);
}
//include_once( WP_ROOT . DR . 'wp-load.php' );
global $wpdb;
////////////////////////////////constants section//////////////////////////////////////
if (!defined('WS_ALIPAY_NAME_EN')) {
    define('WS_ALIPAY_NAME_EN', 'alipay');
}
if (!defined('ALIPAY_SETTINGS_PAGE')) {
    define('ALIPAY_SETTINGS_PAGE', WS_ALIPAY_NAME_EN . "/includes/tpl.settings.php");
}
if (!defined('ALIPAY_MENU_SLUG')) {
    define('ALIPAY_MENU_SLUG', 'ws_alipay');
}
if (!defined('ALIPAY_SETTINGS_LINK')) {
    define('ALIPAY_SETTINGS_LINK', admin_url() . 'options-general.php?page=' . ALIPAY_MENU_SLUG);
}
if (!defined('ALIPAY_NAME')) {
    define('ALIPAY_NAME', '集成支付宝');
}
if (!defined('ALIPAY_AUTH')) {
    define('ALIPAY_AUTH', 'administrator');
}
if (!defined('ALIPAY_SETTINGS_TITLE')) {
    define('ALIPAY_SETTINGS_TITLE', ALIPAY_NAME . '控制面板');
}
if (!defined('ALIPAY_BASENAME')) {
    define('ALIPAY_BASENAME', WS_ALIPAY_NAME_EN . "/alipay.php");
}
if (!defined('WS_ALIPAY_DB_PREFIX')) {
    define('WS_ALIPAY_DB_PREFIX', $wpdb->prefix . 'ws_alipay_');
}
if (!defined('WS_ALIPAY_CHARSET')) {
    define('WS_ALIPAY_CHARSET', get_bloginfo('charset'));
}
if (!defined('WS_DS')) {
    define('WS_DS', DIRECTORY_SEPARATOR);
}
if (!defined('WS_ALIPAY_ROOT')) {
    define('WS_ALIPAY_ROOT', dirname(dirname(__FILE__)) . WS_DS);
}
if (!defined('WS_ALIPAY_INC')) {
    define('WS_ALIPAY_INC', WS_ALIPAY_ROOT . 'includes' . WS_DS);
}
if (!defined('WS_ALIPAY_URL')) {
    define('WS_ALIPAY_URL', WP_PLUGIN_URL . "/" . WS_ALIPAY_NAME_EN);
}
if (!defined('WS_ALIPAY_IMG_URL')) {
    define('WS_ALIPAY_IMG_URL', WS_ALIPAY_URL . '/styles/images');
}
include_once('fnc.core.php');
include_once('fnc.api_core.php');
//////////////////////////////db section//////////////////////////////////////
$wpdb->wsaliprefix            = $wpdb->prefix . 'ws_alipay_';
$wpdb->wsaliproductsname      = 'products';
$wpdb->wsaliproducts          = $wpdb->wsaliprefix . 'products';
$wpdb->wsaliproductsmeta      = $wpdb->wsaliprefix . 'products' . 'meta';
$wpdb->wsaliproductsmetatype  = 'wsali' . 'products';
$wpdb->wsaliordersname        = 'orders';
$wpdb->wsaliorders            = $wpdb->wsaliprefix . 'orders';
$wpdb->wsaliordersmeta        = $wpdb->wsaliprefix . 'orders' . 'meta';
$wpdb->wsaliordersmetatype    = 'wsali' . 'orders';
$wpdb->wsalitemplatesname     = 'templates';
$wpdb->wsalitemplates         = $wpdb->wsaliprefix . 'templates';
$wpdb->wsalitemplatesmeta     = $wpdb->wsaliprefix . 'templates' . 'meta';
$wpdb->wsalitemplatesmetatype = 'wsali' . 'templatets';
$ws_alipay_tables             = array(
    $wpdb->wsaliproductsname,
    $wpdb->wsaliordersname,
    $wpdb->wsalitemplatesname
);
ws_alipay_db_create();
date_default_timezone_set('UTC');
foreach ($ws_alipay_tables as $table) {
    $temp    = array();
    $tbl     = $wpdb->wsaliprefix . $table;
    $tmpData = $wpdb->get_results("SHOW FIELDS FROM $tbl;", ARRAY_A);
    foreach ($tmpData as $k => $arr) {
        $temp[] = $arr['Field'];
    }
    ${'ws_alipay_table_' . $table} = $temp;
    //$ws_alipay_table_pruducts
    //$ws_alipay_table_orders
    //$ws_alipay_table_templates
}

//############################################################################
//PATH
//USAGE:
//require_once( WS_ALIPAY_INC . 'fnc.api_core.php' );
//$dirParent = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
//require_once( $dirParent . 'cfg.config.php' );
