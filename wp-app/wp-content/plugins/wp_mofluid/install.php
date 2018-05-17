<?php
function create_wp_presscart_schema(){
	global $wpdb;
	
$android_schema  = "CREATE TABLE IF NOT EXISTS `mofluidadmin_android` (
  `mofluidadmin_id` int(11) unsigned NOT NULL,
  `app_name` varchar(126) DEFAULT 'Woofluid',
  `bundle_id` varchar(126) NOT NULL DEFAULT 'com.woofluid.app',
  `version` varchar(126) NOT NULL DEFAULT '1.0.0',
  `default_store` int(16) NOT NULL DEFAULT '1',
  `release_key` varchar(255) DEFAULT '',
  `keystore_pswd` varchar(127) DEFAULT '',
  `privatekey_pswd` varchar(127) DEFAULT '',
  `key_status` int(16) NOT NULL DEFAULT '0',
  `key_data` varchar(255) DEFAULT '',
  `key_validity` int(16) NOT NULL DEFAULT '10000',
  `currency_type` varchar(6) NOT NULL DEFAULT 'USD',
  `ico_drawable` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable.png',
  `ico_hdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_hdpi.png',
  `ico_ldpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_ldpi.png',
  `ico_mdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_mdpi.png',
  `ico_xhdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_xhdpi.png',
  `drawable_port_hdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_hdpi.png',
  `drawable_port_ldpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_ldpi.png',
  `drawable_port_mdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_mdpi.png',
  `drawable_port_xhdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_xhdpi.png',
  `drawable_land_hdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_hdpi.png',
  `drawable_land_ldpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_ldpi.png',
  `drawable_land_mdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_mdpi.png',
  `drawable_land_xhdpi` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_xhdpi.png',
  `app_image_type` varchar(255) DEFAULT 'dark',
  `app_img_home` varchar(255) DEFAULT '',
  `app_img_back` varchar(255) DEFAULT '',
  `app_img_cross` varchar(255) DEFAULT '',
  `app_img_del` varchar(255) DEFAULT '',
  `app_img_cart` varchar(255) DEFAULT '',
  `app_img_menu` varchar(255) DEFAULT '',
  `app_img_search` varchar(255) DEFAULT '',
  `banner` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/logo_banner/banner.png',
  `logo` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/logo_banner/logo.png',
  `theme` varchar(63) DEFAULT 'Elegant',
  `theme_background` varchar(125) DEFAULT 'FFFFFF',
  `theme_text` varchar(125) DEFAULT 'CC0000',
  `theme_header` varchar(125) DEFAULT '1D4F17',
  `theme_title` varchar(125) DEFAULT 'BCFF03',
  `theme_button` varchar(125) DEFAULT 'FF4F2B',
  `customfooter` varchar(31) DEFAULT '',
  `customfooter_code` varchar(1024) DEFAULT '',
  `valid_app` int(16) NOT NULL DEFAULT '0',
  `valid_assets` int(16) NOT NULL DEFAULT '1',
  `valid_theme` int(16) NOT NULL DEFAULT '1',
  PRIMARY KEY (`mofluidadmin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$wpdb->query($android_schema);

$insert_query = "INSERT INTO `mofluidadmin_android` (`mofluidadmin_id`, `app_name`, `bundle_id`, `version`, `default_store`, `release_key`, `keystore_pswd`, `privatekey_pswd`, `key_status`, `key_data`, `key_validity`, `currency_type`, `ico_drawable`, `ico_hdpi`, `ico_ldpi`, `ico_mdpi`, `ico_xhdpi`, `drawable_port_hdpi`, `drawable_port_ldpi`, `drawable_port_mdpi`, `drawable_port_xhdpi`, `drawable_land_hdpi`, `drawable_land_ldpi`, `drawable_land_mdpi`, `drawable_land_xhdpi`, `app_image_type`, `app_img_home`, `app_img_back`, `app_img_cross`, `app_img_del`, `app_img_cart`, `app_img_menu`, `app_img_search`, `banner`, `logo`, `theme`, `theme_background`, `theme_text`, `theme_header`, `theme_title`, `theme_button`, `customfooter`, `customfooter_code`, `valid_app`, `valid_assets`, `valid_theme`) VALUES
(1, 'Woofluid', 'com.woofluid.app', '1.0.0', 1, '', '', '', 0, '', 10000, 'USD', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_hdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_ldpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_mdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/icons/drawable_xhdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_hdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_ldpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_mdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_port_xhdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_hdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_ldpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_mdpi.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/splash/drawable_land_xhdpi.png', 'dark', '', '', '', '', '', '', '', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/logo_banner/banner.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/Android/logo_banner/logo.png', 'Elegant', 'FFFFFF', 'CC0000', '1D4F17', 'BCFF03', 'FF4F2B', '0', '', 0, 1, 1)";
$wpdb->query($insert_query);

$query_1 = "CREATE TABLE IF NOT EXISTS `mofluidadmin_details` (
  `mofluidadmin_id` int(11) unsigned NOT NULL,
  `mofluid_id` varchar(128) DEFAULT '',
  `mofluid_key` varchar(63) NOT NULL DEFAULT '',
  `androidcount` int(16) NOT NULL DEFAULT '0',
  `iphonecount` int(16) NOT NULL DEFAULT '0',
  `desc` varchar(255) DEFAULT '',
  PRIMARY KEY (`mofluidadmin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$wpdb->query($query_1);
$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM mofluidadmin_android" );
$insert_query = "INSERT INTO `mofluidadmin_details` (`mofluidadmin_id`, `mofluid_id`, `mofluid_key`, `androidcount`, `iphonecount`, `desc`) VALUES
('".$update_app."', '', '', 0, 0, '')";
$wpdb->query($insert_query);
//}
//function create_wp_presscart_iphone_schema(){
//	global $wpdb;
	$admin_iphone = "CREATE TABLE IF NOT EXISTS `mofluidadmin_iphone` (
  `mofluidadmin_id` int(11) unsigned NOT NULL,
  `app_name` varchar(126) DEFAULT 'Woofluid',
  `bundle_id` varchar(126) NOT NULL DEFAULT 'com.woofluid.app',
  `version` varchar(126) NOT NULL DEFAULT '1.0.0',
  `default_store` int(16) NOT NULL DEFAULT '1',
  `link_set` varchar(511) DEFAULT '',
  `currency_type` varchar(6) NOT NULL DEFAULT 'USD',
  `icon_small` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_small.png',
  `icon_40` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_40.png',
  `icon_50` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_50.png',
  `icon_57` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon.png',
  `icon_60` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_60.png',
  `icon_72` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_72.png',
  `icon_76` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_76.png',
  `icon_small_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_small_2x.png',
  `icon_40_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_40_2x.png',
  `icon_50_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_50_2x.png',
  `icon_57_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_2x.png',
  `icon_60_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_60_2x.png',
  `icon_72_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_72_2x.png',
  `icon_76_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_76_2x.png',
  `itunesartwork` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/itunesartwork.png',
  `itunesartwork_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/itunesartwork_2x.png',
  `screen_iphone_portrait` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_iphone.png',
  `screen_iphone_portrait_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_2x_iphone.png',
  `screen_ipad_portrait` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Portrait_ipad.png',
  `screen_ipad_portrait_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Portrait_2x_ipad.png',
  `screen_ipad_landscape` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Landscape_ipad.png',
  `screen_ipad_landscape_2x` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Landscape_2x_ipad.png',
  `screen_iphone_default` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_568h_2x_iphone.png',
  `app_image_type` varchar(255) DEFAULT 'dark',
  `app_img_home` varchar(255) DEFAULT '',
  `app_img_back` varchar(255) DEFAULT '',
  `app_img_cross` varchar(255) DEFAULT '',
  `app_img_del` varchar(255) DEFAULT '',
  `app_img_cart` varchar(255) DEFAULT '',
  `app_img_menu` varchar(255) DEFAULT '',
  `app_img_search` varchar(255) DEFAULT '',
  `banner` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/logo_banner/banner.png',
  `logo` varchar(255) DEFAULT 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/logo_banner/logo.png',
  `theme` varchar(63) DEFAULT 'Elegant',
  `theme_background` varchar(125) DEFAULT 'FFFFFF',
  `theme_text` varchar(125) DEFAULT 'CC0000',
  `theme_header` varchar(125) DEFAULT '1D4F17',
  `theme_title` varchar(125) DEFAULT 'BCFF03',
  `theme_button` varchar(125) DEFAULT 'FF4F2B',
  `customfooter` varchar(31) DEFAULT '',
  `customfooter_code` varchar(1024) DEFAULT '',
  `valid_app` int(16) NOT NULL DEFAULT '0',
  `valid_pay` int(16) NOT NULL DEFAULT '1',
  `valid_assets` int(16) NOT NULL DEFAULT '1',
  `valid_theme` int(16) NOT NULL DEFAULT '1',
  `device_udid` varchar(500) DEFAULT '',
  PRIMARY KEY (`mofluidadmin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$wpdb->query($admin_iphone);
$insert_iphone = "INSERT INTO `mofluidadmin_iphone` (`mofluidadmin_id`, `app_name`, `bundle_id`, `version`, `default_store`, `link_set`, `currency_type`, `icon_small`, `icon_40`, `icon_50`, `icon_57`, `icon_60`, `icon_72`, `icon_76`, `icon_small_2x`, `icon_40_2x`, `icon_50_2x`, `icon_57_2x`, `icon_60_2x`, `icon_72_2x`, `icon_76_2x`, `itunesartwork`, `itunesartwork_2x`, `screen_iphone_portrait`, `screen_iphone_portrait_2x`, `screen_ipad_portrait`, `screen_ipad_portrait_2x`, `screen_ipad_landscape`, `screen_ipad_landscape_2x`, `screen_iphone_default`, `app_image_type`, `app_img_home`, `app_img_back`, `app_img_cross`, `app_img_del`, `app_img_cart`, `app_img_menu`, `app_img_search`, `banner`, `logo`, `theme`, `theme_background`, `theme_text`, `theme_header`, `theme_title`, `theme_button`, `customfooter`, `customfooter_code`, `valid_app`, `valid_pay`, `valid_assets`, `valid_theme`, `device_udid`) VALUES
(1, 'Woofluid', 'com.woofluid.app', '1.0.0', 1, '', 'USD', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_small.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_40.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_50.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_60.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_72.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_76.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_small_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_40_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_50_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_60_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_72_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/icon_76_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/itunesartwork.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/icons/itunesartwork_2x.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_iphone.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_2x_iphone.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Portrait_ipad.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Portrait_2x_ipad.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Landscape_ipad.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_Landscape_2x_ipad.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/splash/Default_568h_2x_iphone.png', 'dark', '', '', '', '', '', '', '', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/logo_banner/banner.png', 'http://125.63.92.59/Woofluid/Production1.15.0/Default_Assets/iOS/logo_banner/logo.png', 'Elegant', 'FFFFFF', 'CC0000', '1D4F17', 'BCFF03', 'FF4F2B', '0', '', 0, 1, 1, 1, '')
";
$wpdb->query($insert_iphone);

$query_1 = "CREATE TABLE IF NOT EXISTS `mofluidadmin_details` (
  `mofluidadmin_id` int(11) unsigned NOT NULL,
  `mofluid_id` varchar(128) DEFAULT '',
  `mofluid_key` varchar(63) NOT NULL DEFAULT '',
  `androidcount` int(16) NOT NULL DEFAULT '0',
  `iphonecount` int(16) NOT NULL DEFAULT '0',
  `desc` varchar(255) DEFAULT '',
  PRIMARY KEY (`mofluidadmin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$wpdb->query($query_1);

$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM mofluidadmin_iphone" );
$tmp = $wpdb->get_var( "SELECT mofluidadmin_id FROM mofluidadmin_details" );
if($tmp > 0){
		$insert_query = "INSERT INTO `mofluidadmin_details` (`mofluidadmin_id`, `mofluid_id`, `mofluid_key`, `androidcount`, `iphonecount`, `desc`) VALUES
			('".$update_app."', '', '', 0, 0, '')";
		$wpdb->query($insert_query);
	}
        
        
//$payment_methods = "CREATE TABLE IF NOT EXISTS `payment_methods` (
//  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
//  `title` varchar(128) DEFAULT '',
//  `method` varchar(63) NOT NULL DEFAULT '',
//  `active` bool DEFAULT FALSE,
//   PRIMARY KEY (`id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8";        
$payment_methods = "CREATE TABLE IF NOT EXISTS `mofluidpayment` (
  `payment_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payment_method_title` varchar(63) DEFAULT '',
  `payment_method_code` varchar(63) DEFAULT '',
  `payment_method_order_code` varchar(63) DEFAULT '',
  `payment_method_status` int(11) NOT NULL DEFAULT '0',
  `payment_method_mode` int(11) NOT NULL DEFAULT '0',
  `payment_method_account_id` varchar(63) NOT NULL DEFAULT '',
  `payment_method_account_key` varchar(255) NOT NULL DEFAULT '',
  `payment_account_email` varchar(63) DEFAULT '',
  PRIMARY KEY (`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$wpdb->query($payment_methods);

$insert_payment_methoed = "INSERT INTO `mofluidpayment`(`payment_method_title`, `payment_method_code`)
                            VALUES('Cash On Delivery', 'cod'),
                                   ('Paypal', 'paypal_standard')";

$wpdb->query($insert_payment_methoed);
//$paypal_config_details = "CREATE TABLE IF NOT EXISTS `paypal_config_details` (
//  
//  `merchant_id` varchar(500) DEFAULT '',
//  `mode` varchar(50) DEFAULT 'sandbox', 
//  `id` int(11) 
//   
//) ENGINE=InnoDB DEFAULT CHARSET=utf8";        
//
//$wpdb->query($paypal_config_details);

$build_accounts = "CREATE TABLE IF NOT EXISTS `mofluid_build_accounts` (
  `mofluid_admin_id` int(11) unsigned NOT NULL,
  `mofluid_platform_id` int(11) unsigned NOT NULL,
  `mofluid_id` varchar(127) NOT NULL DEFAULT '',
  `mofluid_password` varchar(127) NOT NULL DEFAULT '',
  `platform` varchar(63) NOT NULL DEFAULT '',
  `phonegap_build_id` varchar(127) NOT NULL DEFAULT '',
  `phonegap_build_password` varchar(127) NOT NULL DEFAULT '',
  `certificate_type` varchar(63) NOT NULL DEFAULT '',
  `certificate_path` varchar(511) NOT NULL DEFAULT '',
  `certificate_passpharse` varchar(63) NOT NULL DEFAULT '1',
  `provisioning_profile` varchar(511) NOT NULL DEFAULT '',
  `release_key_type` varchar(63) NOT NULL DEFAULT '',
  `release_privatekey_password` varchar(127) NOT NULL DEFAULT '',
  `release_keystore_password` varchar(127) NOT NULL DEFAULT '',
  `release_key_validity` varchar(63) NOT NULL DEFAULT '1000',
  `release_key_data` varchar(2048) NOT NULL DEFAULT '',
  `build_url` varchar(2048) NOT NULL DEFAULT 'http://125.63.92.59/Woofluid/Production1.16.0/Platforms',
  PRIMARY KEY (`mofluid_platform_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$wpdb->query($build_accounts);

$insert_build_accounts = "INSERT INTO `mofluid_build_accounts` (`mofluid_admin_id`, `mofluid_platform_id`, `mofluid_id`, `mofluid_password`, `platform`, `phonegap_build_id`, `phonegap_build_password`, `certificate_type`, `certificate_path`, `certificate_passpharse`, `provisioning_profile`, `release_key_type`, `release_privatekey_password`, `release_keystore_password`, `release_key_validity`, `release_key_data`, `build_url`) VALUES
(1, 1, '', '', 'ios', '', '', '0', '', '', '', '1', '', '', '', '', ''),
(1, 2, '', '', 'android', '', '', '1', '', '', '', '1', '', '', '1000', '', 'http://125.63.92.59/Woofluid/Production1.16.0/Platforms');";
$wpdb->query($insert_build_accounts);

$push_settings = "CREATE TABLE IF NOT EXISTS `mofluidpush_settings` (
  `mofluidadmin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gcm_mode` varchar(127) NOT NULL DEFAULT '',
  `gcm_id` varchar(511) NOT NULL DEFAULT '',
  `gcm_key` varchar(1023) NOT NULL DEFAULT '',
  `apn_mode` varchar(127) NOT NULL DEFAULT '',
  `apn_certificate` varchar(511) NOT NULL DEFAULT '',
  `apn_passphrase` varchar(127) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mofluidadmin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;";
$wpdb->query($push_settings);

$insert_push_settings = "INSERT INTO `mofluidpush_settings` (`mofluidadmin_id`, `gcm_mode`, `gcm_id`, `gcm_key`, `apn_mode`, `apn_certificate`, `apn_passphrase`) VALUES
(1, '0', '', '', '0', '', '');";
$wpdb->query($insert_push_settings);

}
?>
