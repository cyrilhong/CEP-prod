<?php
/**
 * Plugin Name: Cache for WordPress Performance
 * Plugin URI: https://optimisation.io
 * Description: Simple efficient WordPress caching.
 * Author: pigeonhut, Jody Nesbitt, hosting.io
 * Author URI:https://hosting.io
 * Version: 1.3.34
 *
 * Copyright (C)  2017-2018 Optimisation.io
 * Copyright (C)  2017 KeyCDN
 * Copyright (C)  2015 Sergej Müller
 */

/**
 * Definitions.
 */
define( 'OPTIMISATIONIO_CACHE_ADDON', true);
define( 'OPTIMISATIONIO_CDN_ENABLER_FILE', __FILE__ );
define( 'OPTIMISATIONIO_CDN_ENABLER_DIR', dirname( __FILE__ ) );
define( 'OPTIMISATIONIO_CDN_ENABLER_BASE', plugin_basename( __FILE__ ) );
define( 'OPTIMISATIONIO_CDN_ENABLER_MIN_WP', '3.8' );
define( 'OPTIMISATIONIO_CACHE_DIR', WP_CONTENT_DIR . '/cache/optimisationio' );

/**
 * Include files.
 */
require_once 'lib/class-optimisationio.php';
require_once 'lib/class-optimisationio-admin.php';
require_once 'lib/class-optimisationio-cacheenabler.php';
require_once 'lib/class-optimisationio-cacheenablerdisk.php';
require_once 'lib/class-optimisationio-cdnrewrite.php';

function optimisationio_install() {

	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$db_table = $wpdb->prefix . 'wp_optimisation_sizes_info';

	if( $db_table !== $wpdb->get_var("SHOW TABLES LIKE '" . $db_table . "'") ) {

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $db_table . ' (
	            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	            size BIGINT(20) NOT NULL DEFAULT 0,
	            optimised_size  BIGINT(20) NOT NULL DEFAULT 0,
	            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	            PRIMARY KEY (id)
	    )' . $wpdb->get_charset_collate() . ' ; ';

		dbDelta( $sql );
	}

	$db_table = $wpdb->prefix . 'wp_cache_gravatars';

	if( $db_table !== $wpdb->get_var("SHOW TABLES LIKE '" . $db_table . "'") ) {

	    $sql = "CREATE TABLE " . $db_table . " (
	    			id INT(11) NOT NULL AUTO_INCREMENT,
					url VARCHAR(190) NOT NULL COMMENT 'Original avatar url',
					avatar TEXT NOT NULL,
					files TEXT NOT NULL,
					time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (id),
					UNIQUE INDEX url (url)
				) " . $wpdb->get_charset_collate() . ";";

	    dbDelta( $sql );
	}

	return true;
}

register_activation_hook( __FILE__, 'optimisationio_install' );
register_activation_hook( __FILE__, array( 'Optimisationio_CacheEnabler', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'Optimisationio_CacheEnabler', 'on_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'Optimisationio_CacheEnabler', 'on_uninstall' ) );

Optimisationio::get_instance();
