<?php
/**
 * Class for changin WordPress features.
 *
 * Disable or change WordPress features like disabling header elements,
 * disabling emoji support or chaning Heartbeat settings.
 *
 * @author Björn Ahrens
 * @package WP Performance Pack
 * @since 2.0
 */

class WPPP_WPFeatures_Base extends WPPP_WPFeatures {

	static function clear_edit_locks() {
		global $wpdb;
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_edit_lock' ) );
	}

	public function early_init () {
	
	}

	function init () {
		if ( !$this->wppp->options[ 'emojis' ] )
			$this->remove_emoji();
		if ( !$this->wppp->options[ 'editlock' ] )
			add_filter( 'update_post_metadata', array( $this, 'update_metadata_filter' ), 10, 5 );


		//Heartbeat control features based on https://wordpress.org/support/plugin/heartbeat-control by Jeff Matson 
		if ( $this->wppp->options[ 'heartbeat_location' ] !== 'default' )
			$this->stop_heartbeat();
		if ( is_numeric( $this->wppp->options[ 'heartbeat_frequency' ] ) )
			add_filter( 'heartbeat_settings', array( $this, 'heartbeat_frequency' ) );

		if ( !$this->wppp->options[ 'rsd_link' ] ) 
			remove_action( 'wp_head', 'rsd_link' ); //removes EditURI/RSD (Really Simple Discovery) link.
		if ( !$this->wppp->options[ 'wlwmanifest_link' ] )
			remove_action( 'wp_head', 'wlwmanifest_link' ); //removes wlwmanifest (Windows Live Writer) link.
		if ( !$this->wppp->options[ 'wp_generator' ] )
			remove_action( 'wp_head', 'wp_generator' ); //removes meta name generator.
		if ( !$this->wppp->options[ 'wp_shortlink_wp_head' ] )
			remove_action( 'wp_head', 'wp_shortlink_wp_head' ); //removes shortlink.
		if ( !$this->wppp->options[ 'feed_links' ] )
			remove_action( 'wp_head', 'feed_links', 2 ); //removes feed links.
		if ( !$this->wppp->options[ 'feed_links_extra' ] )
			remove_action( 'wp_head', 'feed_links_extra', 3 );  //removes comments feed.
		if ( !$this->wppp->options[ 'adjacent_posts_links' ] )
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' ); //Removes prev and next links
	}

	function update_metadata_filter() {
		$args = func_get_args();
		if( isset( $args[ 2 ] ) && $args[ 2 ] == '_edit_lock' )
			return false;
	}

	function remove_emoji() {
		// Source: https://fastwp.de/4903/
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'remove_tinymce_emoji' ) );
	}

	function remove_tinymce_emoji( $plugins ) {
		// Source: https://fastwp.de/4903/
		if ( !is_array( $plugins ) ) {
			return array();
		}
		return array_diff( $plugins, array( 'wpemoji' ) );
	}

	/*
	 * Heartbeat control functions
	 */

	function stop_heartbeat() {
		global $pagenow;
		$loc = $this->wppp->options[ 'heartbeat_location' ];
		if ( ( $loc == 'disable_all' )
			|| ( ( $loc == 'disable_dashboard' ) && ( $pagenow == 'index.php' ) )
			|| ( ( $loc == 'allow_post' ) && ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ) ) )
			wp_deregister_script( 'heartbeat' );
	}

	function heartbeat_frequency( $settings ) {
		$settings[ 'interval' ] = $this->wppp->options[ 'heartbeat_frequency' ];
		return $settings;
	}
}