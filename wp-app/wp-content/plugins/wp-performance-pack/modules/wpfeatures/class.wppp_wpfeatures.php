<?php

class WPPP_WPFeatures extends WPPP_Module {
	public static $options_default = array(
		'emojis'				=> array( 'default' => true, 'type' => 'bool' ),
		'editlock'				=> array( 'default' => true, 'type' => 'bool' ),
		'heartbeat_location'	=> array( 'default' => 'default',
										  'type' => 'enum',
										  'values' => array( 'default', 'disable_all', 'disable_dashboard', 'allow_post' ) ),
		'heartbeat_frequency'	=> array( 'default' => 'default',
										  'type' => 'enum',
										  'values' => array( 'default', '10', '15', '20', '25', '30', '35', '40', '50', '60' ) ),
		'rsd_link'				=> array( 'default' => true, 'type' => 'bool' ),
		'wlwmanifest_link'		=> array( 'default' => true, 'type' => 'bool' ),
		'wp_generator'			=> array( 'default' => true, 'type' => 'bool' ),
		'wp_shortlink_wp_head'	=> array( 'default' => true, 'type' => 'bool' ),
		'feed_links'			=> array( 'default' => true, 'type' => 'bool' ),
		'feed_links_extra'		=> array( 'default' => true, 'type' => 'bool' ),
		'adjacent_posts_links'	=> array( 'default' => true, 'type' => 'bool' ),
	);

	public function load_renderer() {
		if ( $this->wppp->options['advanced_admin_view'] ) {
			return new WPPP_WPFeatures_Advanced( $this->wppp );
		} else {
			return new WPPP_WPFeatures_Simple( $this->wppp );
		}
	}

	function is_available() { return true; } // always available

	function spawn_module() {
		return new WPPP_WPFeatures_Base( $this->wppp );
	}

	public function tabName() { return __( 'WP Features', 'wp-performance-pack' ); }
	public function description() { return __( 'Change or disable unwanted WordPress features.', 'wp-performance-pack' ); }
}

?>