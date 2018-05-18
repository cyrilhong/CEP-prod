<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
require_once dirname( __FILE__ ) . '/lib/class-optimisationio-admin.php';
Optimisationio_Admin::clear_gravatars_cache();
Optimisationio_Admin::drop_avatars_cache_db_table();
delete_option( 'wpperformance_rev3a_pages_load_time' );
delete_option( 'Optimisationio_rev3a' );
wp_clear_scheduled_hook( 'optimisation_purge_time_event' );