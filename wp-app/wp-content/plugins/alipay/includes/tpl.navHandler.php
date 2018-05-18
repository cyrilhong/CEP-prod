<?php


$_POST = stripslashes_deep($_POST);
if (!empty($_POST) && !check_admin_referer('ws_alipay_edit')) {
    die();
}
/*$user = wp_get_current_user();
if(!$user->has_cap('activate_plugins'))
	die();*/
if (ws_alipay_is_admin()) {
    if (empty($_GET['tab']) || $_GET['tab'] == 'products') {
        include_once('tpl.tab.products.php');
    } elseif ($_GET['tab'] == 'orders') {
        include_once('tpl.tab.orders.php');
    } elseif ($_GET['tab'] == 'templates') {
        include_once('tpl.tab.templates.php');
    } elseif ($_GET['tab'] == 'general') {
        include_once('tpl.tab.general.php');
    } else {
        include_once('tpl.tab.products.php');
    }
} else {
    include_once('tpl.tab.orders.php');
}
