<?php
function uninstall_schema(){
global $wpdb;
$android_schema = "DROP TABLE IF EXISTS `mofluidadmin_android`";	
$wpdb->query($android_schema);

$query_1 = "DROP TABLE IF EXISTS `mofluidadmin_details`";	
$wpdb->query($query_1);

$iphone_schema = "DROP TABLE IF EXISTS `mofluidadmin_iphone`";	
$wpdb->query($iphone_schema);

$query_2 = "DROP TABLE IF EXISTS `mofluidadmin_details`";	
$wpdb->query($query_2);

$payment_methods = "DROP TABLE IF EXISTS `mofluidpayment`";	
$wpdb->query($payment_methods);

$mofluid_build_accounts = "DROP TABLE IF EXISTS `mofluid_build_accounts`";	
$wpdb->query($mofluid_build_accounts);

$mofluidpush_settings = "DROP TABLE IF EXISTS `mofluidpush_settings`";	
$wpdb->query($mofluidpush_settings);

}
